<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/includes/bootstrap.php';

$errors = [];
$old = [];
if (($_SESSION['form_old']['form_type'] ?? '') === 'contact' || isset($_SESSION['form_error'])) {
    if (($_SESSION['form_old']['form_type'] ?? '') === 'contact' || (($_SESSION['form_old']['form_type'] ?? '') === '' && isset($_SESSION['form_error']['message']))) {
        $errors = $_SESSION['form_error'] ?? [];
        $old = $_SESSION['form_old'] ?? [];
        unset($_SESSION['form_error'], $_SESSION['form_old']);
    }
}

azalea_header([
    'title' => 'Contact | Azalea Yard',
    'description' => 'Contact Azalea Yard, a youth arts and vocational center in formation in Valdosta and Lowndes County, Georgia.',
    'path' => '/contact/',
    'active' => 'contact',
]);
$cfg = azalea_config();
$email = azalea_public_email();
$phone = trim((string) ($cfg['public_phone'] ?? ''));
$address = trim((string) ($cfg['mailing_address'] ?? ''));
?>
<header class="page-head">
  <p class="eyebrow">Contact</p>
  <h1>Write to Azalea Yard</h1>
  <p class="lede">The form below reaches the organizer. Use the <a href="/join/">Founding Donor list</a> if you want to record interest in a tier or a naming item.</p>
</header>

<?php if ($email !== null || $phone !== '' || $address !== ''): ?>
<section class="contact-facts">
  <?php if ($email !== null): ?><p>Email: <a href="mailto:<?= h($email) ?>"><?= h($email) ?></a></p><?php endif; ?>
  <?php if ($phone !== ''): ?><p>Phone: <?= h($phone) ?></p><?php endif; ?>
  <?php if ($address !== ''): ?><p>Mail: <?= nl2br(h($address)) ?></p><?php endif; ?>
</section>
<?php endif; ?>

<form class="interest-form" method="post" action="/join/submit.php" novalidate>
  <input type="hidden" name="form_type" value="contact">
  <input type="hidden" name="return" value="/contact/">
  <?php azalea_render_tokens(); azalea_honeypot(); azalea_error_summary($errors); ?>
  <div class="field">
    <label for="name">Name</label>
    <input id="name" name="name" type="text" required maxlength="80" autocomplete="name" value="<?= h((string) ($old['name'] ?? '')) ?>"<?= isset($errors['name']) ? ' aria-invalid="true" aria-describedby="name-error"' : '' ?>>
    <?php azalea_field_error($errors, 'name'); ?>
  </div>
  <div class="field">
    <label for="email">Email</label>
    <input id="email" name="email" type="email" required maxlength="254" autocomplete="email" inputmode="email" value="<?= h((string) ($old['email'] ?? '')) ?>"<?= isset($errors['email']) ? ' aria-invalid="true" aria-describedby="email-error"' : '' ?>>
    <?php azalea_field_error($errors, 'email'); ?>
  </div>
  <div class="field">
    <label for="phone">Phone <span class="optional">(optional)</span></label>
    <input id="phone" name="phone" type="tel" maxlength="24" autocomplete="tel" inputmode="tel" value="<?= h((string) ($old['phone'] ?? '')) ?>"<?= isset($errors['phone']) ? ' aria-invalid="true" aria-describedby="phone-error"' : '' ?>>
    <?php azalea_field_error($errors, 'phone'); ?>
  </div>
  <div class="field">
    <label for="message">Message</label>
    <textarea id="message" name="message" required maxlength="4000" rows="6"<?= isset($errors['message']) ? ' aria-invalid="true" aria-describedby="message-error"' : '' ?>><?= h((string) ($old['message'] ?? '')) ?></textarea>
    <?php azalea_field_error($errors, 'message'); ?>
  </div>
  <div class="field checks">
    <label class="choice"><input type="checkbox" name="consent_contact" value="1"<?= (($old['consent_contact'] ?? '') === '1') ? ' checked' : '' ?><?= isset($errors['consent_contact']) ? ' aria-invalid="true" aria-describedby="consent_contact-error"' : '' ?>> <span>Azalea Yard may email or call me about this message.</span></label>
    <?php azalea_field_error($errors, 'consent_contact'); ?>
    <label class="choice"><input type="checkbox" name="consent_privacy" value="1"<?= (($old['consent_privacy'] ?? '') === '1') ? ' checked' : '' ?><?= isset($errors['consent_privacy']) ? ' aria-invalid="true" aria-describedby="consent_privacy-error"' : '' ?>> <span>I have read the <a href="/privacy/">privacy policy</a>.</span></label>
    <?php azalea_field_error($errors, 'consent_privacy'); ?>
  </div>
  <button class="btn btn-primary" type="submit">Send the message</button>
</form>
<?php azalea_footer(); ?>
