<?php
define('SUPABASE_URL',              getenv('SUPABASE_URL')              ?: '');
define('SUPABASE_ANON_KEY',         getenv('SUPABASE_ANON_KEY')         ?: '');
define('SUPABASE_SERVICE_ROLE_KEY', getenv('SUPABASE_SERVICE_ROLE_KEY') ?: '');
define('SUPABASE_JWT_SECRET',       getenv('SUPABASE_JWT_SECRET')       ?: '');

define('AUTH_COOKIE',         'vivire_token');
define('REFRESH_COOKIE',      'vivire_refresh');
define('STORAGE_BUCKET',      'journal-media');

define('TODAY_SECTIONS', [
    ['id' => 'feelings',    'label' => 'Cómo me sentí',  'placeholder' => 'Describe cómo te sentiste hoy…'],
    ['id' => 'thoughts',    'label' => 'Qué pensé',       'placeholder' => 'Qué pensamientos tuviste hoy…'],
    ['id' => 'reflections', 'label' => 'Reflexiones',     'placeholder' => 'Reflexiona sobre el día…'],
]);
