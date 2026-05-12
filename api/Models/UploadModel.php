<?php

namespace Shive\BlogManagementSystem\Models;

use Shive\BlogManagementSystem\Config\DatabaseConfig;

class UploadModel
{
    private $connection;

    public function __construct()
    {
        $database = new DatabaseConfig();

        $this->connection =
            $database->connect();
    }

    public function create(
        array $data
    ): bool {

        $query = "
            INSERT INTO uploads (
                blog_id,
                type,
                url,
                public_id,
                folder,
                filename
            )
            VALUES (
                ?, ?, ?, ?, ?, ?
            )
        ";
        

        $statement =
            $this->connection->prepare($query);

        if (!$statement) {
            throw new \RuntimeException(
                'Upload insert prepare failed: ' .
                $this->connection->error
            );
        }

        $bindOk = $statement->bind_param(
            'isssss',

            $data['blog_id'],

            $data['type'],

            $data['url'],

            $data['public_id'],

            $data['folder'],

            $data['filename']
        );

        if (!$bindOk) {
            throw new \RuntimeException(
                'Upload insert bind failed: ' .
                $statement->error
            );
        }

        if (!$statement->execute()) {
            throw new \RuntimeException(
                'Upload insert execute failed: ' .
                $statement->error
            );
        }

        return true;
    }

    public function getAll(): array
    {
        $query = "
            SELECT *
            FROM uploads
            ORDER BY id DESC
        ";

        $result =
            $this->connection->query($query);

        $uploads = [];

        while (
            $row = $result->fetch_assoc()
        ) {
            $uploads[] = $row;
        }

        return $uploads;
    }

    public function findById(int $id): ?array
    {
        $query = "
            SELECT *
            FROM uploads
            WHERE id = ?
            LIMIT 1
        ";

        $statement =
            $this->connection->prepare($query);

        if (!$statement) {
            throw new \RuntimeException(
                'Upload find prepare failed: ' .
                $this->connection->error
            );
        }

        $bindOk = $statement->bind_param(
            'i',
            $id
        );

        if (!$bindOk) {
            throw new \RuntimeException(
                'Upload find bind failed: ' .
                $statement->error
            );
        }

        if (!$statement->execute()) {
            throw new \RuntimeException(
                'Upload find execute failed: ' .
                $statement->error
            );
        }

        $result = $statement->get_result();

        if ($result === false) {
            throw new \RuntimeException(
                'Upload find result failed: ' .
                $statement->error
            );
        }

        $row = $result->fetch_assoc();

        return $row ?: null;
    }

    public function delete(int $id): bool
    {
        $query = "
            DELETE FROM uploads
            WHERE id = ?
            LIMIT 1
        ";

        $statement =
            $this->connection->prepare($query);

        if (!$statement) {
            throw new \RuntimeException(
                'Upload delete prepare failed: ' .
                $this->connection->error
            );
        }

        $bindOk = $statement->bind_param(
            'i',
            $id
        );

        if (!$bindOk) {
            throw new \RuntimeException(
                'Upload delete bind failed: ' .
                $statement->error
            );
        }

        if (!$statement->execute()) {
            throw new \RuntimeException(
                'Upload delete execute failed: ' .
                $statement->error
            );
        }

        return $statement->affected_rows === 1;
    }
}