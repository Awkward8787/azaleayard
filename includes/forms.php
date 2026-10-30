<?php
declare(strict_types=1);

function azalea_plain(string $value, int $max): string
{
    $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    if (mb_strlen($value) > $max) {
        $value = mb_substr($value, 0, $max);
    }
    return $value;
}

function azalea_has_controls(string $value): bool
{
    return (bool) preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F<>]/', $value);
}

function azalea_form_tokens(): array
{
    $issued = time();
    $nonce = bin2hex(random_bytes(8));
    $token = hash_hmac('sha256', $issued . '|' . $nonce, azalea_form_key());
    return ['issued' => $issued, 'nonce' => $nonce, 'token' => $token];
}

function azalea_token_ok(array $post): ?string
{
    $issued = (int) ($post['issued'] ?? 0);
    $nonce = (string) ($post['nonce'] ?? '');
    $token = (string) ($post['token'] ?? '');
    if (!preg_match('/^[a-f0-9]{16}$/', $nonce) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
        return 'That form expired. Please submit it again.';
    }
    $expect = hash_hmac('sha256', $issued . '|' . $nonce, azalea_form_key());
    if (!hash_equals($expect, $token)) {
        return 'That form expired. Please submit it again.';
    }
    $age = time() - $issued;
    if ($age < 2) {
        return 'Please take a moment to review the form, then submit it again.';
    }
    if ($age > 4 * 3600 || $issued > time() + 60) {
        return 'That form expired. Please submit it again.';
    }
    return null;
}

function azalea_old_from(array $post): array
{
    $keys = ['name', 'email', 'phone', 'interest', 'recognition', 'display_name', 'message', 'form_type'];
    $old = [];
    foreach ($keys as $key) {
        if (isset($post[$key]) && is_string($post[$key])) {
            $old[$key] = azalea_plain($post[$key], 4000);
        }
    }
    foreach (['consent_contact', 'consent_privacy'] as $key) {
        $old[$key] = isset($post[$key]) ? '1' : '';
    }
    return $old;
}

function azalea_validate_person(array $post): array
{
    $errors = [];
    $name = azalea_plain((string) ($post['name'] ?? ''), 80);
    $email = azalea_plain((string) ($post['email'] ?? ''), 254);
    $phone = azalea_plain((string) ($post['phone'] ?? ''), 24);

    if (mb_strlen($name) < 2 || azalea_has_controls($name)) {
        $errors['name'] = 'Enter your name.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || azalea_has_controls($email)) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if ($phone !== '' && !preg_match('/^[0-9+().\\-\\s]{7,24}$/', $phone)) {
        $errors['phone'] = 'Enter a phone number using digits, spaces, and + ( ) . - or leave it blank.';
    }
    if (empty($post['consent_contact'])) {
        $errors['consent_contact'] = 'Consent to be contacted is required.';
    }
    if (empty($post['consent_privacy'])) {
        $errors['consent_privacy'] = 'Please confirm you have read the privacy policy.';
    }

    return [$errors, $name, $email, $phone];
}

function azalea_validate_founding(array $post): array
{
    [$errors, $name, $email, $phone] = azalea_validate_person($post);
    $labels = azalea_interest_labels();
    $interest = (string) ($post['interest'] ?? '');
    if ($interest !== '' && !isset($labels[$interest])) {
        $errors['interest'] = 'Choose an amount range or a naming item from the list.';
        $interest = '';
    }
    $recognition = (string) ($post['recognition'] ?? '');
    if (!in_array($recognition, ['named', 'anonymous'], true)) {
        $errors['recognition'] = 'Choose whether to show a name or stay anonymous.';
    }
    $display = azalea_plain((string) ($post['display_name'] ?? ''), 60);
    if ($recognition === 'named') {
        if (mb_strlen($display) < 1 || azalea_has_controls($display)) {
            $errors['display_name'] = 'Enter the name to show, or choose to stay anonymous.';
        }
    } else {
        $display = '';
    }

    $data = [
        'type' => 'founding_interest',
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'interest_id' => $interest,
        'interest_label' => $interest === '' ? '' : $labels[$interest],
        'recognition' => $recognition,
        'display_name' => $display,
        'consent_contact' => empty($errors['consent_contact']),
        'consent_privacy' => empty($errors['consent_privacy']),
    ];
    return ['ok' => $errors === [], 'errors' => $errors, 'data' => $data];
}

function azalea_validate_contact(array $post): array
{
    [$errors, $name, $email, $phone] = azalea_validate_person($post);
    $message = azalea_plain((string) ($post['message'] ?? ''), 4000);
    if (mb_strlen($message) < 2 || azalea_has_controls($message)) {
        $errors['message'] = 'Enter a message.';
    }
    $data = [
        'type' => 'contact',
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'message' => $message,
        'consent_contact' => empty($errors['consent_contact']),
        'consent_privacy' => empty($errors['consent_privacy']),
    ];
    return ['ok' => $errors === [], 'errors' => $errors, 'data' => $data];
}

function azalea_render_tokens(): void
{
    $tokens = azalea_form_tokens();
    echo '<input type="hidden" name="issued" value="' . h((string) $tokens['issued']) . '">';
    echo '<input type="hidden" name="nonce" value="' . h($tokens['nonce']) . '">';
    echo '<input type="hidden" name="token" value="' . h($tokens['token']) . '">';
}

function azalea_honeypot(): void
{
    echo '<div class="hp" aria-hidden="true">';
    echo '<label for="company_url">Company website</label>';
    echo '<input id="company_url" name="company_url" type="text" tabindex="-1" autocomplete="off" value="">';
    echo '</div>';
}

function azalea_error_summary(array $errors): void
{
    if ($errors === []) {
        return;
    }
    echo '<div class="form-errors" role="alert">';
    echo '<p><strong>Please fix the following and submit again.</strong></p><ul>';
    foreach ($errors as $key => $message) {
        echo '<li><a href="#' . h((string) $key) . '">' . h($message) . '</a></li>';
    }
    echo '</ul></div>';
}

function azalea_field_error(array $errors, string $key): void
{
    if (!isset($errors[$key])) {
        return;
    }
    echo '<p class="field-error" id="' . h($key) . '-error">' . h($errors[$key]) . '</p>';
}

function azalea_render_interest_form(array $opts): void
{
    $errors = $opts['errors'] ?? [];
    $old = $opts['old'] ?? [];
    $selected = (string) ($old['interest'] ?? $opts['selected'] ?? '');
    $labels = azalea_interest_labels();
    if (!isset($labels[$selected])) {
        $selected = '';
    }
    $recognition = (string) ($old['recognition'] ?? 'named');
    if (!in_array($recognition, ['named', 'anonymous'], true)) {
        $recognition = 'named';
    }
    $return = (string) ($opts['return'] ?? '/join/');
    $formId = (string) ($opts['form_id'] ?? 'interest-form');

    echo '<form class="interest-form" id="' . h($formId) . '" method="post" action="/join/submit.php" novalidate>';
    echo '<input type="hidden" name="form_type" value="founding">';
    echo '<input type="hidden" name="return" value="' . h($return) . '">';
    azalea_render_tokens();
    azalea_honeypot();
    azalea_error_summary($errors);

    $name = (string) ($old['name'] ?? '');
    $email = (string) ($old['email'] ?? '');
    $phone = (string) ($old['phone'] ?? '');
    $display = (string) ($old['display_name'] ?? '');

    echo '<div class="field">';
    echo '<label for="name">Name</label>';
    echo '<input id="name" name="name" type="text" required maxlength="80" autocomplete="name" value="' . h($name) . '"' . (isset($errors['name']) ? ' aria-invalid="true" aria-describedby="name-error"' : '') . '>';
    azalea_field_error($errors, 'name');
    echo '</div>';

    echo '<div class="field">';
    echo '<label for="email">Email</label>';
    echo '<input id="email" name="email" type="email" required maxlength="254" autocomplete="email" inputmode="email" value="' . h($email) . '"' . (isset($errors['email']) ? ' aria-invalid="true" aria-describedby="email-error"' : '') . '>';
    azalea_field_error($errors, 'email');
    echo '</div>';

    echo '<div class="field">';
    echo '<label for="phone">Phone <span class="optional">(optional)</span></label>';
    echo '<input id="phone" name="phone" type="tel" maxlength="24" autocomplete="tel" inputmode="tel" value="' . h($phone) . '"' . (isset($errors['phone']) ? ' aria-invalid="true" aria-describedby="phone-error"' : '') . '>';
    azalea_field_error($errors, 'phone');
    echo '</div>';

    echo '<div class="field">';
    echo '<label for="interest">Interest <span class="optional">(optional)</span></label>';
    echo '<select id="interest" name="interest"' . (isset($errors['interest']) ? ' aria-invalid="true" aria-describedby="interest-error"' : '') . '>';
    echo '<option value="">No specific amount or item yet</option>';
    foreach (azalea_interest_catalog() as $group => $options) {
        echo '<optgroup label="' . h($group) . '">';
        foreach ($options as $option) {
            $isSel = $option['value'] === $selected ? ' selected' : '';
            echo '<option value="' . h($option['value']) . '"' . $isSel . '>' . h($option['label']) . '</option>';
        }
        echo '</optgroup>';
    }
    echo '</select>';
    echo '<p class="hint">An amount range, or a room or item from the concept plan.</p>';
    azalea_field_error($errors, 'interest');
    echo '</div>';

    echo '<fieldset class="field">';
    echo '<legend>Recognition</legend>';
    echo '<label class="choice"><input type="radio" name="recognition" value="named"' . ($recognition === 'named' ? ' checked' : '') . '> <span>Show my name as</span></label>';
    echo '<input id="display_name" name="display_name" type="text" maxlength="60" value="' . h($display) . '" aria-label="Name to show"' . (isset($errors['display_name']) ? ' aria-invalid="true" aria-describedby="display_name-error"' : '') . '>';
    echo '<label class="choice"><input type="radio" name="recognition" value="anonymous"' . ($recognition === 'anonymous' ? ' checked' : '') . '> <span>Stay anonymous</span></label>';
    echo '<p class="hint">Anonymous interest is recorded as “Anonymous Friend of Azalea Yard.” Choosing a name does not put it on the Founding Donors wall. A name is listed only later, with your confirmation, and only once Azalea Yard can accept support.</p>';
    azalea_field_error($errors, 'recognition');
    azalea_field_error($errors, 'display_name');
    echo '</fieldset>';

    $contactChecked = (($old['consent_contact'] ?? '') === '1') ? ' checked' : '';
    $privacyChecked = (($old['consent_privacy'] ?? '') === '1') ? ' checked' : '';

    echo '<div class="field checks">';
    echo '<label class="choice"><input type="checkbox" name="consent_contact" value="1"' . $contactChecked . (isset($errors['consent_contact']) ? ' aria-invalid="true" aria-describedby="consent_contact-error"' : '') . '> <span>Azalea Yard may email or call me about this interest.</span></label>';
    azalea_field_error($errors, 'consent_contact');
    echo '<label class="choice"><input type="checkbox" name="consent_privacy" value="1"' . $privacyChecked . (isset($errors['consent_privacy']) ? ' aria-invalid="true" aria-describedby="consent_privacy-error"' : '') . '> <span>I have read the <a href="/privacy/">privacy policy</a>. I understand this form records interest and does not take a donation.</span></label>';
    azalea_field_error($errors, 'consent_privacy');
    echo '</div>';

    echo '<button class="btn btn-primary" type="submit">Join the Founding Donor list</button>';
    echo '</form>';
}
