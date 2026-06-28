<?php

require_once __DIR__ . '/../Config/DatabaseConfig.php';

/*
|--------------------------------------------------------------------------
| Database Connection
|--------------------------------------------------------------------------
*/

function db()
{
    return db_connection();
}

/*
|--------------------------------------------------------------------------
| Fetch Single Row
|--------------------------------------------------------------------------
*/

function fetchOne(
    string $query,
    array $params = []
): ?array {

    $connection = db();

    $statement = mysqli_prepare($connection, $query);

    if (!$statement) {
        return null;
    }

    if (!empty($params)) {

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

        mysqli_stmt_bind_param(
            $statement,
            $types,
            ...$params
        );
    }

    mysqli_stmt_execute($statement);

    $result = mysqli_stmt_get_result($statement);

    if (!$result) {
        return null;
    }

    $row = mysqli_fetch_assoc($result) ?: null;
    if ($row) {
        $row = cleanRowUrls($row);
    }
    return $row;
}

/*
|--------------------------------------------------------------------------
| Fetch Multiple Rows
|--------------------------------------------------------------------------
*/

function fetchAll(
    string $query,
    array $params = []
): array {

    $connection = db();

    $statement = mysqli_prepare($connection, $query);

    if (!$statement) {
        return [];
    }

    if (!empty($params)) {

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

        mysqli_stmt_bind_param(
            $statement,
            $types,
            ...$params
        );
    }

    mysqli_stmt_execute($statement);

    $result = mysqli_stmt_get_result($statement);

    if (!$result) {
        return [];
    }

    $rows = mysqli_fetch_all(
        $result,
        MYSQLI_ASSOC
    );

    foreach ($rows as &$row) {
        $row = cleanRowUrls($row);
    }
    unset($row);

    return $rows;
}

/*
|--------------------------------------------------------------------------
| Blog Thumbnail Map
|--------------------------------------------------------------------------
*/

function getBlogThumbnailMap(
    array $blogIds
): array {

    if (empty($blogIds)) {
        return [];
    }

    $placeholders = implode(
        ', ',
        array_fill(0, count($blogIds), '?')
    );

    $rows = fetchAll(
        "
            SELECT blog_id, url, type
            FROM uploads
            WHERE blog_id IN ({$placeholders})
            ORDER BY id DESC
        ",
        array_values($blogIds)
    );

    $map = [];

    foreach ($rows as $row) {

        $blogId = (int) ($row['blog_id'] ?? 0);

        if ($blogId <= 0) {
            continue;
        }

        if (!isset($map[$blogId])) {
            $map[$blogId] = [
                'url' => $row['url'] ?? '',
                'type' => $row['type'] ?? ''
            ];
        }
    }

    return $map;
}

/*
|--------------------------------------------------------------------------
| Insert / Update / Delete Query
|--------------------------------------------------------------------------
*/

function execute(
    string $query,
    array $params = []
): bool {

    $connection = db();

    $statement = mysqli_prepare($connection, $query);

    if (!$statement) {
        return false;
    }

    if (!empty($params)) {

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

        mysqli_stmt_bind_param(
            $statement,
            $types,
            ...$params
        );
    }

    return mysqli_stmt_execute($statement);
}

/*
|--------------------------------------------------------------------------
| Find By ID
|--------------------------------------------------------------------------
*/

function findById(
    string $table,
    int $id
): ?array {

    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        return null;
    }

    $query = "
        SELECT *
        FROM {$table}
        WHERE id = ?
        LIMIT 1
    ";

    return fetchOne(
        $query,
        [$id]
    );
}

/*
|--------------------------------------------------------------------------
| Find Unique Record
|--------------------------------------------------------------------------
*/

function findUnique(
    string $table,
    string $column,
    $value
): ?array {

    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        return null;
    }

    if (!preg_match('/^[A-Za-z0-9_]+$/', $column)) {
        return null;
    }

    $query = "
        SELECT *
        FROM {$table}
        WHERE {$column} = ?
        LIMIT 1
    ";

    return fetchOne(
        $query,
        [$value]
    );
}

/*
|--------------------------------------------------------------------------
| Insert Record
|--------------------------------------------------------------------------
*/

function create(
    string $table,
    array $data
): bool {

    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        return false;
    }

    if (empty($data)) {
        return false;
    }

    foreach (array_keys($data) as $column) {

        if (!preg_match('/^[A-Za-z0-9_]+$/', $column)) {
            return false;
        }
    }

    $columns = implode(
        ', ',
        array_keys($data)
    );

    $placeholders = implode(
        ', ',
        array_fill(
            0,
            count($data),
            '?'
        )
    );

    $query = "
        INSERT INTO {$table}
        ({$columns})
        VALUES
        ({$placeholders})
    ";

    return execute(
        $query,
        array_values($data)
    );
}

/*
|--------------------------------------------------------------------------
| Update Record
|--------------------------------------------------------------------------
*/

function update(
    string $table,
    array $data,
    int $id
): bool {

    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        return false;
    }

    if (empty($data)) {
        return false;
    }

    $fields = [];

    foreach ($data as $column => $value) {

        if (!preg_match('/^[A-Za-z0-9_]+$/', $column)) {
            return false;
        }

        $fields[] = "{$column} = ?";
    }

    $setClause = implode(
        ', ',
        $fields
    );

    $query = "
        UPDATE {$table}
        SET {$setClause}
        WHERE id = ?
    ";

    $params = array_values($data);

    $params[] = $id;

    return execute(
        $query,
        $params
    );
}

/*
|--------------------------------------------------------------------------
| Delete Record
|--------------------------------------------------------------------------
*/

function deleteRecord(
    string $table,
    int $id
): bool {

    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        return false;
    }

    $query = "
        DELETE FROM {$table}
        WHERE id = ?
    ";

    return execute(
        $query,
        [$id]
    );
}

/*
|--------------------------------------------------------------------------
| Clean Row URLs (Environment Helper)
|--------------------------------------------------------------------------
*/

function cleanRowUrls(array $row): array {
    $isLocal = !isset($_SERVER['HTTP_HOST']) || (
        in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'], true) ||
        str_starts_with($_SERVER['HTTP_HOST'], '192.168.')
    );

    if (!$isLocal) {
        if (isset($row['url']) && str_starts_with($row['url'], '/blog-management-system/')) {
            $row['url'] = substr($row['url'], strlen('/blog-management-system'));
        }
        if (isset($row['avatar']) && str_starts_with($row['avatar'], '/blog-management-system/')) {
            $row['avatar'] = substr($row['avatar'], strlen('/blog-management-system'));
        }
    }
    return $row;
}