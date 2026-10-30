<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/includes/bootstrap.php';

azalea_header([
    'title' => 'About | Azalea Yard',
    'description' => 'Azalea Yard is a planned youth arts and vocational center in Valdosta and Lowndes County, Georgia, founded by Anthony Jaamal Jones.',
    'path' => '/about/',
    'active' => 'about',
]);
$cfg = azalea_config();
$photo = azalea_founder_photo();
?>
<header class="page-head">
  <p class="eyebrow">About</p>
  <h1>A center in formation</h1>
  <p class="lede"><?= h(azalea_mission()) ?></p>
</header>

<section class="founder">
  <?php if ($photo !== null): ?>
    <img class="founder-photo" src="<?= h($photo) ?>" alt="Portrait of <?= h($cfg['founder_name']) ?>">
  <?php else: ?>
    <p class="monogram" aria-hidden="true">AJ</p>
  <?php endif; ?>
  <div>
    <h2><?= h($cfg['founder_name']) ?></h2>
    <p>Founder of Azalea Yard. He goes by <?= h($cfg['founder_short']) ?>.</p>
  </div>
</section>

<section>
  <h2>What is being built</h2>
  <p>Azalea Yard is planned as a year-round place for young adults ages 16 to 24 in Valdosta and Lowndes County. The five tracks are Create, Tech, Build, Well, and Navigate: studios and shops, a coding and printing lab, counseling and recreation, and a career and resource center.</p>
  <p>The vision: <?= h(azalea_vision()) ?></p>
  <p>The name comes from Valdosta’s nickname, the Azalea City. “Yard” is the hangout, the backyard, and the work yard. As of the October 30, 2026 planning drafts, the organization was not incorporated, a domain was not registered, and the notes say nothing had been published or contacted on its behalf.</p>
  <p>The building site has not been chosen. The <a href="/tour/">3D tour</a> is a concept allocation of 12,000 square feet on a 120 by 100 foot one-story footprint. It is not a construction drawing.</p>
</section>

<section>
  <h2>Who it is for</h2>
  <p>Young adults ages 16 to 24 in Valdosta and Lowndes County, with priority on those who are out of school, out of work, or living on a low income. The operating plan is a free membership, drop-in time on afternoons, evenings, and Saturdays, and skills cohorts of 8 to 12 weeks. None of that is open yet.</p>
  <p>Navigate, the career and resource track, is how the plan connects members to help that already exists: WIOA and WorkSource Southern Georgia, GED options, Wiregrass Georgia Technical College, Valdosta State University, health coverage, and housing. Those are referral paths in the plan, not signed partnerships.</p>
</section>

<section>
  <h2>How the plan would know it was working</h2>
  <ul>
    <li>Members enrolled, and the average number of visits in a week</li>
    <li>Credentials earned</li>
    <li>Job or apprenticeship placements at 90 and 180 days</li>
    <li>GED and college enrollments</li>
    <li>Referrals the navigator completes</li>
    <li>A wellness survey before and after participation</li>
    <li>Member-made work that is shown or sold</li>
  </ul>
  <p>There are no results to report. The center has not opened.</p>
</section>
<?php azalea_footer(); ?>
