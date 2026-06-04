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

$search = trim($_GET['search'] ?? '');
$role = trim($_GET['role'] ?? '');
$status = trim($_GET['status'] ?? '');

$query = "
    SELECT
        id,
        name,
        username,
        email,
        role,
        status,
        created_at
    FROM users
";

$where = [];

$params = [];

if ($search !== '') {

    $where[] = "
        (
            name LIKE ?
            OR username LIKE ?
            OR email LIKE ?
        )
    ";

    $searchTerm = '%' . $search . '%';

    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($role !== '') {

    $where[] = "role = ?";

    $params[] = $role;
}

if ($status !== '') {

    $where[] = "status = ?";

    $params[] = $status;
}

if (!empty($where)) {

    $query .= "
        WHERE
        " . implode(' AND ', $where);
}

$query .= "
    ORDER BY created_at DESC
";

$users = fetchAll(
    $query,
    $params
);

response_send([
    'success' => true,
    'status' => 200,
    'message' => 'Users fetched successfully',
    'errors' => [],
    'data' => $users,
    'redirect' => null
]);