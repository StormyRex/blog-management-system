<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    response_send([
        'success' => false,
        'status' => 405,
        'message' => 'Method not allowed',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ]);
}

if (empty($_SESSION['pending_admin_id'])) {

    response_send([
        'success' => false,
        'status' => 401,
        'message' => 'Session expired',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ]);
}

$otp = trim($_POST['otp'] ?? '');

$errors = [];

if ($otp === '') {

    $errors['otp'] = 'OTP is required';
}

if (!empty($errors)) {

    response_send([
        'success' => false,
        'status' => 422,
        'message' => 'Validation failed',
        'errors' => $errors,
        'data' => [],
        'redirect' => null
    ]);
}

$userId = $_SESSION['pending_admin_id'];

$otpRecord = fetchOne(
    "
        SELECT *
        FROM otp_verifications
        WHERE user_id = ?
        AND otp = ?
        ORDER BY id DESC
        LIMIT 1 
    ",
    [$userId, $otp]
);

if (!$otpRecord) {

    response_send([
        'success' => false,
        'status' => 401,
        'message' => 'Invalid OTP',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ]);
}

if (
    strtotime($otpRecord['expired_at']) < time()
) {

    response_send([
        'success' => false,
        'status' => 401,
        'message' => 'OTP expired',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ]);
}

$user = fetchOne(
    "
        SELECT id, name, email, role, avatar
        FROM users
        WHERE id = ?
        LIMIT 1
    ",
    [$userId]
);

if (!$user) {

    response_send([
        'success' => false,
        'status' => 404,
        'message' => 'User not found',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ]);
}

unset($_SESSION['pending_admin_id']);

$_SESSION['user'] = [
    'id' => $user['id'],
    'name' => $user['name'],
    'email' => $user['email'],
    'role' => $user['role'],
    'avatar' => $user['avatar'] ?? null
];

response_send([
    'success' => true,
    'status' => 200,
    'message' => 'OTP verified successfully',
    'errors' => [],
    'data' => [],
    'redirect' => null
]);