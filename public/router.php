<?php
declare(strict_types=1);

// Used only by the built-in PHP server: php -S 127.0.0.1:8080 -t public public/router.php
// Apache on Hostinger uses public/.htaccess instead.
$uri = urldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$root = __DIR__;

if ($uri === '/robots.txt') {
    require $root . '/robots.php';
    return true;
}
if ($uri === '/sitemap.xml') {
    require $root . '/sitemap.php';
    return true;
}

$candidate = realpath($root . $uri);
if ($uri !== '/' && $candidate !== false && str_starts_with($candidate, $root) && is_file($candidate)) {
    return false;
}

$index = rtrim($root . $uri, '/') . '/index.php';
if (is_file($index)) {
    require $index;
    return true;
}

http_response_code(404);
require $root . '/404.php';
return true;
