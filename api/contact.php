<?php

require_once __DIR__ . '/Helpers/DatabaseHelper.php';
require_once __DIR__ . '/Helpers/ResponseHelper.php';
require_once __DIR__ . '/Helpers/ValidationHelper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response_send([
        'success' => false,
        'message' => 'Method not allowed.'
    ], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$name    = trim($input['name'] ?? '');
$email   = trim($input['email'] ?? '');
$subject = trim($input['subject'] ?? '');
$message = trim($input['message'] ?? '');

if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    response_send([
        'success' => false,
        'message' => 'Please fill in all fields.'
    ], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    response_send([
        'success' => false,
        'message' => 'Please provide a valid email address.'
    ], 400);
}

$inserted = create('contact_messages', [
    'name'    => $name,
    'email'   => $email,
    'subject' => $subject,
    'message' => $message
]);

if (!$inserted) {
    response_send([
        'success' => false,
        'message' => 'Something went wrong. Please try again later.'
    ], 500);
}

response_send([
    'success' => true,
    'message' => 'Your message has been sent successfully!'
]);
