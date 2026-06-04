<?php

function db_connection(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $connection = new mysqli(
        "localhost",
        "root",
        "",
        "blog_management_system"
    );

    if ($connection->connect_error) {

        die(
            "Database Connection Failed: " .
            $connection->connect_error
        );
    }

    $connection->set_charset("utf8mb4");

    return $connection;
}