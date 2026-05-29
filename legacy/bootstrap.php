<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

if (is_file(__DIR__ . '/.env')) {
    Dotenv\Dotenv::createImmutable(__DIR__)->load();
}

require __DIR__ . '/config.php';
require __DIR__ . '/lib/supabase.php';
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/entries.php';
require __DIR__ . '/lib/blocks.php';
require __DIR__ . '/lib/date_helpers.php';
require __DIR__ . '/templates/layout.php';
