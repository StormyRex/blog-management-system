<?php

require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../Helpers/MailHelper.php';
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

$email = trim($_POST['email'] ?? '');

$errors = [];

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if (empty($email)) {

    $errors['email'] = 'Email is required';

} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $errors['email'] = 'Invalid email';

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
| Lookup User
|--------------------------------------------------------------------------
*/

$user = fetchOne(
    "SELECT id, email, status FROM users WHERE email = ? LIMIT 1",
    [$email]
);

/*
|--------------------------------------------------------------------------
| Create Reset Token
|--------------------------------------------------------------------------
*/

if ($user && ($user['status'] ?? 'ACTIVE') === 'ACTIVE') {

    execute(
        "
            DELETE FROM password_resets
            WHERE user_id = ?
        ",
        [$user['id']]
    );

    $token = bin2hex(random_bytes(32));

    $expiredAt = date(
        'Y-m-d H:i:s',
        strtotime('+1 hour')
    );

    $created = execute(
        "
            INSERT INTO password_resets (
                user_id,
                token,
                expired_at
            )
            VALUES (?, ?, ?)
        ",
        [
            $user['id'],
            $token,
            $expiredAt
        ]
    );

    if ($created) {

        $resetLink =
            BASE_URL .
            '/auth/reset-password?token=' .
            $token;

        $emailBody = "
            <h2>Reset Your Password</h2>

            <p>
                Click the link below to reset your password:
            </p>

            <p>
                <a href=\"{$resetLink}\">Reset Password</a>
            </p>

            <p>
                This link expires in 1 hour.
            </p>
        ";

        sendMail(
            $user['email'],
            'Reset Your Password',
            $emailBody
        );
    }
}

/*
|--------------------------------------------------------------------------
| Success Response
|--------------------------------------------------------------------------
*/

response_send([
    'success' => true,
    'status' => 200,
    'message' => 'If the email exists, a reset link has been sent',
    'errors' => [],
    'data' => [],
    'redirect' => null
], 200);
