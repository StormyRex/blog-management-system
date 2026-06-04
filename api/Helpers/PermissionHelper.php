<?php

require_once __DIR__ . '/DatabaseHelper.php';
require_once __DIR__ . '/ResponseHelper.php';

function current_user_has_permission(
    string $permissionCode,
    ?int $userId = null
): bool {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $resolvedUserId = $userId ?? ($_SESSION['user']['id'] ?? null);

    if (empty($resolvedUserId)) {
        return false;
    }

    $user = fetchOne(
        "
            SELECT role
            FROM users
            WHERE id = ?
            LIMIT 1
        ",
        [$resolvedUserId]
    );

    if (!$user) {
        return false;
    }

    if (($user['role'] ?? '') === 'ADMIN') {
        return true;
    }

    $permission = fetchOne(
        "
            SELECT id
            FROM permissions
            WHERE code = ?
            LIMIT 1
        ",
        [$permissionCode]
    );

    if (!$permission) {
        return false;
    }

    $existingPermission = fetchOne(
        "
            SELECT id
            FROM user_permissions
            WHERE user_id = ?
            AND permission_id = ?
            LIMIT 1
        ",
        [
            $resolvedUserId,
            $permission['id']
        ]
    );

    // Default-allow model: If a restriction row does not exist, the user is allowed the permission.
    return empty($existingPermission);
}

function require_permission(
    string $permissionCode,
    ?int $userId = null
): void {

    if (current_user_has_permission($permissionCode, $userId)) {
        return;
    }

    response_send([
        'success' => false,
        'status' => 403,
        'message' => 'Forbidden',
        'errors' => [],
        'data' => [],
        'redirect' => null
    ], 403);
}