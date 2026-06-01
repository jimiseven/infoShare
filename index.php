<?php
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';

date_default_timezone_set(APP_TIMEZONE);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => SESSION_SECURE,
    'httponly' => true,
    'samesite' => SESSION_SAMESITE,
]);
ini_set('session.cookie_lifetime', '0');
ini_set('session.gc_maxlifetime', '43200');

session_start();

$app = require __DIR__ . '/bootstrap/app.php';
$router = $app['router'];

$route = $_GET['r'] ?? null;
if ($route === null || trim((string)$route) === '') {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
    $normalizedPath = str_replace('\\', '/', (string)$uriPath);
    if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($normalizedPath, $scriptDir)) {
        $normalizedPath = substr($normalizedPath, strlen($scriptDir));
    }
    $normalizedPath = trim((string)$normalizedPath, '/');
    $route = $normalizedPath !== '' ? $normalizedPath : 'dashboard';
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$key = strtoupper($method) === 'POST' ? ($route . '.post') : $route;

$router->dispatch($key, $method);
