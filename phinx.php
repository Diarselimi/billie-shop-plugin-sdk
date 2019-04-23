<?php

use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/vendor/autoload.php';

if (!isset($_SERVER['DATABASE_NAME'])) {
    if (!class_exists(Dotenv::class)) {
        throw new \RuntimeException('Database connection parameters are missing');
    }

    (new Dotenv())->load(__DIR__.'/.env');
}

return [
    'environments' => [
        'default' => [
            'name' => getenv('DATABASE_NAME'),
            'host' => getenv('DATABASE_HOST'),
            'user' => getenv('DATABASE_USERNAME'),
            'pass' => getenv('DATABASE_PASSWORD'),
            'port' => getenv('DATABASE_PORT'),
            'charset' => 'utf8',
            'adapter' => 'mysql',
        ],
    ],
    'paths' => [
        'migrations' => [
            '%%PHINX_CONFIG_DIR%%/db/migrations/common',
            '%%PHINX_CONFIG_DIR%%/db/migrations/'.getenv('APP_ENV'),
        ],
        'seeds' => [
            '%%PHINX_CONFIG_DIR%%/db/seeds/common',
            '%%PHINX_CONFIG_DIR%%/db/seeds/'.getenv('APP_ENV'),
        ],
    ],
];
