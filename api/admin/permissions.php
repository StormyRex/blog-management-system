<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';

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

$users = fetchAll(
    "
        SELECT
            id,
            name,
            username,
            email,
            role,
            status
        FROM users
        WHERE role <> 'ADMIN'
        ORDER BY created_at DESC
    "
);

$permissions = fetchAll(
    "
        SELECT
            id,
            name,
            code
        FROM permissions
        ORDER BY id ASC
    "
);

$userPermissions = fetchAll(
    "
        SELECT
            user_id,
            permission_id
        FROM user_permissions
    "
);

response_send([
    'success' => true,
    'status' => 200,
    'message' => 'Permissions fetched successfully',
    'errors' => [],
    'data' => [
        'users' => $users,
        'permissions' => $permissions,
        'userPermissions' => $userPermissions
    ],
    'redirect' => null
]);