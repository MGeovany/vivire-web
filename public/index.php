<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

$dispatcher = simpleDispatcher(function (RouteCollector $r): void {
    $r->addRoute(['GET', 'POST'], '/',         'home');
    $r->addRoute(['GET', 'POST'], '/login',    'login');
    $r->addRoute(['GET', 'POST'], '/register', 'register');
    $r->addRoute(['GET', 'POST'], '/logout',   'logout');
    $r->addRoute('POST',          '/api/save',   'api_save');
    $r->addRoute('POST',          '/api/upload', 'api_upload');
});

$method = $_SERVER['REQUEST_METHOD'];
$uri    = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$route  = $dispatcher->dispatch($method, $uri);

switch ($route[0]) {
    case Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo '404 Not Found';
        break;

    case Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo '405 Method Not Allowed';
        break;

    case Dispatcher::FOUND:
        $root = dirname(__DIR__);
        match ($route[1]) {
            'home'       => require "$root/handlers/home.php",
            'login'      => require "$root/auth/login.php",
            'register'   => require "$root/auth/register.php",
            'logout'     => require "$root/auth/logout.php",
            'api_save'   => require "$root/api/save.php",
            'api_upload' => require "$root/api/upload.php",
        };
        break;
}
