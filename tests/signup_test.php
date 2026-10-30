<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';

$fail = 0;
function check(bool $ok, string $message): void
{
    global $fail;
    if ($ok) {
        echo "ok  {$message}\n";
        return;
    }
    $fail++;
    echo "FAIL {$message}\n";
}

check(azalea_tax_mode() === 'none', 'default tax mode is none');
$notice = azalea_status_notice();
check(str_contains($notice['title'], 'in formation'), 'formation sentence is the default title');
check(!str_contains(strtolower($notice['title'] . $notice['body']), '501'), 'default notice does not name a determination');
check(!str_contains(strtolower($notice['title'] . $notice['body']), 'deduct'), 'default notice does not say deductible');

check(azalea_tax_mode(['tax_status' => '501c3', 'org_legal_name' => '', 'org_ein' => '12-3456789', 'sponsor_name' => '', 'sponsor_ein' => '']) === 'none', '501c3 without a legal name stays none');
check(azalea_tax_mode(['tax_status' => '501c3', 'org_legal_name' => 'Azalea Yard Inc.', 'org_ein' => '', 'sponsor_name' => '', 'sponsor_ein' => '']) === 'none', '501c3 without an EIN stays none');
check(azalea_tax_mode(['tax_status' => 'fiscal_sponsor', 'org_legal_name' => '', 'org_ein' => '', 'sponsor_name' => 'Example Sponsor', 'sponsor_ein' => '']) === 'none', 'sponsor without an EIN stays none');
$ready = azalea_tax_mode(['tax_status' => '501c3', 'org_legal_name' => 'Azalea Yard Inc.', 'org_ein' => '12-3456789', 'sponsor_name' => '', 'sponsor_ein' => '']);
check($ready === '501c3', '501c3 with name and EIN unlocks that wording');

$good = azalea_validate_founding([
    'name' => 'Ada Lovelace',
    'email' => 'ada@example.com',
    'phone' => '',
    'interest' => 'tier:seed',
    'recognition' => 'anonymous',
    'display_name' => '',
    'consent_contact' => '1',
    'consent_privacy' => '1',
]);
check($good['ok'] === true, 'anonymous interest with a tier is valid');
check($good['data']['display_name'] === '', 'anonymous interest stores no display name');

$named = azalea_validate_founding([
    'name' => 'Ada Lovelace',
    'email' => 'ada@example.com',
    'phone' => '(229) 555-0100',
    'interest' => 'room:ceramics',
    'recognition' => 'named',
    'display_name' => 'The Lovelace Family',
    'consent_contact' => '1',
    'consent_privacy' => '1',
]);
check($named['ok'] === true, 'named interest in a real slot is valid');
check($named['data']['interest_label'] !== '', 'slot label is stored');

$badEmail = azalea_validate_founding([
    'name' => 'Ada',
    'email' => 'not-an-email',
    'recognition' => 'anonymous',
    'consent_contact' => '1',
    'consent_privacy' => '1',
]);
check($badEmail['ok'] === false && isset($badEmail['errors']['email']), 'bad email is rejected');

$noConsent = azalea_validate_founding([
    'name' => 'Ada Lovelace',
    'email' => 'ada@example.com',
    'recognition' => 'named',
    'display_name' => 'Ada',
]);
check(isset($noConsent['errors']['consent_contact']) && isset($noConsent['errors']['consent_privacy']), 'both consents are required');
check(isset($noConsent['errors']['display_name']) === false, 'display name is present in this case');

$missingName = azalea_validate_founding([
    'name' => 'Ada Lovelace',
    'email' => 'ada@example.com',
    'recognition' => 'named',
    'display_name' => '',
    'consent_contact' => '1',
    'consent_privacy' => '1',
]);
check(isset($missingName['errors']['display_name']), 'a shown name is required when recognition is named');

$bogus = azalea_validate_founding([
    'name' => 'Ada Lovelace',
    'email' => 'ada@example.com',
    'interest' => 'room:does-not-exist',
    'recognition' => 'anonymous',
    'consent_contact' => '1',
    'consent_privacy' => '1',
]);
check(isset($bogus['errors']['interest']), 'unknown naming items are rejected');

$contact = azalea_validate_contact([
    'name' => 'Ada Lovelace',
    'email' => 'ada@example.com',
    'phone' => '',
    'message' => 'Please tell me when the list opens.',
    'consent_contact' => '1',
    'consent_privacy' => '1',
]);
check($contact['ok'] === true, 'contact message is valid');

$_SERVER['REMOTE_ADDR'] = '203.0.113.10';
$allowed = 0;
for ($i = 0; $i < 8; $i++) {
    if (azalea_rate_allow()) {
        $allowed++;
    }
}
check($allowed === 8, 'eight submissions in an hour are allowed');
check(azalea_rate_allow() === false, 'the ninth submission in an hour is refused');

if ($fail > 0) {
    fwrite(STDERR, "{$fail} failed\n");
    exit(1);
}
echo "all checks passed\n";
