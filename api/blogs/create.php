<?php

require_once __DIR__ .
    '/../Helpers/DatabaseHelper.php';
require_once __DIR__ .
    '/../Helpers/PermissionHelper.php';
require_once __DIR__ .
    '/../Helpers/ResponseHelper.php';

if (
    session_status()
    === PHP_SESSION_NONE
) {

    session_start();
}

if (empty($_SESSION['user'])) {

    response_send([
        'success' => false,
        'message' => 'Unauthorized',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ], 401);
}

require_permission('BLOG_CREATE');

$title =
    trim($_POST['title'] ?? '');

$description =
    trim($_POST['description'] ?? '');

$category =
    trim($_POST['category'] ?? '');

$hashtags =
    trim($_POST['hashtags'] ?? '');

$visibility =
    trim($_POST['visibility'] ?? 'PUBLIC');

$errors = [];

if ($title === '') {

    $errors['title'] =
        'Title is required';

} elseif (
    strlen($title) < 5
) {

    $errors['title'] =
        'Title must be at least 5 characters';

}

if ($description === '') {

    $errors['description'] =
        'Description is required';

}

if ($category === '') {

    $errors['category'] =
        'Category is required';

}

if (
    !in_array(
        $visibility,
        ['PUBLIC', 'PRIVATE'],
        true
    )
) {

    $errors['visibility'] =
        'Invalid visibility';

}

if (!empty($errors)) {

    response_send([

        'success' => false,

        'message' => 'Validation failed',

        'errors' => $errors,

        'data' => [],

        'redirect' => null

    ], 422);
}

$slug = strtolower(
    trim(
        preg_replace(
            '/[^A-Za-z0-9-]+/',
            '-',
            $title
        )
    )
);

$slug = trim(
    $slug,
    '-'
);

$userId =
    $_SESSION['user']['id'];

$created = create(

    'blogs',

    [

        'user_id' => $userId,

        'title' => $title,

        'slug' => $slug,

        'description' => $description,

        'category' => $category,

        'hashtags' => $hashtags,

        'visibility' => $visibility

    ]

);

if (!$created) {

    response_send([

        'success' => false,

        'message' => 'Failed to create blog',

        'errors' => [],

        'data' => [],

        'redirect' => null

    ], 500);
}

$inserted = fetchOne(
    'SELECT LAST_INSERT_ID() AS id'
);

$blogId =
    $inserted['id'] ?? null;

response_send([

    'success' => true,

    'message' =>
        'Blog created successfully',

    'data' => [

        'id' => $blogId

    ],

    'errors' => [],

    'redirect' =>
        BASE_URL .
        '/blogs/my-blogs'

    ]);