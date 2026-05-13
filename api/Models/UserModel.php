<?php

namespace Shive\BlogManagementSystem\Models;

use Shive\BlogManagementSystem\Config\DatabaseConfig;

class UserModel
{
    private $connection;

    public function __construct()
    {
        $database = new DatabaseConfig();
        $this->connection = $database->connect();
    }

    public function createUser(array $data): bool
    {
        $query = "
            INSERT INTO users (
                name,
                username,
                email,
                password
            ) VALUES (
                ?, ?, ?, ?
            )
        ";

        $statement = $this->connection->prepare($query);

        if (!$statement) {
            throw new \RuntimeException(
                'User insert prepare failed: ' . $this->connection->error
            );
        }

        $bindOk = $statement->bind_param(
            'ssss',
            $data['name'],
            $data['username'],
            $data['email'],
            $data['password']
        );

        if (!$bindOk) {
            throw new \RuntimeException(
                'User insert bind failed: ' . $statement->error
            );
        }

        if (!$statement->execute()) {
            throw new \RuntimeException(
                'User insert execute failed: ' . $statement->error
            );
        }

        return true;
    }

    public function findByEmail(string $email): ?array
    {
        $query = "
            SELECT *
            FROM users
            WHERE email = ?
            LIMIT 1
        ";

        $statement = $this->connection->prepare($query);

        if (!$statement) {
            throw new \RuntimeException(
                'User email lookup prepare failed: ' . $this->connection->error
            );
        }

        $bindOk = $statement->bind_param('s', $email);

        if (!$bindOk) {
            throw new \RuntimeException(
                'User email lookup bind failed: ' . $statement->error
            );
        }

        if (!$statement->execute()) {
            throw new \RuntimeException(
                'User email lookup execute failed: ' . $statement->error
            );
        }

        $result = $statement->get_result();

        if ($result === false) {
            return null;
        }

        $row = $result->fetch_assoc();

        return $row ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $query = "
            SELECT *
            FROM users
            WHERE username = ?
            LIMIT 1
        ";

        $statement = $this->connection->prepare($query);

        if (!$statement) {
            throw new \RuntimeException(
                'User username lookup prepare failed: ' . $this->connection->error
            );
        }

        $bindOk = $statement->bind_param('s', $username);

        if (!$bindOk) {
            throw new \RuntimeException(
                'User username lookup bind failed: ' . $statement->error
            );
        }

        if (!$statement->execute()) {
            throw new \RuntimeException(
                'User username lookup execute failed: ' . $statement->error
            );
        }

        $result = $statement->get_result();

        if ($result === false) {
            return null;
        }

        $row = $result->fetch_assoc();

        return $row ?: null;
    }
}
