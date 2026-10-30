<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';

azalea_header([
    'title' => 'Azalea Yard | A youth arts and vocational center in formation',
    'description' => 'Azalea Yard is a planned year-round youth arts and vocational center for ages 16 to 24 in Valdosta and Lowndes County, Georgia. Founded by Anthony Jaamal Jones. In formation, and not yet accepting donations.',
    'path' => '/',
    'active' => 'home',
]);
$cfg = azalea_config();
?>
<section class="hero">
  <div class="hero-copy">
    <p class="eyebrow">Ages 16 to 24 · Valdosta and Lowndes County</p>
    <h1>Azalea Yard</h1>
    <p class="lede">A planned year-round youth arts and vocational center. Valdosta is the Azalea City. The yard is the hangout, the backyard, and the work yard.</p>
    <div class="cta-row">
      <a class="btn btn-primary" href="/join/">Join the Founding Donor list</a>
      <a class="btn btn-secondary" href="/tour/">Explore the concept tour</a>
    </div>
    <p class="fine">Interest only. The list does not take a donation, and it does not place a name on the wall.</p>
  </div>
  <figure class="hero-plan">
    <?= azalea_floorplan_svg('home') ?>
    <figcaption>Concept design, not to scale for construction. The site has not been chosen. Colors mark the five tracks and the shared spaces. Select a room to open it in the tour.</figcaption>
  </figure>
</section>

<section class="band" id="mission">
  <div class="mission-grid">
    <article>
      <h2>Mission</h2>
      <p class="statement"><?= h(azalea_mission()) ?></p>
    </article>
    <article>
      <h2>Vision</h2>
      <p class="statement"><?= h(azalea_vision()) ?></p>
    </article>
  </div>
</section>

<section id="need">
  <div class="section-head">
    <h2>The need in Lowndes County</h2>
    <p>These figures describe the county and the city. They are not a count of future members. Sources are the U.S. Census Bureau’s American Community Survey, Census QuickFacts, and FRED. Research notes are dated October 30, 2026.</p>
  </div>
  <div class="stat-grid">
    <?php foreach (azalea_need_stats() as $stat): ?>
      <article class="stat-card">
        <p class="stat-figure"><?= h($stat['figure']) ?></p>
        <h3><?= h($stat['label']) ?></h3>
        <p><?= h($stat['detail']) ?></p>
        <p class="source"><a href="<?= h($stat['url']) ?>"><?= h($stat['source']) ?></a></p>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<section>
  <div class="section-head">
    <h2>Programs already here, and the gap</h2>
    <p>The plan treats these as neighbors to work alongside, not as competitors, and not as confirmed partners. Nothing in the October 30, 2026 notes had been contacted.</p>
  </div>
  <ul class="program-list">
    <?php foreach (azalea_local_programs() as $program): ?>
      <li>
        <h3><?php if ($program['url'] !== ''): ?><a href="<?= h($program['url']) ?>"><?= h($program['name']) ?></a><?php else: ?><?= h($program['name']) ?><?php endif; ?></h3>
        <p><?= h($program['note']) ?></p>
        <?php if ($program['source_url'] !== '' && $program['source_url'] !== $program['url']): ?>
          <p class="source"><a href="<?= h($program['source_url']) ?>"><?= h($program['source_name']) ?></a></p>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
  <p class="callout">The gap the plan names: existing programs stop at high school, run as classes, or depend on eligibility. The drafts did not find one year-round drop-in place for 16- to 24-year-olds that combines arts, tech, trades, wellness, and resource navigation under one roof.</p>
</section>

<section id="tracks">
  <div class="section-head">
    <h2>Five tracks, one roof</h2>
    <p>The working plan is a free membership, with drop-in hours on afternoons, evenings, and Saturdays year-round, and cohort tracks of 8 to 12 weeks. Priority is young adults ages 16 to 24 in Valdosta and Lowndes County who are out of school, out of work, or living on a low income. The center is not open.</p>
  </div>
  <div class="track-grid">
    <?php foreach (azalea_tracks() as $track): ?>
      <article class="track-card" style="--track: <?= h($track['color']) ?>">
        <h3><?= h($track['name']) ?></h3>
        <p><?= h($track['summary']) ?></p>
      </article>
    <?php endforeach; ?>
  </div>
  <p class="fine"><a href="/tour/">Walk the concept building</a> to see how those tracks sit in a 12,000 square foot plan.</p>
</section>

<section class="cta-band">
  <div>
    <h2>The working budget, before a site is chosen</h2>
    <p>The lease-and-renovate draft prices a 12,000 sf full concept at <strong><?= h(azalea_money(2657074)) ?></strong> for startup plus the first year of operating, and a 4,750 sf first phase at <strong><?= h(azalea_money(857555)) ?></strong>. Those figures are planning costs from cited sources. They are not an amount being collected.</p>
  </div>
  <div class="cta-row">
    <a class="btn btn-primary" href="/support/">See how support would work</a>
    <a class="btn btn-secondary" href="/donors/">Founding Donors wall</a>
  </div>
</section>
<?php azalea_footer(); ?>
