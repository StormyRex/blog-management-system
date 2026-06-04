<?php

require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';
require_once __DIR__ . '/../Helpers/ValidationHelper.php';
require_once __DIR__ . '/../Helpers/CloudinaryHelper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user']['id'])) {
    response_error('Unauthorized access', 401);
}

$requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($requestMethod === 'POST' && strtoupper($_POST['_method'] ?? '') === 'PUT') {
    $requestMethod = 'PUT';
}

if (!in_array($requestMethod, ['PUT', 'POST'], true)) {
    response_error('Method not allowed', 405);
}

$payload = [];
$contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '');

if ($requestMethod === 'PUT' && str_contains($contentType, 'application/json')) {
    $rawBody = file_get_contents('php://input');
    $decoded = json_decode($rawBody, true);

    if (is_array($decoded)) {
        $payload = $decoded;
    }
} else {
    $payload = $_POST;
}

$userId = (int) $_SESSION['user']['id'];

$name = trim($payload['name'] ?? '');
$username = trim($payload['username'] ?? '');
$email = trim($payload['email'] ?? '');
$phone = trim($payload['phone'] ?? '');
$gender = trim($payload['gender'] ?? '');
$birthDate = trim($payload['birthDate'] ?? $payload['birth_date'] ?? '');
$city = trim($payload['city'] ?? '');
$bio = trim($payload['bio'] ?? '');
$avatarFile = $_FILES['avatar'] ?? null;

$errors = [];

if ($name === '') {
    $errors['name'] = 'Name is required';
}

if ($username === '') {
    $errors['username'] = 'Username is required';
} elseif (strlen($username) < 3) {
    $errors['username'] = 'Username must be at least 3 characters';
} else {
    $usernameExists = fetchOne(
        "
            SELECT id
            FROM users
            WHERE username = ?
            AND id != ?
            LIMIT 1
        ",
        [$username, $userId]
    );

    if ($usernameExists) {
        $errors['username'] = 'Username already exists';
    }
}

if ($email === '') {
    $errors['email'] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email address';
} else {
    $emailExists = fetchOne(
        "
            SELECT id
            FROM users
            WHERE email = ?
            AND id != ?
            LIMIT 1
        ",
        [$email, $userId]
    );

    if ($emailExists) {
        $errors['email'] = 'Email already exists';
    }
}

if ($gender !== '' && !in_array($gender, ['Male', 'Female', 'Other'], true)) {
    $errors['gender'] = 'Invalid gender';
}

if ($birthDate !== '') {
    $birthDateTime = DateTime::createFromFormat('Y-m-d', $birthDate);

    if (!$birthDateTime || $birthDateTime->format('Y-m-d') !== $birthDate) {
        $errors['birthDate'] = 'Invalid birth date';
    }
}

if ($avatarFile && ($avatarFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    if (($avatarFile['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $errors['avatar'] = 'Avatar upload failed';
    } elseif (!validation_image($avatarFile)) {
        $errors['avatar'] = 'Avatar must be a valid image';
    } elseif (!validation_allowed_extensions($avatarFile, ['jpg', 'jpeg', 'png', 'webp'])) {
        $errors['avatar'] = 'Allowed formats: jpg, jpeg, png, webp';
    }
}

if (!empty($errors)) {
    response_error('Validation failed', 422, $errors);
}

$avatarUrl = null;

if ($avatarFile && ($avatarFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    $uploadResult = cloudinary_upload(
        $avatarFile['tmp_name'],
        'blog-management-system/user-avatars',
        [
            'resource_type' => 'image'
        ]
    );

    $avatarUrl = $uploadResult['secure_url'] ?? null;

    if (!$avatarUrl) {
        response_error('Failed to upload avatar', 500);
    }
}

$profile = fetchOne(
    "
        SELECT id
        FROM users
        WHERE id = ?
        LIMIT 1
    ",
    [$userId]
);

if (!$profile) {
    response_error('User not found', 404);
}

$updateData = [
    'name' => $name,
    'username' => $username,
    'email' => $email,
    'phone' => $phone !== '' ? $phone : null,
    'gender' => $gender !== '' ? $gender : null,
    'birth_date' => $birthDate !== '' ? $birthDate : null,
    'bio' => $bio !== '' ? $bio : null,
    'city' => $city !== '' ? $city : null
];

if ($avatarUrl !== null) {
    $updateData['avatar'] = $avatarUrl;
}

$updated = update('users', $updateData, $userId);

if (!$updated) {
    response_error('Update failed', 500);
}

$updatedProfile = fetchOne(
    "
        SELECT
            id,
            avatar,
            name,
            username,
            email,
            phone,
            gender,
            birth_date,
            bio,
            city
        FROM users
        WHERE id = ?
        LIMIT 1
    ",
    [$userId]
);

$_SESSION['user'] = array_merge(
    $_SESSION['user'],
    [
        'name' => $updatedProfile['name'] ?? $name,
        'email' => $updatedProfile['email'] ?? $email,
        'username' => $updatedProfile['username'] ?? $username,
        'avatar' => $updatedProfile['avatar'] ?? $avatarUrl
    ]
);

response_success(
    'Profile updated successfully',
    [
        'profile' => [
            'id' => (int) ($updatedProfile['id'] ?? $userId),
            'avatar' => $updatedProfile['avatar'] ?? $avatarUrl,
            'name' => $updatedProfile['name'] ?? $name,
            'username' => $updatedProfile['username'] ?? $username,
            'email' => $updatedProfile['email'] ?? $email,
            'phone' => $updatedProfile['phone'] ?? $phone,
            'gender' => $updatedProfile['gender'] ?? $gender,
            'birthDate' => $updatedProfile['birth_date'] ?? $birthDate,
            'bio' => $updatedProfile['bio'] ?? $bio,
            'city' => $updatedProfile['city'] ?? $city
        ]
    ]
);
