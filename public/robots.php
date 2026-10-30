<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
header('Content-Type: text/plain; charset=UTF-8');
$base = azalea_base_url();
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /join/thanks.php\n";
echo "\nSitemap: {$base}/sitemap.xml\n";
