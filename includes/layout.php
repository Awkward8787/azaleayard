<?php
declare(strict_types=1);

function azalea_mark(): string
{
    return '<svg class="mark" viewBox="0 0 64 64" aria-hidden="true" focusable="false">'
        . '<circle cx="32" cy="32" r="30" fill="#f8e4ec"/>'
        . '<g fill="#c44b73">'
        . '<ellipse cx="32" cy="14" rx="7.5" ry="12"/>'
        . '<ellipse cx="32" cy="14" rx="7.5" ry="12" transform="rotate(72 32 32)"/>'
        . '<ellipse cx="32" cy="14" rx="7.5" ry="12" transform="rotate(144 32 32)"/>'
        . '<ellipse cx="32" cy="14" rx="7.5" ry="12" transform="rotate(216 32 32)"/>'
        . '<ellipse cx="32" cy="14" rx="7.5" ry="12" transform="rotate(288 32 32)"/>'
        . '</g>'
        . '<circle cx="32" cy="32" r="6.5" fill="#1e4638"/>'
        . '</svg>';
}

function azalea_nav(): array
{
    return [
        'home' => ['/', 'Home'],
        'tour' => ['/tour/', 'Concept tour'],
        'support' => ['/support/', 'How support works'],
        'donors' => ['/donors/', 'Founding Donors'],
        'about' => ['/about/', 'About'],
        'contact' => ['/contact/', 'Contact'],
    ];
}

function azalea_header(array $page): void
{
    $cfg = azalea_config();
    $title = (string) ($page['title'] ?? $cfg['org_name']);
    $description = (string) ($page['description'] ?? '');
    $path = (string) ($page['path'] ?? '/');
    $active = (string) ($page['active'] ?? '');
    $bare = !empty($page['bare']);
    $robots = (string) ($page['robots'] ?? 'index,follow');
    $GLOBALS['azalea_page_bare'] = $bare;
    $base = azalea_base_url();
    $canonical = $base . $path;
    $image = $base . '/assets/img/og.png';
    $notice = azalea_status_notice();
    $org = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $cfg['org_name'],
        'description' => azalea_mission(),
        'areaServed' => 'Valdosta, Lowndes County, Georgia',
        'founder' => [
            '@type' => 'Person',
            'name' => $cfg['founder_name'],
        ],
    ];
    if (trim((string) $cfg['site_url']) !== '') {
        $org['url'] = rtrim((string) $cfg['site_url'], '/') . '/';
    }

    echo '<!doctype html><html lang="en"><head>';
    echo '<meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($title) . '</title>';
    echo '<meta name="description" content="' . h($description) . '">';
    echo '<meta name="robots" content="' . h($robots) . '">';
    echo '<link rel="canonical" href="' . h($canonical) . '">';
    echo '<meta property="og:title" content="' . h($title) . '">';
    echo '<meta property="og:description" content="' . h($description) . '">';
    echo '<meta property="og:type" content="website">';
    echo '<meta property="og:url" content="' . h($canonical) . '">';
    echo '<meta property="og:image" content="' . h($image) . '">';
    echo '<meta property="og:locale" content="en_US">';
    echo '<meta name="twitter:card" content="summary_large_image">';
    echo '<meta name="theme-color" content="#16342a">';
    echo '<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">';
    echo '<link rel="stylesheet" href="/assets/css/site.css">';
    if (!empty($page['extra_css'])) {
        echo '<link rel="stylesheet" href="' . h((string) $page['extra_css']) . '">';
    }
    echo '<script type="application/ld+json">' . json_encode($org, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    echo '</head><body class="' . h((string) ($page['body_class'] ?? '')) . '">';
    echo '<a class="skip" href="#main">Skip to content</a>';
    echo '<header class="site-header">';
    echo '<div class="header-bar">';
    echo '<a class="brand" href="/">' . azalea_mark() . '<span><strong>' . h($cfg['org_name']) . '</strong><small>' . h($cfg['place']) . '</small></span></a>';
    echo '<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">Menu</button>';
    echo '<nav id="site-nav" aria-label="Primary">';
    foreach (azalea_nav() as $key => $item) {
        $current = $key === $active ? ' aria-current="page"' : '';
        echo '<a href="' . h($item[0]) . '"' . $current . '>' . h($item[1]) . '</a>';
    }
    echo '<a class="btn btn-primary" href="/join/">Join the list</a>';
    echo '</nav></div></header>';
    echo '<div class="banner" role="note"><p><strong>' . h($notice['title']) . '</strong> ' . h($notice['body']) . '</p></div>';
    if (!$bare) {
        echo '<main id="main" class="wrap">';
    }
}

function azalea_footer(): void
{
    $cfg = azalea_config();
    $bare = !empty($GLOBALS['azalea_page_bare']);
    if (!$bare) {
        echo '</main>';
    }
    $notice = azalea_status_notice();
    echo '<footer class="site-footer"><div class="wrap footer-grid">';
    echo '<div><a class="brand brand-footer" href="/">' . azalea_mark() . '<strong>' . h($cfg['org_name']) . '</strong></a>';
    echo '<p>' . h($notice['title']) . '</p></div>';
    echo '<div><p class="footer-label">On this site</p><ul>';
    foreach (azalea_nav() as $item) {
        echo '<li><a href="' . h($item[0]) . '">' . h($item[1]) . '</a></li>';
    }
    echo '<li><a href="/join/">Join the Founding Donor list</a></li>';
    echo '<li><a href="/privacy/">Privacy policy</a></li>';
    echo '</ul></div>';
    echo '<div><p class="footer-label">Concept</p>';
    echo '<p>Concept design, not to scale for construction. The site has not been chosen. Room sizes in the tour are a concept allocation of the plan’s 12,000 square feet.</p>';
    echo '<p>Founded by ' . h($cfg['founder_name']) . '.</p></div>';
    echo '</div></footer>';
    echo '<script src="/assets/js/site.js"></script>';
    echo '</body></html>';
}

function azalea_public_email(): ?string
{
    $email = trim((string) (azalea_config()['public_email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return null;
    }
    return $email;
}

function azalea_founder_photo(): ?string
{
    $rel = trim((string) (azalea_config()['founder_photo'] ?? ''));
    if ($rel === '' || str_contains($rel, '..')) {
        return null;
    }
    $rel = ltrim($rel, '/');
    if (!is_file(AZALEA_PUBLIC . '/' . $rel)) {
        return null;
    }
    return '/' . $rel;
}
