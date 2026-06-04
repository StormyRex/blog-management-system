<?php

require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../Helpers/PermissionHelper.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';
require_once __DIR__ . '/../Helpers/CloudinaryHelper.php';

$baseUrl = defined('BASE_URL') ? BASE_URL : '';

function is_cloudinary_delete_success($response): bool
{
    if ($response === true) {
        return true;
    }

    if (!is_array($response)) {
        return false;
    }

    if (isset($response['error'])) {
        return false;
    }

    if (isset($response['result'])) {
        $result = strtolower((string) $response['result']);
        return in_array(
            $result,
            ['ok', 'not found', 'not_found'],
            true
        );
    }

    return false;
}

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

require_permission('BLOG_DELETE');

$userId = $_SESSION['user']['id'];

$id = intval($_POST['id'] ?? null);

if (empty($id)) {

    response_send([
        'success' => false,
        'status' => 422,
        'message' => 'Blog ID is required',
        'errors' => [],
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
        'status' => 404,
        'message' => 'Blog not found',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ], 404);
}

$uploads = fetchAll(
    "
        SELECT id, public_id
        FROM uploads
        WHERE blog_id = ?
    ",
    [$id]
);

if (!empty($uploads)) {

    foreach ($uploads as $upload) {

        $publicId = trim($upload['public_id'] ?? '');

        if ($publicId === '') {

            response_send([
                'success' => false,
                'status' => 500,
                'message' => 'Upload record missing public id',
                'errors' => [],
                'data' => [],
                'redirect' => null
            ], 500);
        }

        $deleteResponse = cloudinary_delete($publicId);

        if (!is_cloudinary_delete_success($deleteResponse)) {

            response_send([
                'success' => false,
                'status' => 500,
                'message' => 'Failed to delete blog media',
                'errors' => [],
                'data' => [],
                'redirect' => null
            ], 500);
        }
    }
}

$connection = db();

if (!$connection->begin_transaction()) {

    response_send([
        'success' => false,
        'status' => 500,
        'message' => 'Failed to start cleanup transaction',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ], 500);
}

$uploadsDeleted = execute(
    "
        DELETE FROM uploads
        WHERE blog_id = ?
    ",
    [$id]
);

if (!$uploadsDeleted) {

    $connection->rollback();

    response_send([
        'success' => false,
        'status' => 500,
        'message' => 'Failed to delete blog uploads',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ], 500);
}

$deleted = deleteRecord(
    'blogs',
    $id
);

if (!$deleted) {

    $connection->rollback();

    response_send([
        'success' => false,
        'status' => 500,
        'message' => 'Failed to delete blog',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ], 500);
}

$connection->commit();

response_send([
    'success' => true,
    'status' => 200,
    'message' => 'Blog deleted successfully',
    'data' => [],
    'errors' => [],
    'redirect' => null
], 200);