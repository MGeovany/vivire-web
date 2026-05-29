<?php
// phpdotenv createImmutable() writes to $_ENV — check there first, then getenv() fallback
function env(string $key, string $default = ''): string {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

define('SUPABASE_URL',              env('SUPABASE_URL'));
define('SUPABASE_ANON_KEY',         env('SUPABASE_ANON_KEY'));
define('SUPABASE_SERVICE_ROLE_KEY', env('SUPABASE_SERVICE_ROLE_KEY'));
define('SUPABASE_JWT_SECRET',       env('SUPABASE_JWT_SECRET'));

/** Base URL for auth redirects (email confirm, etc.) */
define('APP_URL', env('APP_URL', 'http://localhost:8080'));

define('AUTH_COOKIE',    'vivire_token');
define('REFRESH_COOKIE', 'vivire_refresh');
define('STORAGE_BUCKET', 'journal-media');
define('IS_DEV',         is_file(dirname(__FILE__) . '/.env'));

define('TODAY_SECTIONS', [
    ['id' => 'feelings',    'label' => 'Cómo me sentí',  'placeholder' => 'Describe cómo te sentiste hoy…'],
    ['id' => 'thoughts',    'label' => 'Qué pensé',       'placeholder' => 'Qué pensamientos tuviste hoy…'],
    ['id' => 'reflections', 'label' => 'Reflexiones',     'placeholder' => 'Reflexiona sobre el día…'],
]);
