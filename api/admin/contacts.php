<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';

if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'ADMIN') {
    response_send([
        'success' => false,
        'message' => 'Unauthorized access.'
    ], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    response_send([
        'success' => false,
        'message' => 'Method not allowed.'
    ], 405);
}

$search = trim($_GET['search'] ?? '');

$query = "
    SELECT 
        id, 
        name, 
        email, 
        subject,
        message,
        created_at
    FROM contact_messages
";

$params = [];
$conditions = [];

if (!empty($search)) {
    $conditions[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ?)";
    $likeSearch = '%' . $search . '%';
    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $params[] = $likeSearch;
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

$query .= " ORDER BY id DESC";

$messages = fetchAll($query, $params);

response_send([
    'success' => true,
    'data' => $messages
]);
