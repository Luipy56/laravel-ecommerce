<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// GSC: never serve the shop under /index.php (duplicate of /). Use $REQUEST_URI only —
// internal front-controller dispatches keep the original path (e.g. /products).
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
if (preg_match('#^/index\.php(?:\?|$)#', $requestUri) === 1) {
    $query = parse_url($requestUri, PHP_URL_QUERY);
    $target = '/'.($query ? ('?'.$query) : '');
    header('Location: '.$target, true, 301);
    exit;
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
