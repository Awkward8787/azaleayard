<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/includes/bootstrap.php';

azalea_header([
    'title' => 'Founding Donors wall | Azalea Yard',
    'description' => 'Naming opportunities for Azalea Yard. Every slot is Available. No donor names are listed. Azalea Yard is in formation and is not yet accepting donations.',
    'path' => '/donors/',
    'active' => 'donors',
]);

$plan = azalea_plan();
$tiers = azalea_tiers_by_id();
$byTier = [];
foreach ($plan['slots'] as $slot) {
    $byTier[$slot['tier']][] = $slot;
}
$order = array_reverse(array_column($plan['tiers'], 'id'));
?>
<header class="page-head">
  <p class="eyebrow"><?= count($plan['slots']) ?> opportunities</p>
  <h1>Founding Donors wall</h1>
  <p class="lede">Every slot starts as Available. This page does not show sample names. A name is added only after a person has agreed to be listed and Azalea Yard is able to accept support.</p>
</header>

<?php foreach ($order as $tierId):
    $slots = $byTier[$tierId] ?? [];
    if ($slots === []) {
        continue;
    }
    $tier = $tiers[$tierId];
?>
<section class="tier-block" id="<?= h($tierId) ?>">
  <h2><?= h($tier['name']) ?> · <?= h(azalea_tier_amount($tier)) ?></h2>
  <p class="fine"><?= h($tier['funds']) ?> · <?= count($slots) ?> available</p>
  <ul class="slot-grid">
    <?php foreach ($slots as $slot): ?>
      <li class="slot">
        <p class="who">Available</p>
        <p class="what"><?= h($slot['label']) ?><?php if (($slot['status'] ?? '') === 'proposed'): ?> <span class="pill">Proposed use</span><?php endif; ?></p>
        <a href="/join/?slot=<?= h(rawurlencode($slot['id'])) ?>">Reserve interest</a>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
<?php endforeach; ?>
<?php azalea_footer(); ?>
