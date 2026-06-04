<?php

require_once __DIR__ . '/Helpers/DatabaseHelper.php';
require_once __DIR__ . '/Helpers/ResponseHelper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUser = $_SESSION['user'] ?? [];
$currentUserId = (int) ($currentUser['id'] ?? 0);

$query = trim($_GET['q'] ?? '');

if ($query === '') {

    response_success(
        'Search results',
        [
            'blogs' => [],
            'users' => []
        ]
    );

}

if (str_starts_with($query, '@')) {

    $username = substr($query, 1);

    $blogs = [];

    $users = fetchAll(
        "
            SELECT
                id,
                name,
                username,
                avatar,
                bio
            FROM users
            WHERE
                status = 'ACTIVE'
                AND username LIKE ?
            LIMIT 10
        ",
        [
            '%' . $username . '%'
        ]
    );

} else if (str_starts_with($query, '#')) {

    $tag = substr($query, 1);

    $blogs = fetchAll(
        "
            SELECT
                blogs.id,
                blogs.title,
                blogs.description,
                blogs.category,
                blogs.hashtags,
                users.name,
                users.username,

                (
                    SELECT COUNT(*)
                    FROM likes
                    WHERE likes.blog_id = blogs.id
                ) AS like_count,

                (
                    SELECT COUNT(*)
                    FROM likes
                    WHERE likes.blog_id = blogs.id
                    AND likes.user_id = ?
                ) AS is_liked,

                (
                    SELECT COUNT(*)
                    FROM shares
                    WHERE shares.blog_id = blogs.id
                ) AS share_count,

                (
                    SELECT COUNT(*)
                    FROM comments
                    WHERE comments.blog_id = blogs.id
                ) AS comment_count

            FROM blogs
            INNER JOIN users
            ON users.id = blogs.user_id
            WHERE
                blogs.visibility = 'PUBLIC'
                AND blogs.status = 'ACTIVE'
                AND blogs.hashtags LIKE ?
            LIMIT 10
        ",
        [
            $currentUserId,
            '%' . $tag . '%'
        ]
    );

    $users = [];

} else {

    $searchTerm = '%' . $query . '%';

    $blogs = fetchAll(
        "
            SELECT
                blogs.id,
                blogs.title,
                blogs.description,
                blogs.category,
                blogs.hashtags,
                users.name,
                users.username,

                (
                    SELECT COUNT(*)
                    FROM likes
                    WHERE likes.blog_id = blogs.id
                ) AS like_count,

                (
                    SELECT COUNT(*)
                    FROM likes
                    WHERE likes.blog_id = blogs.id
                    AND likes.user_id = ?
                ) AS is_liked,

                (
                    SELECT COUNT(*)
                    FROM shares
                    WHERE shares.blog_id = blogs.id
                ) AS share_count,

                (
                    SELECT COUNT(*)
                    FROM comments
                    WHERE comments.blog_id = blogs.id
                ) AS comment_count

            FROM blogs
            INNER JOIN users
            ON users.id = blogs.user_id
            WHERE
                blogs.visibility = 'PUBLIC'
                AND blogs.status = 'ACTIVE'
                AND (
                    blogs.title LIKE ?
                    OR blogs.description LIKE ?
                )
            LIMIT 10
        ",
        [
            $currentUserId,
            $searchTerm,
            $searchTerm
        ]
    );

    $users = fetchAll(
        "
            SELECT
                id,
                name,
                username,
                avatar,
                bio
            FROM users
            WHERE
                status = 'ACTIVE'
                AND (
                    username LIKE ?
                    OR name LIKE ?
                )
            LIMIT 10
        ",
        [
            $searchTerm,
            $searchTerm
        ]
    );

}

$blogIds = array_column(
    $blogs,
    'id'
);

$thumbnailMap = getBlogThumbnailMap(
    $blogIds
);

foreach ($blogs as &$blog) {

    $blog['thumbnail'] =
        $thumbnailMap[$blog['id']] ?? null;
}

unset($blog);

response_success(
    'Search results',
    [
        'blogs' => $blogs,
        'users' => $users
    ]
);