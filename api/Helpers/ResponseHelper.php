<?php

function response_send(
    array $payload,
    int $statusCode = 200
): void {

    http_response_code($statusCode);

    header(
        'Content-Type: application/json'
    );

    echo json_encode($payload);

    exit;
}

function response_json(
    bool $success,
    string $message,
    array $data = [],
    int $statusCode = 200
): void {

    $payload = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'redirect' => null
    ];

    if ($success) {
        $payload['errors'] = [];
    } else {
        $payload['errors'] = $data ?: [];
    }

    response_send(
        $payload,
        $statusCode
    );
}

function response_success(
    string $message,
    array $data = [],
    int $statusCode = 200
): void {

    response_json(
        true,
        $message,
        $data,
        $statusCode
    );
}

function response_error(
    string $message,
    int $statusCode = 400,
    array $data = []
): void {

    response_json(
        false,
        $message,
        $data,
        $statusCode
    );
}