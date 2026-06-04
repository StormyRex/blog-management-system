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

$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirmPassword = trim($_POST['confirmPassword'] ?? '');

$errors = [];

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if (empty($name)) {

    $errors['name'] = 'Name is required';

} elseif (!preg_match('/^[A-Za-z ]+$/', $name)) {

    $errors['name'] = 'Only alphabets and spaces allowed';

}

if (empty($username)) {

    $errors['username'] = 'Username is required';

} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {

    $errors['username'] = 'Invalid username';

}

if (empty($email)) {

    $errors['email'] = 'Email is required';

} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $errors['email'] = 'Invalid email';

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
| Unique Email
|--------------------------------------------------------------------------
*/

$emailExists = findUnique(
    'users',
    'email',
    $email
);

if ($emailExists) {

    $errors['email'] = 'Email already exists';

}

/*
|--------------------------------------------------------------------------
| Unique Username
|--------------------------------------------------------------------------
*/

$usernameExists = findUnique(
    'users',
    'username',
    $username
);

if ($usernameExists) {

    $errors['username'] = 'Username already exists';

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
| Create User
|--------------------------------------------------------------------------
*/

$hashedPassword = password_hash(
    $password,
    PASSWORD_BCRYPT
);

$created = execute(
    "INSERT INTO users
    (
        name,
        username,
        email,
        password,
        created_at
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        NOW()
    )",
    [
        $name,
        $username,
        $email,
        $hashedPassword
    ]
);

/*
|--------------------------------------------------------------------------
| Success Response
|--------------------------------------------------------------------------
*/

if ($created) {

    response_send([
        'success' => true,
        'status' => 200,
        'message' => 'Registration successful',
        'data' => [],
        'errors' => [],
        'redirect' => null
    ], 200);
}

/*
|--------------------------------------------------------------------------
| Server Error
|--------------------------------------------------------------------------
*/

response_send([
    'success' => false,
    'status' => 500,
    'message' => 'Something went wrong',
    'errors' => [],
    'data' => [],
    'redirect' => null
], 500);