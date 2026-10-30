<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/includes/bootstrap.php';

azalea_header([
    'title' => 'How support works | Azalea Yard',
    'description' => 'Giving tiers and naming opportunities from the Azalea Yard campaign plan. Interest only: Azalea Yard is in formation and is not yet accepting donations.',
    'path' => '/support/',
    'active' => 'support',
]);

$plan = azalea_plan();
$tiers = $plan['tiers'];
$sources = azalea_tier_sources();
$budgets = azalea_budget_tables();

function azalea_money_table(array $rows): void
{
    echo '<table class="data"><thead><tr><th scope="col">Item</th><th scope="col">Basis</th><th scope="col">Amount</th></tr></thead><tbody>';
    foreach ($rows as $row) {
        [$item, $basis, $amount, $url] = $row;
        echo '<tr><th scope="row">' . h($item) . '</th><td>' . h($basis);
        if ($url !== '') {
            $label = str_starts_with($url, '/') ? 'Details' : 'Source';
            echo ' <a href="' . h($url) . '">' . $label . '</a>';
        }
        echo '</td><td class="num">' . h(azalea_money((int) $amount)) . '</td></tr>';
    }
    echo '</tbody></table>';
}
?>
<header class="page-head">
  <p class="eyebrow">Campaign plan</p>
  <h1>How support works</h1>
  <p class="lede">The tiers below are tied to costs in the October 30, 2026 draft budget. Joining the Founding Donor list records which tier or naming item you want to talk about. It does not collect money, and it does not reserve a name.</p>
</header>

<section>
  <h2>Giving tiers</h2>
  <table class="data">
    <thead>
      <tr>
        <th scope="col">Tier</th>
        <th scope="col">Amount</th>
        <th scope="col">What the plan says it funds</th>
        <th scope="col">Interest</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tiers as $tier): ?>
        <tr>
          <th scope="row"><?= h($tier['name']) ?></th>
          <td class="num"><?= h(azalea_tier_amount($tier)) ?></td>
          <td>
            <?= h($tier['funds']) ?>
            <?php if (($sources[$tier['id']] ?? '') !== ''): ?>
              <a href="<?= h($sources[$tier['id']]) ?>">Source</a>
            <?php else: ?>
              <span class="fine">The Founding Partner amount is a proposal in the campaign plan.</span>
            <?php endif; ?>
          </td>
          <td><a href="/join/?interest=<?= h('tier:' . $tier['id']) ?>">Reserve interest</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p class="fine">Seed is $85 beside a $84.01 table. Maker is $480 beside a $479.99 printer. Studio is $1,500 beside a $1,479 workstation. Kiln Club is $2,500 beside a $2,499 kiln. Mentor is $31,000 beside a BLS Valdosta mean wage of $30,910. The plan rounds the tier to the item.</p>
</section>

<section>
  <h2>Naming</h2>
  <p>The campaign plan ties Founding Partner naming to studios and labs. In the concept model those are the art studio, ceramics studio, music and recording booths, screening room, film and media studio, computer lab, 3D printing lab, and wood and metal shop.</p>
  <p>Naming a wing, the lounge, the counseling rooms, the lobby, the offices, fitness, the career center, or the outdoor court is marked <strong>proposed use</strong>. The plan does not set a separate wing amount, so the model uses the Founding Partner tier and flags it for board approval. A written gift and naming policy, covering term, removal, and wording, is still ahead of any listing.</p>
  <p>The main corridor, the restrooms, and the parking lot are not naming opportunities. The outdoor court and the parking lot have no line in the budget, so the tour marks them “Not priced.”</p>
  <p><a href="/donors/">Every slot on the Founding Donors wall is Available.</a> No names are shown, because none have been accepted.</p>
</section>

<section id="recognition">
  <h2>Named or anonymous</h2>
  <p>When you reserve interest you choose one of two things: show a name you write, or stay anonymous. Anonymous records use the words “Anonymous Friend of Azalea Yard.”</p>
  <p>That choice is stored with your interest. It is not published. A name goes on the public wall only if you later confirm it, and only once Azalea Yard is able to accept support.</p>
  <p><a class="btn btn-primary" href="/join/">Join the Founding Donor list</a></p>
</section>

<section id="budget">
  <h2>What the draft budget prices</h2>
  <p>Two concept scales are in the plan. Rent figures come from published Valdosta listings used as references. They are not a selected address. Wages are BLS Occupational Employment and Wage Statistics mean wages for May 2025, mostly for the Valdosta metropolitan area. The electricity rate is the Georgia commercial price cited for August 2026.</p>
  <?php foreach (['full', 'phase'] as $key): $b = $budgets[$key]; ?>
    <h3><?= h($b['title']) ?></h3>
    <p><?= h($b['note']) ?></p>
    <div class="summary-row">
      <article><strong><?= h(azalea_money($b['startup_total'])) ?></strong><span>Startup</span></article>
      <article><strong><?= h(azalea_money($b['ops_total'])) ?></strong><span>Year-1 operating</span></article>
      <article><strong><?= h(azalea_money($b['combined'])) ?></strong><span>Startup + year 1</span></article>
      <article><strong><?= h(azalea_money($b['equipment'])) ?></strong><span>Itemized equipment</span></article>
    </div>
    <details>
      <summary>Startup lines for <?= h($b['title']) ?></summary>
      <?php azalea_money_table($b['startup']); ?>
    </details>
    <details>
      <summary>Year-1 operating lines for <?= h($b['title']) ?></summary>
      <?php azalea_money_table($b['operating']); ?>
    </details>
  <?php endforeach; ?>
</section>

<section id="equipment">
  <h2>Equipment the tiers point at</h2>
  <p>The full concept itemizes <?= h(azalea_money(81633)) ?> of equipment and furniture, including 20 media workstations, 4 printers, 2 kilns, 8 pottery wheels, and the shop tools. Phase 1 itemizes <?= h(azalea_money(40478)) ?>. The <a href="/tour/">concept tour</a> splits those lines across rooms and links each one to the store or wage source used in <code>budget.json</code>.</p>
  <p>The full staffing plan is 13 roles and 16 full-time-equivalent positions. Phase 1 is 8 roles and 7.0 FTE. Both use the BLS means linked above. Benefits are estimated, not quoted from an insurer, at 42.9% of wages.</p>
</section>

<section>
  <h2>Not priced yet</h2>
  <p>The plan says to add these before a final ask:</p>
  <ul>
    <?php foreach (azalea_unpriced() as $item): ?>
      <li><?= h($item) ?></li>
    <?php endforeach; ?>
  </ul>
</section>

<section>
  <h2>Planning targets for a future mix</h2>
  <p>These shares are planning targets in the business plan, not money raised and not a forecast.</p>
  <ul class="mix">
    <?php foreach (azalea_funding_mix() as [$label, $share]): ?>
      <li><strong><?= h($share) ?></strong> <?= h($label) ?></li>
    <?php endforeach; ?>
  </ul>
  <p class="fine">Earned revenue in the plan means studio rentals, event space, commissioned media work, and workshops. The plan cites the Boys &amp; Girls Club of Valdosta, which drew about 90% of FY2023 revenue from contributions ($3.05 million of $3.39 million), as a comparison, not as a projection for Azalea Yard. <a href="https://projects.propublica.org/nonprofits/organizations/237067775">ProPublica Nonprofit Explorer</a>.</p>
</section>
<?php azalea_footer(); ?>
