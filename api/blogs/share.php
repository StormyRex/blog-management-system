<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';

execute(
    "
        CREATE TABLE IF NOT EXISTS shares (
            id BIGINT PRIMARY KEY AUTO_INCREMENT,
            blog_id BIGINT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_share_blog
            FOREIGN KEY (blog_id)
            REFERENCES blogs(id)
            ON DELETE CASCADE
        )
    "
);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    response_error(
        'Method not allowed',
        405
    );
}

$blogId = (int) ($_POST['blogId'] ?? 0);

if ($blogId <= 0) {

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

$saved = execute(
    "
        INSERT INTO shares (
            blog_id
        ) VALUES (
            ?
        )
    ",
    [$blogId]
);

if (!$saved) {

    response_error(
        'Unable to record share',
        500
    );
}

$shareCount = fetchOne(
    "
        SELECT COUNT(*) AS share_count
        FROM shares
        WHERE blog_id = ?
    ",
    [$blogId]
);

response_success(
    'Blog shared successfully',
    [
        'sharesCount' => (int) ($shareCount['share_count'] ?? 0)
    ]
);