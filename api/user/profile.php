<?php

require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Allow anonymous viewing when an 'id' is provided. If no id and no session, require auth.
$currentUserId = (int) ($_SESSION['user']['id'] ?? 0);
$targetUserId = isset($_GET['id']) ? (int) $_GET['id'] : $currentUserId;

if ($targetUserId <= 0) {
    // No target specified and no logged-in user -> unauthorized
    response_error('Unauthorized access', 401);
}

$isOwnProfile = $targetUserId === $currentUserId;

if ($isOwnProfile) {

    $profile = fetchOne(
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
        [$targetUserId]
    );

} else {

    $profile = fetchOne(
        "
            SELECT
                id,
                avatar,
                name,
                username,
                bio,
                city
            FROM users
            WHERE id = ?
            AND status = 'ACTIVE'
            LIMIT 1
        ",
        [$targetUserId]
    );

}

if (!$profile) {
    response_error('User not found', 404);
}

if ($isOwnProfile) {

    $totalBlogs = fetchOne(
        "
            SELECT COUNT(*) AS total_blogs
            FROM blogs
            WHERE user_id = ?
        ",
        [$targetUserId]
    );

} else {

    $totalBlogs = fetchOne(
        "
            SELECT COUNT(*) AS total_blogs
            FROM blogs
            WHERE user_id = ?
            AND visibility = 'PUBLIC'
            AND status = 'ACTIVE'
        ",
        [$targetUserId]
    );

}

$profileData = [
    'id' => (int) $profile['id'],
    'avatar' => $profile['avatar'] ?? null,
    'name' => $profile['name'] ?? '',
    'username' => $profile['username'] ?? '',
    'bio' => $profile['bio'] ?? null,
    'city' => $profile['city'] ?? null,
    'email' => $isOwnProfile ? ($profile['email'] ?? '') : null,
    'phone' => $isOwnProfile ? ($profile['phone'] ?? null) : null,
    'gender' => $isOwnProfile ? ($profile['gender'] ?? null) : null,
    'birthDate' => $isOwnProfile ? ($profile['birth_date'] ?? null) : null,
    'totalBlogsCount' => (int) ($totalBlogs['total_blogs'] ?? 0),
    'canEdit' => $isOwnProfile
];

response_success(
    'Profile loaded successfully',
    [
        'profile' => $profileData
    ]
);
