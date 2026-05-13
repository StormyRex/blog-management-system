<?php

require 'vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
}

$frontendRoutes = [
    '/' => [
        \Shive\BlogManagementSystem\Controllers\HomeController::class,
        'index'
    ],
    '/explore' => [
        \Shive\BlogManagementSystem\Controllers\HomeController::class,
        'explore'
    ],
    '/contact-us' => [
        \Shive\BlogManagementSystem\Controllers\HomeController::class,
        'contact'
    ],
    '/login' => [
        \Shive\BlogManagementSystem\Controllers\AuthController::class,
        'login'
    ],
    '/register' => [
        \Shive\BlogManagementSystem\Controllers\AuthController::class,
        'register'
    ],
    '/logout' => [
        \Shive\BlogManagementSystem\Controllers\AuthController::class,
        'logout'
    ],
    '/user/profile' => [
        \Shive\BlogManagementSystem\Controllers\UserController::class,
        'profile'
    ],
    '/user/dashboard' => [
        \Shive\BlogManagementSystem\Controllers\UserController::class,
        'dashboard'
    ],
    '/blogs/my-blogs' => [
        \Shive\BlogManagementSystem\Controllers\BlogController::class,
        'myBlogs'
    ],
    '/blogs/create' => [
        \Shive\BlogManagementSystem\Controllers\BlogController::class,
        'create'
    ],
    '/admin/dashboard' => [
        \Shive\BlogManagementSystem\Controllers\AdminController::class,
        'dashboard'
    ],
    '/upload' => [
        \Shive\BlogManagementSystem\Controllers\UploadPageController::class,
        'index'
    ]
];

$apiRoutes = require 'api/Routes/ApiRouteConfig.php';

$routes = array_merge($frontendRoutes, $apiRoutes);

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