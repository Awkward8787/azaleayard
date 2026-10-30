<?php
/**
 * Azalea Yard site configuration.
 *
 * This file lives outside the public web folder. Set the values Jaamal
 * has to supply before the site is published. Leave a value empty rather
 * than inventing an address, a photo, or an email. Empty public fields
 * are omitted from the pages.
 *
 * tax_status controls the sentence shown on every page.
 *   "none"            Current state. The site says Azalea Yard is in
 *                     formation and is not accepting donations. It must
 *                     not say gifts are tax-deductible, and it must not
 *                     claim 501(c)(3) status or a fiscal sponsor.
 *   "501c3"           Used only together with org_legal_name AND org_ein.
 *   "fiscal_sponsor"  Used only together with sponsor_name AND sponsor_ein.
 *   "fractured_atlas" Treated as a fiscal sponsor, and only if the
 *                     sponsor name and EIN are both filled in.
 * If the required name or EIN is blank, the site stays on "none".
 */
return [
    'org_name' => 'Azalea Yard',
    'founder_name' => 'Anthony Jaamal Jones',
    'founder_short' => 'Jaamal',
    'place' => 'Valdosta / Lowndes County, Georgia',

    // Public site URL after a domain is registered. Leave empty until then.
    // Do not assume a hostname; azaleayard.org was unregistered as of Oct 30, 2026.
    'site_url' => '',

    // Inbox for form notifications. Required before launch if mail should send.
    'notify_email' => '',

    // From address for those notifications. On Hostinger this should be an
    // address on the hosted domain, or mail() may drop the message.
    'mail_from' => '',

    // Shown on the contact page only when set to a real address.
    'public_email' => '',

    // Shown only when set. Do not invent a number.
    'public_phone' => '',

    // Shown only when set. The site has not been chosen.
    'mailing_address' => '',

    // Optional photo of the founder, a path under public/ such as
    // "assets/img/founder.jpg". If the file is missing, the about page
    // uses a monogram and does not show a broken image.
    'founder_photo' => '',

    'tax_status' => 'none',
    'org_legal_name' => '',
    'org_ein' => '',
    'sponsor_name' => '',
    'sponsor_ein' => '',

    'timezone' => 'America/New_York',
];
