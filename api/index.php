<?php

/*
 * Vercel serverless entrypoint for Laravel.
 *
 * Vercel's filesystem is read-only except for /tmp, so compiled Blade views are
 * redirected there (cache=array, sessions=cookie, logs=stderr are set via env).
 */

$tmpViews = '/tmp/views';
if (! is_dir($tmpViews)) {
    @mkdir($tmpViews, 0755, true);
}
putenv("VIEW_COMPILED_PATH={$tmpViews}");
$_ENV['VIEW_COMPILED_PATH'] = $_SERVER['VIEW_COMPILED_PATH'] = $tmpViews;

require __DIR__ . '/../bootstrap/request.php';
