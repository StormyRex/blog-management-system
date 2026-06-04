<?php

require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../Helpers/PermissionHelper.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';
$baseUrl = defined('BASE_URL') ? BASE_URL : ''; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user'])) {

    response_send([
        'success' => false,
        'status' => 401,
        'message' => 'Unauthorized access',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ], 401);
}

require_permission('BLOG_UPDATE');

$userId = $_SESSION['user']['id'];

$id = $_POST['id'] ?? '';

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$category = trim($_POST['category'] ?? '');
$hashtags = trim($_POST['hashtags'] ?? '');
$visibility = trim($_POST['visibility'] ?? '');

$errors = [];

if ($title === '') {
    $errors['title'] = 'Title is required';
}

if ($description === '') {
    $errors['description'] = 'Description is required';
}

if ($category === '') {
    $errors['category'] = 'Category is required';
}

if ($visibility === '') {
    $errors['visibility'] = 'Visibility is required';
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

$blog = fetchOne(
    "
        SELECT id
        FROM blogs
        WHERE id = ?
        AND user_id = ?
    ",
    [$id, $userId]
);

if (!$blog) {

    response_send([
        'success' => false,
        'message' => 'Blog not found',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ], 404);
}

$updated = update(
    'blogs',
    [
        'title' => $title,
        'description' => $description,
        'category' => $category,
        'hashtags' => $hashtags,
        'visibility' => $visibility
    ],
    $id
);

if (!$updated) {

    response_send([
        'success' => false,
        'message' => 'Update failed',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ], 500);
}

response_send([
    'success' => true,
    'status' => 200,
    'message' => 'Blog updated successfully',
    'data' => [],
    'errors' => [],
    'redirect' => $baseUrl . '/blogs/my-blogs'
], 200);