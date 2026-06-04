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

    response_error(
        'Unauthorized',
        401
    );
}

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

$userId =
    $input['userId'] ?? null;

$permissionId =
    $input['permissionId'] ?? null;

$enabled =
    $input['enabled'] ?? false;

if (
    empty($userId) ||
    empty($permissionId)
) {

    response_error(
        'Invalid request data',
        422
    );
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

    response_error(
        'User not found',
        404
    );

} else if (($user['role'] ?? '') === 'ADMIN') {

    response_error(
        'Forbidden',
        403
    );
}

$permission = fetchOne(
    "
        SELECT id
        FROM permissions
        WHERE id = ?
        LIMIT 1
    ",
    [$permissionId]
);

if (!$permission) {

    response_error(
        'Permission not found',
        404
    );
}

$existingPermission = fetchOne(
    "
        SELECT id
        FROM user_permissions
        WHERE user_id = ?
        AND permission_id = ?
        LIMIT 1
    ",
    [$userId, $permissionId]
);

if ($enabled) {

    // Enable permission: delete the restriction row if it exists
    if ($existingPermission) {

        execute(
            "
                DELETE FROM user_permissions
                WHERE user_id = ?
                AND permission_id = ?
            ",
            [$userId, $permissionId]
        );
    }

} else {

    // Disable permission: insert a restriction row if it doesn't exist
    if (!$existingPermission) {

        execute(
            "
                INSERT INTO user_permissions
                (
                    user_id,
                    permission_id
                )
                VALUES (?, ?)
            ",
            [$userId, $permissionId]
        );
    }
}

response_success(
    'Permission updated successfully'
);