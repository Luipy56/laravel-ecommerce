<?php

declare(strict_types=1);

/*
| PHPUnit bootstrap: testing database selection before Laravel boots.
|
| - mysql / mariadb / pgsql: DB_DATABASE is set to DB_TESTING_DATABASE (default schema name
|   ecommerce_testing) on the same host as .env.
| - sqlite: DB_DATABASE must be an absolute path or :memory:. A bare schema name is invalid;
|   default is :memory: (CI uses sqlite from .env.example). Set DB_TESTING_DATABASE to a
|   path like database/testing.sqlite if you need a file-based test DB.
*/

$projectRoot = dirname(__DIR__);

require $projectRoot.'/vendor/autoload.php';

if (is_file($projectRoot.'/.env')) {
    Dotenv\Dotenv::createImmutable($projectRoot)->safeLoad();
}

$connection = $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?? 'sqlite';
$connection = is_string($connection) ? strtolower($connection) : 'sqlite';

$testDbRaw = $_ENV['DB_TESTING_DATABASE'] ?? getenv('DB_TESTING_DATABASE');
$testDbRaw = is_string($testDbRaw) ? trim($testDbRaw) : '';

if (in_array($connection, ['mysql', 'mariadb', 'pgsql'], true)) {
    $testDb = $testDbRaw !== '' ? $testDbRaw : 'ecommerce_testing';
} elseif ($connection === 'sqlite') {
    if ($testDbRaw === ':memory:') {
        $testDb = ':memory:';
    } elseif ($testDbRaw !== '' && (
        str_contains($testDbRaw, '/')
        || str_contains($testDbRaw, '\\')
        || str_ends_with(strtolower($testDbRaw), '.sqlite')
    )) {
        $testDb = (str_starts_with($testDbRaw, '/') || (strlen($testDbRaw) > 2 && $testDbRaw[1] === ':'))
            ? $testDbRaw
            : $projectRoot.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($testDbRaw, '/\\'));
        $dir = dirname($testDb);
        if ($dir !== '' && ! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if (! is_file($testDb)) {
            touch($testDb);
        }
    } else {
        $testDb = ':memory:';
    }
} else {
    $testDb = $testDbRaw !== '' ? $testDbRaw : 'ecommerce_testing';
}

putenv('DB_DATABASE='.$testDb);
$_ENV['DB_DATABASE'] = $testDb;
$_SERVER['DB_DATABASE'] = $testDb;

/*
| Database sessions require a migrated `sessions` table. SQLite :memory: is a fresh schema per
| process unless migrations run before every request; pairing it with SESSION_DRIVER=database
| (from .env or cached config) causes "no such table: sessions". Align with phpunit.xml.
*/
if ($connection === 'sqlite' && $testDb === ':memory:') {
    putenv('SESSION_DRIVER=array');
    $_ENV['SESSION_DRIVER'] = 'array';
    $_SERVER['SESSION_DRIVER'] = 'array';
}
