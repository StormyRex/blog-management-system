<?php

require 'vendor/autoload.php';

$routes = require 'api/Config/FrontendRouteConfig.php';

$url = $_GET['url'] ?? '/';

$url = '/' . trim($url, '/');

if ($url === '//') {
    $url = '/';
}

$routeTarget = $routes[$url] ?? null;

if (is_array($routeTarget) && count($routeTarget) === 2) {
    [$controllerClass, $method] = $routeTarget;

    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();

        if (method_exists($controller, $method)) {
            $controller->$method();
            return;
        }
    }
}

$errorPath = __DIR__ . '/pages/errors/404.php';
http_response_code(404);

if (is_file($errorPath)) {
    require $errorPath;
} else {
    echo '404 Not Found';
}