<?php

namespace Shive\BlogManagementSystem\Helpers;

use Shive\BlogManagementSystem\Config\DatabaseConfig;

class DatabaseHelper
{
    private $connection;

    public function __construct()
    {
        $database = new DatabaseConfig();
        $this->connection = $database->connect();
    }

    public function fetchOne(
        string $query,
        array $params = []
    ): ?array {

        $statement = $this->prepareStatement($query, $params);

        if (!$statement->execute()) {
            return null;
        }

        $result = $statement->get_result();

        if ($result === false) {
            return null;
        }

        return $result->fetch_assoc() ?: null;
    }

    public function fetchAll(
        string $query,
        array $params = []
    ): array {

        $statement = $this->prepareStatement($query, $params);

        if (!$statement->execute()) {
            return [];
        }

        $result = $statement->get_result();

        if ($result === false) {
            return [];
        }

        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function execute(
        string $query,
        array $params = []
    ): bool {

        $statement = $this->prepareStatement($query, $params);

        return $statement->execute();
    }

    public function lastInsertId(): int
    {
        return (int) $this->connection->insert_id;
    }

    private function prepareStatement(
        string $query,
        array $params
    ) {

        $statement = $this->connection->prepare($query);

        if (!$statement) {
            throw new \RuntimeException(
                'Query prepare failed: ' . $this->connection->error
            );
        }

        if (!empty($params)) {
            $types = $this->detectTypes($params);

            if (!$statement->bind_param($types, ...$params)) {
                throw new \RuntimeException(
                    'Bind param failed: ' . $statement->error
                );
            }
        }

        return $statement;
    }

    private function detectTypes(array $params): string
    {
        $types = '';

        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }

        return $types;
    }
}
