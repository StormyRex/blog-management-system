<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';

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

$currentUserId = (int) ($_SESSION['user']['id'] ?? 0);
$currentUserRole = $_SESSION['user']['role'] ?? '';
$commentId = (int) ($_POST['id'] ?? 0);
$errors = [];

if ($commentId <= 0) {
    $errors['id'] = 'Comment ID is required';
}

if (!empty($errors)) {

    response_error(
        'Validation failed',
        422,
        $errors
    );
}

$comment = fetchOne(
    "
        SELECT id, user_id
        FROM comments
        WHERE id = ?
        LIMIT 1
    ",
    [$commentId]
);

if (!$comment) {

    response_error(
        'Comment not found',
        404
    );
}

$commentOwnerId = (int) ($comment['user_id'] ?? 0);

if (
    $currentUserId !== $commentOwnerId &&
    $currentUserRole !== 'ADMIN'
) {

    response_error(
        'Unauthorized',
        403
    );
}

$deleted = deleteRecord('comments', $commentId);

if (!$deleted) {

    response_error(
        'Failed to delete comment',
        500
    );
}

response_success(
    'Comment deleted successfully',
    [
        'id' => $commentId
    ]
);