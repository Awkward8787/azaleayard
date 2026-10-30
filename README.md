# Azalea Yard

Pre-launch website for Azalea Yard, a planned year-round youth arts and vocational center for young adults ages 16 to 24 in Valdosta / Lowndes County, Georgia. Founded by Anthony Jaamal Jones (Jaamal).

The site explains the plan, shows a concept 3D tour, and collects **interest** in the Founding Donor list. It does not take a payment.

Azalea Yard is in formation. Until `tax_status` in `config/site.php` is set to a real 501(c)(3) or fiscal sponsor **and** the matching legal name and EIN are filled in, every page says the organization is not yet accepting donations. The site does not claim that gifts are tax-deductible.

Figures and the mission and vision come from the October 30, 2026 planning drafts. The building site has not been chosen. The tour is a concept allocation of 12,000 square feet, not a construction drawing. A later note in the business-plan draft mentions a buy-and-build rebudget that was not part of this source set; this site does not use that note.

## Run it locally

PHP 8.0 or newer:

```bash
php -S 127.0.0.1:8080 -t public public/router.php
```

Open http://127.0.0.1:8080/

Checks that do not need a browser:

```bash
php tests/signup_test.php
```

The interest form waits about two seconds before it will accept a submit (a simple spam check). A successful submit writes a line to `storage/submissions/interest.jsonl` and a notification file to `storage/outbox/`. With `notify_email` empty, `mail()` is not called. That is expected on a laptop.

## Deploy to Hostinger

Shared hosting, static files plus PHP. No Node server and no build step.

1. Upload the project into the account, for example `~/azaleayard/`, keeping this layout:

   ```
   azaleayard/
     config/site.php
     includes/
     storage/
     public/          ← this is the only folder the web server should serve
   ```

2. Point the domain’s document root at `azaleayard/public`.

   In hPanel: **Domains → the domain → Document root**, and set it to `azaleayard/public`.

   If the panel will not change the document root, move the **contents** of `public/` into `public_html`, and move `config/`, `includes/`, and `storage/` into the home directory **next to** `public_html` (not inside it). The pages look for those folders one level above the public files. Do not put `config/` or `storage/` inside `public_html`.

3. Make `storage/` writable by PHP (`chmod 750 storage storage/submissions storage/rate storage/outbox`). The app creates `storage/form.key` on the first form view. Back that file up with the storage folder. If you delete it, open forms just need to be reloaded.

4. Edit `config/site.php` before you announce the site. See the list below.

5. Confirm these URLs do **not** open:

   - `https://your-domain/../storage/submissions/interest.jsonl`
   - `https://your-domain/config/site.php`

   `storage/.htaccess` denies access if that folder is ever placed inside the web root by mistake. The real protection is keeping it outside the document root.

6. Turn on compression (gzip or Brotli) for HTML, CSS, JavaScript, and JSON if it is not already on. `public/.htaccess` asks Apache for deflate on those types. The tour’s Three.js files are large before compression and are loaded only on the tour page.

### Where submissions land

| Path | What |
|---|---|
| `storage/submissions/interest.jsonl` | One JSON object per line: founding-donor interest and contact messages |
| `storage/outbox/<id>.txt` | The notification text, even when email is not sent |
| `storage/spam.jsonl` | Submissions that filled the hidden honeypot field |
| `storage/rate/` | Short-lived hashes used to limit repeats. Not IP addresses. |

Files are mode `0600`. They are gitignored. Do not commit them and do not copy a development `storage/` folder full of test entries onto the server.

Each record has a reference like `AY-1A2B3C4D`. There is no public list of names. The Founding Donors wall stays on **Available** until someone confirms a listing and the organization can accept support.

### Config values to set

All of these live in `config/site.php`. Leave a value empty rather than inventing one. Empty public fields are omitted from the pages.

| Key | Set it to |
|---|---|
| `notify_email` | Inbox that should receive form notifications. Empty means store the submission and skip `mail()`. |
| `mail_from` | A From address on the hosted domain. Hostinger often drops mail whose From is not on that domain. |
| `public_email` | Address to show on the contact page, or empty to hide it. |
| `public_phone` | Phone to show, or empty. |
| `mailing_address` | Postal address to show, or empty. The site is not chosen yet. |
| `founder_photo` | Path under `public/`, such as `assets/img/founder.jpg`. If the file is missing, the about page uses a monogram. |
| `site_url` | Public origin once a domain is registered, such as `https://example.org`. Leave empty until then. Do not assume a hostname. |
| `tax_status` | Keep `none` until there is a real determination or a fiscal sponsor. |
| `org_legal_name`, `org_ein` | Required together before `tax_status` of `501c3` changes any wording. |
| `sponsor_name`, `sponsor_ein` | Required together before `fiscal_sponsor` or `fractured_atlas` changes any wording. |

`tax_status` is the only switch for that sentence. A status without the matching name and EIN stays on the formation wording.

## Pages

| URL | Page |
|---|---|
| `/` | Home: mission, vision, cited local need, five tracks |
| `/tour/` | Concept tour. “Reserve interest in this space” opens the same signup, with that room or item selected. |
| `/support/` | Giving tiers and naming rules from the campaign plan |
| `/donors/` | Founding Donors wall. Every slot is Available. |
| `/about/` | Founder: Anthony Jaamal Jones, founder. |
| `/contact/` | Message form |
| `/join/` | Founding Donor interest form |
| `/privacy/` | What the forms collect and how it is used |

The tour keeps the line **Concept design, not to scale for construction.** `?view=list` is the still floor plan. Browsers that report reduced motion start on that plan; “Load the 3D tour” opts in. Browsers without WebGL stay on the plan. Jump to a room works from the keyboard.

The downloadable model is `public/tour/model/azalea-yard-concept.glb` (about 114 KB). It was quantized with gltfpack (`KHR_mesh_quantization`) from a 548 KB export. The disclaimer is in the asset extras. The page itself draws the model in code; it does not load the GLB to render.

Three.js r187 is vendored in `public/tour/vendor/` under the MIT license (`THREE-LICENSE`).

## Go-live checklist

1. Choose a site, then replace the concept plan and the budget. Do not keep presenting the lease-and-renovate figures as the ask after the building changes.
2. Have an architect review anything that would be built. Keep the “not to scale” line until drawings exist.
3. Incorporate, and either receive a 501(c)(3) determination or sign a fiscal sponsor. Only then set `tax_status` plus the legal name and EIN. Have a tax advisor review any acknowledgment before a gift is accepted. This repository does not include receipt templates.
4. Board approval of naming amounts, of every “proposed use,” and of a written naming policy (term, removal, wording).
5. A payment page, on a provider you choose, only after step 3. This site still should not take a payment until that exists, and it should not be bolted on quietly.
6. Moderate names before anything appears on the public wall.
7. Register a domain, set `site_url`, and set `notify_email` and `mail_from`. Send yourself a test form and confirm the message arrives.
