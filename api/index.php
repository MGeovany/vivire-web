<?php

/*
 * Vercel serverless entrypoint for Laravel.
 */

$tmpViews = '/tmp/views';
if (! is_dir($tmpViews)) {
    @mkdir($tmpViews, 0755, true);
}
putenv("VIEW_COMPILED_PATH={$tmpViews}");
$_ENV['VIEW_COMPILED_PATH'] = $_SERVER['VIEW_COMPILED_PATH'] = $tmpViews;

if (! getenv('APP_KEY')) {
    error_log('[vivire] Missing APP_KEY. Add it in Vercel → Settings → Environment Variables.');
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Configuración incompleta: falta APP_KEY en Vercel.';
    exit;
}

try {
    require __DIR__.'/../bootstrap/request.php';
} catch (Throwable $e) {
    error_log('[vivire] '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
    http_response_code(500);

    if (filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL)) {
        throw $e;
    }

    header('Content-Type: text/plain; charset=utf-8');
    echo 'Error del servidor. Revisa los logs en Vercel.';
}
