<?php

require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    response_error(
        'Method not allowed',
        405
    );
}

if (empty($_SESSION['user'])) {

    response_error(
        'Unauthorized',
        401
    );
}

$blogId = (int) ($_POST['blogId'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
$parentIdRaw = trim((string) ($_POST['parentId'] ?? ''));
$parentId = null;

if ($parentIdRaw !== '') {
    $parentId = (int) $parentIdRaw;
}

$errors = [];

if ($blogId <= 0) {

    $errors['blogId'] = 'Blog is required';

} else {

    $blog = findById('blogs', $blogId);

    if (!$blog) {

        $errors['blogId'] = 'Blog not found';

    }
}

if ($comment === '') {

    $errors['comment'] = 'Comment is required';

} elseif (strlen($comment) > 1000) {

    $errors['comment'] = 'Comment must not exceed 1000 characters';

}

if ($parentIdRaw !== '' && $parentId <= 0) {

    $errors['parentId'] = 'Invalid parent comment';

}

if ($parentId !== null && empty($errors['blogId'])) {

    $parentComment = fetchOne(
        "
            SELECT id, blog_id, parent_id
            FROM comments
            WHERE id = ?
            LIMIT 1
        ",
        [$parentId]
    );

    if (!$parentComment) {

        $errors['parentId'] = 'Parent comment not found';

    } elseif ((int) $parentComment['blog_id'] !== $blogId) {

        $errors['parentId'] = 'Parent comment does not belong to this blog';

    }
}

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

$userId = (int) ($_SESSION['user']['id'] ?? 0);

$created = create(
    'comments',
    [
        'blog_id' => $blogId,
        'user_id' => $userId,
        'comment' => $comment,
        'parent_id' => $parentId,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]
);

if (!$created) {

    response_error(
        'Failed to create comment',
        500
    );
}

$inserted = fetchOne(
    'SELECT LAST_INSERT_ID() AS id'
);

$commentId = $inserted['id'] ?? null;

response_success(
    'Comment created successfully',
    [
        'id' => $commentId,
        'blogId' => $blogId,
        'parentId' => $parentId,
        'comment' => $comment
    ]
);