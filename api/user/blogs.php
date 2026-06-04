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

if ($targetUserId <= 0) {
    response_error('Invalid user', 422);
}

$isOwnProfile = $targetUserId === $currentUserId;

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, (int) $_GET['limit']) : 10;
$limit = min($limit, 20);
$offset = ($page - 1) * $limit;

if ($isOwnProfile) {

    $totalRows = fetchOne(
        "
            SELECT COUNT(*) AS total_count
            FROM blogs
            WHERE user_id = ?
        ",
        [$targetUserId]
    );

    $blogs = fetchAll(
        "
            SELECT
                id,
                title,
                description,
                created_at,
                slug
            FROM blogs
            WHERE user_id = ?
            ORDER BY created_at DESC, id DESC
            LIMIT ? OFFSET ?
        ",
        [$targetUserId, $limit, $offset]
    );

} else {

    $totalRows = fetchOne(
        "
            SELECT COUNT(*) AS total_count
            FROM blogs
            WHERE user_id = ?
            AND visibility = 'PUBLIC'
            AND status = 'ACTIVE'
        ",
        [$targetUserId]
    );

    $blogs = fetchAll(
        "
            SELECT
                id,
                title,
                description,
                created_at,
                slug
            FROM blogs
            WHERE user_id = ?
            AND visibility = 'PUBLIC'
            AND status = 'ACTIVE'
            ORDER BY created_at DESC, id DESC
            LIMIT ? OFFSET ?
        ",
        [$targetUserId, $limit, $offset]
    );

}

$blogIds = array_column($blogs, 'id');
$thumbnailMap = getBlogThumbnailMap($blogIds);

$formattedBlogs = [];

foreach ($blogs as $blog) {

    $description = $blog['description'] ?? '';
    $shortDescription = function_exists('mb_substr')
        ? mb_substr($description, 0, 160)
        : substr($description, 0, 160);

    if (strlen($description) > 160) {
        $shortDescription .= '...';
    }

    $formattedBlogs[] = [
        'id' => (int) $blog['id'],
        'title' => $blog['title'] ?? '',
        'description' => $description,
        'shortDescription' => $shortDescription,
        'slug' => $blog['slug'] ?? '',
        'createdAt' => $blog['created_at'] ?? null,
        'thumbnail' => $thumbnailMap[(int) $blog['id']] ?? null
    ];

}

$totalCount = (int) ($totalRows['total_count'] ?? 0);
$totalPages = $limit > 0 ? (int) ceil($totalCount / $limit) : 0;

response_success(
    'Blogs loaded successfully',
    [
        'blogs' => $formattedBlogs,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'totalItems' => $totalCount,
            'totalPages' => $totalPages,
            'hasNext' => $page < $totalPages,
            'hasPrev' => $page > 1
        ]
    ]
);
