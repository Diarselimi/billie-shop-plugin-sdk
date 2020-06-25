<?php

use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

if (!isset($_SERVER['DATABASE_NAME'])) {
    if (!class_exists(Dotenv::class)) {
        throw new \RuntimeException('Database connection parameters are missing');
    }

    (new Dotenv())->load(__DIR__ . '/.env');
}

$db = [
    'name' => getenv('DATABASE_NAME'),
    'host' => getenv('DATABASE_HOST'),
    'user' => getenv('DATABASE_USERNAME'),
    'pass' => getenv('DATABASE_PASSWORD'),
    'port' => getenv('DATABASE_PORT'),
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'adapter' => 'mysql',
];

$env = getenv('APP_ENV') ?: 'dev';

return [
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default' => $db,
        $env => $db,
    ],
    'paths' => [
        'migrations' => [
            '%%PHINX_CONFIG_DIR%%/db/migrations/common',
            '%%PHINX_CONFIG_DIR%%/db/migrations/' . $env,
        ],
        'seeds' => [
            '%%PHINX_CONFIG_DIR%%/db/seeds/common',
            '%%PHINX_CONFIG_DIR%%/db/seeds/' . $env,
        ],
    ],
];
