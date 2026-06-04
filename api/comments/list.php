<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

    response_error(
        'Method not allowed',
        405
    );
}

$blogId = (int) ($_GET['blogId'] ?? 0);
$errors = [];

if ($blogId <= 0) {

    $errors['blogId'] = 'Blog is required';

} else {

    $blog = findById('blogs', $blogId);

    if (!$blog) {

        $errors['blogId'] = 'Blog not found';
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

$rows = fetchAll(
    "
        SELECT
            comments.id,
            comments.blog_id,
            comments.user_id,
            comments.comment,
            comments.parent_id,
            comments.created_at,
            users.id AS author_id,
            users.name,
            users.username,
            users.avatar
        FROM comments
        INNER JOIN users
        ON users.id = comments.user_id
        WHERE comments.blog_id = ?
        ORDER BY comments.created_at ASC, comments.id ASC
    ",
    [$blogId]
);

$currentUserId = (int) ($_SESSION['user']['id'] ?? 0);
$currentUserRole = $_SESSION['user']['role'] ?? '';
$commentsById = [];
$topLevelIds = [];
$repliesByParent = [];

foreach ($rows as $row) {

    $commentId = (int) ($row['id'] ?? 0);
    $parentId = $row['parent_id'] !== null ? (int) $row['parent_id'] : null;

    $commentsById[$commentId] = [
        'id' => $commentId,
        'comment' => $row['comment'] ?? '',
        'parent_id' => $parentId,
        'created_at' => $row['created_at'] ?? null,
        'canDelete' => (
            $currentUserId > 0 && (
                $currentUserId === (int) ($row['author_id'] ?? 0) ||
                $currentUserRole === 'ADMIN'
            )
        ),
        'user' => [
            'id' => (int) ($row['author_id'] ?? 0),
            'name' => $row['name'] ?? '',
            'username' => $row['username'] ?? '',
            'avatar' => $row['avatar'] ?? null
        ],
        'replies' => []
    ];

    if ($parentId === null) {

        $topLevelIds[] = $commentId;

    } else {

        $repliesByParent[$parentId][] = $commentsById[$commentId];
    }
}

function buildCommentTree(
    int $commentId,
    array $commentsById,
    array $repliesByParent
): array {

    $comment = $commentsById[$commentId];

    $comment['replies'] = [];

    foreach ($repliesByParent[$commentId] ?? [] as $reply) {

        $replyId = (int) $reply['id'];

        $comment['replies'][] = buildCommentTree(
            $replyId,
            $commentsById,
            $repliesByParent
        );

    }

    return $comment;

}

$comments = [];

foreach ($topLevelIds as $commentId) {

    $comments[] = buildCommentTree(
        $commentId,
        $commentsById,
        $repliesByParent
    );

}

response_success(
    'Comments retrieved successfully',
    [
        'count' => count($rows),
        'comments' => $comments
    ]
);