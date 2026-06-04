<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';

if (empty($_SESSION['user'])) {

    response_error(
        'Unauthorized',
        401
    );
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    response_error(
        'Method not allowed',
        405
    );
}

$blogId = (int) ($_POST['blogId'] ?? 0);
$userId = (int) ($_SESSION['user']['id'] ?? 0);

if ($blogId <= 0 || $userId <= 0) {

    response_error(
        'Invalid request data',
        422
    );
}

$blog = fetchOne(
    "
        SELECT id
        FROM blogs
        WHERE id = ?
        LIMIT 1
    ",
    [$blogId]
);

if (!$blog) {

    response_error(
        'Blog not found',
        404
    );
}

$existingLike = fetchOne(
    "
        SELECT id
        FROM likes
        WHERE blog_id = ?
        AND user_id = ?
        LIMIT 1
    ",
    [
        $blogId,
        $userId
    ]
);

if (!$existingLike) {

    $saved = execute(
        "
            INSERT INTO likes (
                blog_id,
                user_id
            ) VALUES (
                ?,
                ?
            )
        ",
        [
            $blogId,
            $userId
        ]
    );

    if (!$saved) {

        response_error(
            'Unable to like blog',
            500
        );
    }

    $liked = true;

} else {

    $removed = execute(
        "
            DELETE FROM likes
            WHERE id = ?
        ",
        [
            (int) $existingLike['id']
        ]
    );

    if (!$removed) {

        response_error(
            'Unable to unlike blog',
            500
        );
    }

    $liked = false;
}

$likesCount = fetchOne(
    "
        SELECT COUNT(*) AS like_count
        FROM likes
        WHERE blog_id = ?
    ",
    [$blogId]
);

response_success(
    $liked ? 'Blog liked successfully' : 'Blog unliked successfully',
    [
        'liked' => $liked,
        'likesCount' => (int) ($likesCount['like_count'] ?? 0)
    ]
);