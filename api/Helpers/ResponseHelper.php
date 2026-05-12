<?php

namespace Shive\BlogManagementSystem\Helpers;

class ResponseHelper
{
    public static function json(
        bool $success,
        string $message,
        array $data = [],
        int $statusCode = 200
    ): void {

        http_response_code($statusCode);

        header(
            'Content-Type: application/json'
        );

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);

        exit;
    }

    public static function success(
        string $message,
        array $data = [],
        int $statusCode = 200
    ): void {

        self::json(
            true,
            $message,
            $data,
            $statusCode
        );
    }

    public static function error(
        string $message,
        int $statusCode = 400,
        array $data = []
    ): void {

        self::json(
            false,
            $message,
            $data,
            $statusCode
        );
    }
}   