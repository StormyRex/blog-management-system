<?php

require 'vendor/autoload.php';

$routes = require 'api/Config/FrontendRouteConfig.php';

$url = $_GET['url'] ?? '/';

$url = '/' . trim($url, '/');

if ($url === '//') {
    $url = '/';
}

$routeTarget = $routes[$url] ?? null;
$routePath = null;

if ($routeTarget) {
    // Resolve route target path safely from the project root.
    $routePath = __DIR__ . '/' . ltrim($routeTarget, '/');
}

if ($routeTarget && file_exists($routePath)) {
    require $routePath;
} else {
    $errorPath = __DIR__ . '/pages/errors/404.php';
    http_response_code(404);

    if (file_exists($errorPath)) {
        require $errorPath;
    } else {
        echo '404 Not Found';
    }
}