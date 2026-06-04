<?php

require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';

/*
|--------------------------------------------------------------------------
| Only GET Allowed
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

    response_send([
        'success' => false,
        'status' => 405,
        'message' => 'Method not allowed',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ], 405);
}

/*
|--------------------------------------------------------------------------
| Get Inputs
|--------------------------------------------------------------------------
*/

$token = trim($_GET['token'] ?? '');

$errors = [];

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if (empty($token)) {

    $errors['token'] = 'Token is required';

}

/*
|--------------------------------------------------------------------------
| Validation Response
|--------------------------------------------------------------------------
*/

if (!empty($errors)) {

    response_send([
        'success' => false,
        'status' => 422,
        'message' => 'Validation failed',
        'errors' => $errors,
        'data' => [],
        'redirect' => null
    ], 422);
}

/*
|--------------------------------------------------------------------------
| Lookup Token
|--------------------------------------------------------------------------
*/

$reset = fetchOne(
    "
        SELECT
            password_resets.id,
            password_resets.expired_at,
            users.email
        FROM password_resets
        INNER JOIN users
        ON users.id = password_resets.user_id
        WHERE token = ?
        LIMIT 1
    ",
    [$token]
);

$isExpired = $reset
    && strtotime($reset['expired_at']) < time();

if (!$reset || $isExpired) {

    response_send([
        'success' => false,
        'status' => 404,
        'message' => 'Invalid or expired token',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ], 404);
}

/*
|--------------------------------------------------------------------------
| Success Response
|--------------------------------------------------------------------------
*/

response_send([
    'success' => true,
    'status' => 200,
    'message' => 'Token verified successfully',
    'data' => [
        'email' => $reset['email']
    ],
    'errors' => [],
    'redirect' => null
], 200);
