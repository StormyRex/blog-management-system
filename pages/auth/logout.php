<?php

if (session_status() === PHP_SESSION_NONE) {

    session_start();

}

$isAdmin = false;

if (
    !empty($_SESSION['user']) &&
    ($_SESSION['user']['role'] ?? '') === 'ADMIN'
) {

    $isAdmin = true;

} else {

    $isAdmin = false;
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );

}

session_destroy();

$baseUrl = defined('BASE_URL') ? BASE_URL : '';

if ($isAdmin) {
    header('Location: ' . $baseUrl . '/admin/login');
} else {
    header('Location: ' . $baseUrl . '/auth/login');
}

exit;
