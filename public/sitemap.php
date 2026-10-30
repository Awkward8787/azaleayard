<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
header('Content-Type: application/xml; charset=UTF-8');
$base = azalea_base_url();
$paths = ['/', '/tour/', '/support/', '/donors/', '/about/', '/contact/', '/privacy/', '/join/'];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($paths as $path) {
    echo '  <url><loc>' . h($base . $path) . '</loc></url>' . "\n";
}
echo '</urlset>';
