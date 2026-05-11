<?php

namespace Shive\BlogManagementSystem\Config;

use mysqli;

class DatabaseConfig
{
    private string $host = "localhost";

    private string $database = "blog_management_system";

    private string $username = "root";

    private string $password = "";

    public function connect(): mysqli
    {
        $connection = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->database
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
}