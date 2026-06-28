<?php

function db_load_env(): void
{
    static $loaded = false;

    if ($loaded) {
        return;
    }

    if (class_exists('Dotenv\Dotenv')) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable(
                __DIR__ . '/../../'
            );
            $dotenv->safeLoad();
        } catch (Throwable $e) {
            // Safe load will ignore errors if file does not exist
        }
    }

    $loaded = true;
}

function db_connection(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    db_load_env();

    $host   = $_ENV['DB_HOST'] ?? 'localhost';
    $user   = $_ENV['DB_USER'] ?? 'root';
    $pass   = $_ENV['DB_PASS'] ?? '';
    $dbname = $_ENV['DB_NAME'] ?? 'blog_management_system';

    $connection = new mysqli($host, $user, $pass, $dbname);

    if ($connection->connect_error) {
        die("Database Connection Failed: " . $connection->connect_error);
    }

    $connection->set_charset("utf8mb4");

    return $connection;
}