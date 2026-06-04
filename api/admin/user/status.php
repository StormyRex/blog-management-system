<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../../Helpers/ResponseHelper.php';

if (
    empty($_SESSION['user']) ||
    ($_SESSION['user']['role'] ?? '') !== 'ADMIN'
) {

    response_send([
        'success' => false,
        'status' => 401,
        'message' => 'Unauthorized',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {

    response_send([
        'success' => false,
        'status' => 405,
        'message' => 'Method not allowed',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ]);
}

$input = json_decode(file_get_contents('php://input'), true);

$userId = $input['userId'] ?? null;

$status = $input['status'] ?? '';

$allowedStatuses = [
    'ACTIVE',
    'BLOCKED',
    'DEACTIVATED'
];

if (
    empty($userId) ||
    !in_array($status, $allowedStatuses)
) {

    response_send([
        'success' => false,
        'status' => 422,
        'message' => 'Invalid request data',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ]);
}

$user = fetchOne(
    "
        SELECT id, role
        FROM users
        WHERE id = ?
        LIMIT 1
    ",
    [$userId]
);

if (!$user) {

    response_send([
        'success' => false,
        'status' => 404,
        'message' => 'User not found',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ]);
}

if ($user['role'] === 'ADMIN') {

    response_send([
        'success' => false,
        'status' => 403,
        'message' => 'Admin users cannot be modified',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ]);
}

execute(
    "
        UPDATE users
        SET status = ?
        WHERE id = ?
    ",
    [$status, $userId]
);

response_send([
    'success' => true,
    'status' => 200,
    'message' => 'User status updated successfully',
    'errors' => [],
    'data' => [],
    'redirect' => null
]);