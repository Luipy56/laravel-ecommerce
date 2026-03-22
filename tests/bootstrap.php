<?php

declare(strict_types=1);

/*
| PHPUnit bootstrap: point tests at a dedicated database on the same server as .env
| (DB_HOST, DB_USERNAME, …). Set DB_TESTING_DATABASE in .env to the remote schema name.
| Laravel's LoadEnvironmentVariables::safeLoad() will not overwrite DB_DATABASE if already set.
*/

$projectRoot = dirname(__DIR__);

require $projectRoot.'/vendor/autoload.php';

if (is_file($projectRoot.'/.env')) {
    Dotenv\Dotenv::createImmutable($projectRoot)->safeLoad();
}

$testDb = $_ENV['DB_TESTING_DATABASE'] ?? getenv('DB_TESTING_DATABASE');
if (! is_string($testDb) || $testDb === '') {
    $testDb = 'ecommerce_testing';
}

putenv('DB_DATABASE='.$testDb);
$_ENV['DB_DATABASE'] = $testDb;
$_SERVER['DB_DATABASE'] = $testDb;
