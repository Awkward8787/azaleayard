<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/includes/bootstrap.php';

$errors = [];
$old = [];
$pendingType = (string) ($_SESSION['form_old']['form_type'] ?? '');
if ($pendingType === 'founding' || ($pendingType === '' && isset($_SESSION['form_error']) && !isset($_SESSION['form_error']['message']))) {
    $errors = $_SESSION['form_error'] ?? [];
    $old = $_SESSION['form_old'] ?? [];
    unset($_SESSION['form_error'], $_SESSION['form_old']);
}

$selected = '';
if ($old === []) {
    $selected = (string) ($_GET['slot'] ?? $_GET['interest'] ?? '');
}

azalea_header([
    'title' => 'Join the Founding Donor list | Azalea Yard',
    'description' => 'Record interest in supporting Azalea Yard. Name, email, optional phone, an optional amount or naming item, and a recognition preference. No payment is taken.',
    'path' => '/join/',
    'active' => '',
]);

$label = azalea_interest_labels()[$selected] ?? '';
?>
<header class="page-head">
  <p class="eyebrow">Interest only</p>
  <h1>Join the Founding Donor list</h1>
  <p class="lede">Tell Azalea Yard you want to be part of the founding list. This is not a donation. Nothing is charged, and your name is not added to the public wall from this form.</p>
  <?php if ($label !== ''): ?>
    <p class="callout">You are reserving interest in: <?= h($label) ?></p>
  <?php endif; ?>
</header>
<?php
azalea_render_interest_form([
    'errors' => $errors,
    'old' => $old,
    'selected' => $selected,
    'return' => '/join/',
]);
azalea_footer();
