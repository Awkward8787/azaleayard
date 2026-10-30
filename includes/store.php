<?php
declare(strict_types=1);

function azalea_client_hash(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    return hash('sha256', $ip . '|' . azalea_form_key());
}

function azalea_rate_allow(): bool
{
    $dir = AZALEA_STORAGE . '/rate';
    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
        return true;
    }
    if (random_int(1, 40) === 1) {
        foreach (glob($dir . '/*.json') ?: [] as $old) {
            if (is_file($old) && filemtime($old) < time() - 172800) {
                unlink($old);
            }
        }
    }
    $file = $dir . '/' . azalea_client_hash() . '.json';
    $fh = fopen($file, 'c+');
    if ($fh === false) {
        return true;
    }
    flock($fh, LOCK_EX);
    $raw = stream_get_contents($fh);
    $data = json_decode($raw === false ? '' : $raw, true);
    $times = is_array($data['times'] ?? null) ? $data['times'] : [];
    $now = time();
    $times = array_values(array_filter($times, static fn ($t) => is_int($t) && $t > $now - 3600));
    $allowed = count($times) < 8;
    if ($allowed) {
        $times[] = $now;
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, json_encode(['times' => $times]));
        fflush($fh);
    }
    flock($fh, LOCK_UN);
    fclose($fh);
    return $allowed;
}

function azalea_notification_body(array $row): string
{
    $lines = [
        'Azalea Yard website form',
        'Reference: ' . $row['id'],
        'Received: ' . $row['received_at'],
        'Type: ' . $row['type'],
        '',
        'Name: ' . $row['name'],
        'Email: ' . $row['email'],
        'Phone: ' . ($row['phone'] !== '' ? $row['phone'] : '(none)'),
    ];
    if ($row['type'] === 'founding_interest') {
        $lines[] = 'Interest: ' . ($row['interest_label'] !== '' ? $row['interest_label'] : '(none specified)');
        $lines[] = 'Recognition: ' . ($row['recognition'] === 'anonymous' ? 'Stay anonymous' : 'Show name as ' . $row['display_name']);
        $lines[] = '';
        $lines[] = 'This is interest only. No payment was taken. Do not list the name on the public wall unless the person later confirms it and Azalea Yard can accept support.';
    } else {
        $lines[] = '';
        $lines[] = 'Message:';
        $lines[] = $row['message'] ?? '';
    }
    $lines[] = '';
    $lines[] = 'Consents: contact=' . (!empty($row['consent_contact']) ? 'yes' : 'no') . ', privacy=' . (!empty($row['consent_privacy']) ? 'yes' : 'no');
    if (!empty($row['spam'])) {
        $lines[] = 'Flag: honeypot field was filled. Review before replying.';
    }
    return implode("\n", $lines) . "\n";
}

function azalea_append_jsonl(string $path, array $row): void
{
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }
    $fh = fopen($path, 'ab');
    if ($fh === false) {
        throw new RuntimeException('Could not store the submission.');
    }
    flock($fh, LOCK_EX);
    fwrite($fh, json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    chmod($path, 0600);
}

function azalea_notify(array $row): bool
{
    $dir = AZALEA_STORAGE . '/outbox';
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }
    $body = azalea_notification_body($row);
    $note = $dir . '/' . $row['id'] . '.txt';
    file_put_contents($note, $body, LOCK_EX);
    chmod($note, 0600);

    $to = trim((string) (azalea_config()['notify_email'] ?? ''));
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $from = trim((string) (azalea_config()['mail_from'] ?? ''));
    if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $from)) {
        $from = 'noreply@localhost';
    }
    $subject = 'Azalea Yard form ' . $row['id'];
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $from,
    ];
    $reply = $row['email'];
    if (filter_var($reply, FILTER_VALIDATE_EMAIL) && !preg_match('/[\r\n]/', $reply)) {
        $headers[] = 'Reply-To: ' . $reply;
    }
    return @mail($to, $subject, $body, implode("\r\n", $headers));
}

function azalea_store(array $data, bool $spam = false): array
{
    $id = 'AY-' . strtoupper(bin2hex(random_bytes(4)));
    $row = array_merge([
        'id' => $id,
        'received_at' => gmdate('c'),
        'spam' => $spam,
    ], $data);
    if ($spam) {
        $row['email_sent'] = false;
        $row['email_note'] = 'Honeypot was filled. Stored in spam.jsonl and not mailed.';
        $dir = AZALEA_STORAGE . '/outbox';
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        $note = $dir . '/' . $row['id'] . '.txt';
        file_put_contents($note, azalea_notification_body($row), LOCK_EX);
        chmod($note, 0600);
    } else {
        $row['email_sent'] = azalea_notify($row);
        $row['email_note'] = $row['email_sent']
            ? 'mail() accepted the notification'
            : 'Notification stored in storage/outbox. mail() was not sent because notify_email is empty or the mailer refused it.';
    }
    $path = $spam
        ? AZALEA_STORAGE . '/spam.jsonl'
        : AZALEA_STORAGE . '/submissions/interest.jsonl';
    azalea_append_jsonl($path, $row);
    return $row;
}

function azalea_handle_post(array $post): void
{
    $return = (string) ($post['return'] ?? '/join/');
    if (!in_array($return, ['/join/', '/contact/'], true)) {
        $return = '/join/';
    }
    $fail = static function (array $errors) use ($return, $post): void {
        $_SESSION['form_error'] = $errors;
        $_SESSION['form_old'] = azalea_old_from($post);
        header('Location: ' . $return, true, 303);
        exit;
    };

    $tokenError = azalea_token_ok($post);
    if ($tokenError !== null) {
        $fail(['form' => $tokenError]);
    }
    if (!azalea_rate_allow()) {
        $fail(['form' => 'Too many submissions from this network in the past hour. Please try again later.']);
    }

    $honeypot = trim((string) ($post['company_url'] ?? ''));
    $type = (string) ($post['form_type'] ?? '');
    if ($type === 'founding') {
        $checked = azalea_validate_founding($post);
    } elseif ($type === 'contact') {
        $checked = azalea_validate_contact($post);
    } else {
        $checked = ['ok' => false, 'errors' => ['form' => 'Unknown form.'], 'data' => []];
    }
    if (!$checked['ok']) {
        $fail($checked['errors']);
    }

    try {
        $row = azalea_store($checked['data'], $honeypot !== '');
    } catch (RuntimeException $e) {
        $fail(['form' => 'The form could not be saved. Please try again in a little while.']);
    }

    $_SESSION['thanks'] = [
        'id' => $row['id'],
        'kind' => $type,
    ];
    header('Location: /join/thanks.php', true, 303);
    exit;
}
