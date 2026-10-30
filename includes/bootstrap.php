<?php
declare(strict_types=1);

define('AZALEA_ROOT', dirname(__DIR__));
define('AZALEA_PUBLIC', AZALEA_ROOT . '/public');
define('AZALEA_STORAGE', AZALEA_ROOT . '/storage');

function azalea_config(): array
{
    static $cfg;
    if ($cfg === null) {
        $path = AZALEA_ROOT . '/config/site.php';
        if (!is_file($path)) {
            http_response_code(500);
            echo 'Site configuration is missing.';
            exit;
        }
        $loaded = require $path;
        if (!is_array($loaded)) {
            http_response_code(500);
            echo 'Site configuration is invalid.';
            exit;
        }
        $cfg = $loaded;
    }
    return $cfg;
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function azalea_form_key(): string
{
    $path = AZALEA_STORAGE . '/form.key';
    if (!is_file($path)) {
        if (!is_dir(AZALEA_STORAGE)) {
            mkdir(AZALEA_STORAGE, 0750, true);
        }
        $key = bin2hex(random_bytes(32));
        file_put_contents($path, $key, LOCK_EX);
        chmod($path, 0600);
    }
    $key = trim((string) file_get_contents($path));
    if ($key === '') {
        http_response_code(500);
        echo 'Form key is missing.';
        exit;
    }
    return $key;
}

function azalea_base_url(): string
{
    $set = trim((string) (azalea_config()['site_url'] ?? ''));
    if ($set !== '') {
        return rtrim($set, '/');
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return ($https ? 'https' : 'http') . '://' . $host;
}

function azalea_secure_cookie(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => azalea_secure_cookie(),
        'use_strict_mode' => true,
    ]);
}

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');

require __DIR__ . '/status.php';
require __DIR__ . '/plan.php';
require __DIR__ . '/facts.php';
require __DIR__ . '/forms.php';
require __DIR__ . '/store.php';
require __DIR__ . '/floorplan.php';
require __DIR__ . '/layout.php';
