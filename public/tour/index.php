<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/includes/bootstrap.php';

azalea_header([
    'title' => 'Concept tour | Azalea Yard',
    'description' => 'Explore a concept model of Azalea Yard, a planned 12,000 square foot youth arts and vocational center. Not to scale. Reserve interest in a space without making a donation.',
    'path' => '/tour/',
    'active' => 'tour',
    'bare' => true,
    'body_class' => 'tour-page',
    'extra_css' => '/tour/styles.css',
]);

$plan = azalea_plan();
$meta = $plan['meta'];
$selected = (string) ($_GET['slot'] ?? '');
?>
<div class="tour-toolbar">
  <p class="ribbon"><strong>Concept design, not to scale for construction.</strong> The site has not been chosen. Room sizes are a concept allocation of the plan’s <?= h(number_format((int) $meta['totalSf'])) ?> sf.</p>
  <div class="tour-tools">
    <button type="button" id="btn-overview">Overview</button>
    <button type="button" id="btn-top">Floor plan view</button>
    <a class="tool" id="btn-wall" href="/donors/">Founding Donors wall</a>
    <a class="tool" href="model/azalea-yard-concept.glb" download>Download 3D (GLB)</a>
    <a class="tool" href="floorplan/azalea-yard-concept-floorplan.dxf" download>Download floor plan (DXF)</a>
    <a class="tool js-load-3d" href="?view=3d">Load the 3D tour</a>
    <a class="tool" href="?view=list">Still floor plan</a>
    <label class="jump">Jump to a room
      <select id="room-jump">
        <option value="">Choose</option>
        <?php foreach ($plan['rooms'] as $room): ?>
          <option value="<?= h($room['id']) ?>"><?= h($room['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  </div>
</div>
<main id="main" class="tour-shell">
  <div class="stage">
    <div id="fallback">
      <div id="floorplan">
        <?= azalea_floorplan_svg('tour') ?>
      </div>
      <p class="fallback-note">You are looking at the still floor plan. It is the same concept as the 3D model, without a moving camera. <a class="js-load-3d" href="?view=3d">Load the 3D tour</a> if your browser can draw it.</p>
      <h2>Rooms</h2>
      <ul class="room-list">
        <?php foreach ($plan['rooms'] as $room):
            $fund = $room['fundTotal'] ?? null;
            $slotId = 'room:' . $room['id'];
            $hasSlot = false;
            foreach ($plan['slots'] as $slot) {
                if ($slot['id'] === $slotId) {
                    $hasSlot = true;
                    break;
                }
            }
        ?>
          <li id="room-<?= h($room['id']) ?>">
            <h3><a href="?room=<?= h($room['id']) ?>&amp;view=list"><?= h($room['name']) ?></a></h3>
            <p><?= h($room['purpose']) ?></p>
            <p class="fine"><?= h(number_format((int) $room['sf'])) ?> sf<?php if ($room['inBuilding']): ?> · concept allocation<?php else: ?> · outside the 12,000 sf building<?php endif; ?><?php if ($fund !== null): ?> · <?= h(azalea_money((float) $fund)) ?> to build out and equip<?php else: ?> · not priced in the budget<?php endif; ?></p>
            <?php if ($hasSlot): ?>
              <p><a href="/join/?slot=<?= h(rawurlencode($slotId)) ?>">Reserve interest in this space</a></p>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div id="viewport" hidden aria-label="3D concept model of Azalea Yard. Drag to orbit, scroll to zoom, or use Jump to a room."></div>
    <div id="legend"></div>
    <p id="hint">Choose a room in the plan, or use Jump to a room.</p>
  </div>
  <aside id="panel" aria-live="polite">
    <p class="kicker">Explore the plan</p>
    <h2>Azalea Yard</h2>
    <p><?= h(azalea_mission()) ?></p>
    <p class="fine"><?= h($meta['sfNote']) ?> <?= h($meta['costNote']) ?></p>
  </aside>
</main>

<dialog id="reserve" aria-labelledby="reserve-title">
  <div class="dlg">
    <h2 id="reserve-title">Reserve interest in this space</h2>
    <p class="reserve-label" data-reserve-label></p>
    <p class="fine" data-reserve-meta></p>
    <p>This is the same Founding Donor form. It records interest. It does not take a donation, and it does not hold the name on the wall.</p>
    <?php
    azalea_render_interest_form([
        'selected' => $selected,
        'return' => '/join/',
        'form_id' => 'tour-interest-form',
    ]);
    ?>
    <button type="button" class="btn btn-secondary" id="reserve-cancel">Keep exploring</button>
  </div>
</dialog>
<dialog id="wall" class="wall" aria-labelledby="wall-title"></dialog>

<script type="importmap">
{ "imports": { "three": "./vendor/three.module.js", "three/addons/": "./vendor/addons/" } }
</script>
<script type="module" src="./app.js"></script>
<?php azalea_footer(); ?>
