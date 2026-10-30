<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/includes/bootstrap.php';

$thanks = $_SESSION['thanks'] ?? null;
unset($_SESSION['thanks']);
$kind = is_array($thanks) ? (string) ($thanks['kind'] ?? '') : '';
$id = is_array($thanks) ? (string) ($thanks['id'] ?? '') : '';
if (!preg_match('/^AY-[A-F0-9]{8}$/', $id)) {
    $id = '';
}
$notice = azalea_status_notice();

azalea_header([
    'title' => 'Interest recorded | Azalea Yard',
    'description' => 'Your Azalea Yard form was recorded. Azalea Yard is in formation and is not yet accepting donations.',
    'path' => '/join/thanks.php',
    'robots' => 'noindex,follow',
    'active' => '',
]);
?>
<header class="page-head">
  <p class="eyebrow">Thank you</p>
  <?php if ($id !== ''): ?>
    <h1><?= $kind === 'contact' ? 'Your message is recorded.' : 'Your interest is recorded.' ?></h1>
    <p class="lede"><?= h($notice['title']) ?> <?= h($notice['body']) ?></p>
    <p>Reference <strong><?= h($id) ?></strong>. Keep it if you write again and want this submission found.</p>
  <?php else: ?>
    <h1>This page confirms a form after it is sent.</h1>
    <p class="lede">If you have not sent one, start at the <a href="/join/">Founding Donor list</a> or the <a href="/contact/">contact form</a>.</p>
  <?php endif; ?>
  <p><a class="btn btn-secondary" href="/">Back to the home page</a></p>
</header>
<?php azalea_footer(); ?>
