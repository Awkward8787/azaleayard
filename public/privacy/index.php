<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/includes/bootstrap.php';

azalea_header([
    'title' => 'Privacy policy | Azalea Yard',
    'description' => 'What the Azalea Yard interest and contact forms collect, how it is stored, and how to ask for it to be deleted.',
    'path' => '/privacy/',
    'active' => '',
]);
$email = azalea_public_email();
?>
<header class="page-head">
  <p class="eyebrow">Privacy</p>
  <h1>Privacy policy</h1>
  <p class="lede">Last updated October 30, 2026. This is a plain-language description of what this website does with form submissions. It is not a legal opinion.</p>
</header>

<section>
  <h2>Who this is</h2>
  <p>Azalea Yard is a planned youth arts and vocational center for ages 16 to 24 in Valdosta and Lowndes County, Georgia. It is in formation. Anthony Jaamal Jones (Jaamal) is the founder. <?= h(azalea_status_notice()['title']) ?></p>
  <?php if ($email !== null): ?>
    <p>Public email: <a href="mailto:<?= h($email) ?>"><?= h($email) ?></a>.</p>
  <?php else: ?>
    <p>A public email address is not published yet. Use the <a href="/contact/">contact form</a>.</p>
  <?php endif; ?>
</section>

<section>
  <h2>What the Founding Donor form collects</h2>
  <ul>
    <li>Your name</li>
    <li>Your email address</li>
    <li>A phone number, if you give one</li>
    <li>An optional interest: an amount range from the campaign plan, or a specific naming item</li>
    <li>A recognition preference: show a display name you write, or stay anonymous</li>
    <li>That display name, if you choose to be named</li>
    <li>Two consents: that Azalea Yard may contact you about the interest, and that you have read this policy</li>
  </ul>
  <p>The form does not collect a payment, a card number, or a bank account. Choosing a display name does not publish it. The <a href="/donors/">Founding Donors wall</a> stays on Available until you later confirm a listing and Azalea Yard can accept support.</p>
</section>

<section>
  <h2>What the contact form collects</h2>
  <ul>
    <li>Your name, email, and an optional phone number</li>
    <li>Your message</li>
    <li>The same two consents, applied to that message</li>
  </ul>
</section>

<section>
  <h2>What is stored besides the answers</h2>
  <p>Each saved submission gets a reference code and a timestamp in UTC. To slow spam, the server keeps a hash of your IP address, mixed with a private key, for up to an hour, and counts submissions from that hash. The IP address itself is not written into the interest record. The hash file is deleted after it is a couple of days old.</p>
  <p>A hidden field labeled “Company website” is there for automated spam. People should leave it blank. Submissions that fill it are kept in a separate spam file so a real note is not thrown away, and they are not treated as interest.</p>
</section>

<section>
  <h2>How it is used</h2>
  <p>The organizer uses the submission to reply to you and to plan the Founding Donor list. A copy is also written as a notification file on the server. If a notification email address is set in the site configuration, the same text is sent to that address. If the address is not set yet, the submission is still saved and no email goes out.</p>
  <p>The records are not sold. They are not sent to a payment company. This website does not use a payment company.</p>
  <p>On the host, submissions are written outside the public website folder when the site is deployed as documented. Until then, on a local copy, they sit in the project’s storage folder, which is not part of the public pages.</p>
</section>

<section>
  <h2>How long it is kept</h2>
  <p>Records stay until you ask for them to be deleted, or until they are no longer needed to plan the center. To ask for a copy or for deletion, use the <a href="/contact/">contact form</a> and include the email address you used. Say that you want your interest or message deleted. There is no separate account login.</p>
</section>

<section>
  <h2>Ages</h2>
  <p>The center is being planned for young adults ages 16 to 24. These forms are for people contacting the organizer or recording interest in supporting the center. If you are under 18, submit a form only with a parent or guardian.</p>
</section>

<section>
  <h2>Changes</h2>
  <p>If the practice changes, this page will change and the date at the top will change. The status sentence shown on every page stays as it is until the organizer records, in the server configuration, both a legal status that allows different wording and the legal name and identification number that go with it.</p>
</section>
<?php azalea_footer(); ?>
