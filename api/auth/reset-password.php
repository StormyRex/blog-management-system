<?php

require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';

/*
|--------------------------------------------------------------------------
| Only POST Allowed
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

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

$token = trim($_POST['token'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirmPassword = trim($_POST['confirmPassword'] ?? '');

$errors = [];

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if (empty($token)) {

    $errors['token'] = 'Token is required';

}

if (empty($password)) {

    $errors['password'] = 'Password is required';

} elseif (
    !preg_match(
        '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@_&])[A-Za-z\d@_&]{8,16}$/',
        $password
    )
) {

    $errors['password'] =
        'Password format invalid';

}

if ($password !== $confirmPassword) {

    $errors['confirmPassword'] =
        'Passwords do not match';

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
            id,
            user_id,
            expired_at
        FROM password_resets
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
| Update Password
|--------------------------------------------------------------------------
*/

$hashedPassword = password_hash(
    $password,
    PASSWORD_BCRYPT
);

$updated = execute(
    "
        UPDATE users
        SET password = ?
        WHERE id = ?
    ",
    [$hashedPassword, $reset['user_id']]
);

if (!$updated) {

    response_send([
        'success' => false,
        'status' => 500,
        'message' => 'Something went wrong',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ], 500);
}

/*
|--------------------------------------------------------------------------
| Cleanup Token
|--------------------------------------------------------------------------
*/

execute(
    "
        DELETE FROM password_resets
        WHERE user_id = ?
    ",
    [$reset['user_id']]
);

/*
|--------------------------------------------------------------------------
| Success Response
|--------------------------------------------------------------------------
*/

response_send([
    'success' => true,
    'status' => 200,
    'message' => 'Password reset successful',
    'errors' => [],
    'data' => [],
    'redirect' => null
], 200);
