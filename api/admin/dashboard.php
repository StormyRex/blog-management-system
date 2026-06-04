<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';

if (
    empty($_SESSION['user']) ||
    ($_SESSION['user']['role'] ?? '') !== 'ADMIN'
) {

    response_send([
        'success' => false,
        'status' => 401,
        'message' => 'Unauthorized',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ]);
}

$totalUsers = fetchOne(
    "
        SELECT COUNT(*) AS total
        FROM users
    "
);

$totalBlogs = fetchOne(
    "
        SELECT COUNT(*) AS total
        FROM blogs
    "
);

$totalActiveUsers = fetchOne(
    "
        SELECT COUNT(*) AS total
        FROM users
        WHERE status = 'ACTIVE'
    "
);

$totalRestrictedBlogs = fetchOne(
    "
        SELECT COUNT(*) AS total
        FROM blogs
        WHERE status = 'RESTRICTED'
    "
);

$recentBlogs = fetchAll(
    "
        SELECT
            blogs.id,
            blogs.title,
            users.name AS creator_name,
            blogs.created_at
        FROM blogs
        LEFT JOIN users
            ON users.id = blogs.user_id
        ORDER BY blogs.id DESC
        LIMIT 10
    "
);

response_send([
    'success' => true,
    'status' => 200,
    'message' => 'Dashboard statistics fetched successfully',
    'errors' => [],
    'data' => [

        'totalUsers' => (int) ($totalUsers['total'] ?? 0),

        'totalBlogs' => (int) ($totalBlogs['total'] ?? 0),

        'totalActiveUsers' => (int) ($totalActiveUsers['total'] ?? 0),

        'totalRestrictedBlogs' => (int) ($totalRestrictedBlogs['total'] ?? 0),

        'recentBlogs' => $recentBlogs
    ],
    'redirect' => null
]);