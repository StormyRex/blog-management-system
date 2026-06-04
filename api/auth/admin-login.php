<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../Helpers/MailHelper.php';
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

$identity = trim($_POST['identity'] ?? '');
$password = trim($_POST['password'] ?? '');

$errors = [];

if ($identity === '') {
    $errors['identity'] = 'Email or username is required';
}

if ($password === '') {
    $errors['password'] = 'Password is required';
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

$user = fetchOne(
    "
        SELECT *
        FROM users
        WHERE (
            email = ?
            OR username = ?
        )
        AND role = 'ADMIN'
        LIMIT 1
    ",
    [$identity, $identity]
);

if (!$user) {

    response_send([
        'success' => false,
        'status' => 401,
        'message' => 'Invalid credentials',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ]);
}

if (!password_verify($password, $user['password'])) {

    response_send([
        'success' => false,
        'status' => 401,
        'message' => 'Invalid credentials',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ]);
}

$otp = (string) random_int(100000, 999999);

$expiredAt = date(
    'Y-m-d H:i:s',
    strtotime('+10 minutes')
);

$existingOtp = fetchOne(
    "
        SELECT id
        FROM otp_verifications
        WHERE user_id = ?
        LIMIT 1
    ",
    [$user['id']]
);

if ($existingOtp) {

    execute(
        "
            UPDATE otp_verifications
            SET
                otp = ?,
                expired_at = ?,
                created_at = NOW()
            WHERE user_id = ?
        ",
        [
            $otp,
            $expiredAt,
            $user['id']
        ]
    );

} else {

    execute(
        "
            INSERT INTO otp_verifications (
                user_id,
                otp,
                expired_at
            )
            VALUES (?, ?, ?)
        ",
        [
            $user['id'],
            $otp,
            $expiredAt
        ]
    );
}


$emailSent = sendMail(
    $user['email'],
    'Your Admin Login OTP',
    "
        <h2>Admin Login OTP</h2>

        <p>
            Your OTP code is:
        </p>

        <h3>{$otp}</h3>

        <p>
            This OTP will expire in 10 minutes.
        </p>
    "
);

if (!$emailSent) {

    response_send([
        'success' => false,
        'status' => 500,
        'message' => 'Failed to send OTP email',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ]);
}

$_SESSION['pending_admin_id'] = $user['id'];

response_send([
    'success' => true,
    'status' => 200,
    'message' => 'OTP sent successfully',
    'errors' => [],
    'data' => [],
    'redirect' => null
]);