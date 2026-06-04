<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../../Helpers/PermissionHelper.php';
require_once __DIR__ . '/../../Helpers/ResponseHelper.php';

if (
    empty($_SESSION['user']) ||
    ($_SESSION['user']['role'] ?? '') !== 'ADMIN'
) {

    response_error(
        'Unauthorized',
        401
    );
}

require_permission('BLOG_DELETE');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {

    response_error(
        'Method not allowed',
        405
    );
}

$input = json_decode(
    file_get_contents('php://input'),
    true
);

$blogId = $input['blogId'] ?? null;

if (empty($blogId)) {

    response_error(
        'Blog ID is required',
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

execute(
    "
        UPDATE blogs
        SET status = 'DELETED'
        WHERE id = ?
    ",
    [$blogId]
);

response_success(
    'Blog deleted successfully'
);