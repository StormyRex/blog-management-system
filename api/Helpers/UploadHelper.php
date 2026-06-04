<?php

require_once __DIR__ . '/DatabaseHelper.php';

function upload_create(array $data): bool
{
    $required = [
        'blog_id',
        'type',
        'url',
        'public_id',
        'folder',
        'filename'
    ];

    foreach ($required as $key) {
        if (!array_key_exists($key, $data)) {
            return false;
        }
    }

    return create(
        'uploads',
        [
            'blog_id' => (int) $data['blog_id'],
            'type' => (string) $data['type'],
            'url' => (string) $data['url'],
            'public_id' => (string) $data['public_id'],
            'folder' => (string) $data['folder'],
            'filename' => (string) $data['filename']
        ]
    );
}

function upload_get_all(): array
{
    return fetchAll(
        "
            SELECT *
            FROM uploads
            ORDER BY id DESC
        "
    );
}

function upload_get_by_blog_id(
    int $blogId
): array {

    return fetchAll(
        "
            SELECT *
            FROM uploads
            WHERE blog_id = ?
            ORDER BY id DESC
        ",
        [$blogId]
    );
}

function upload_find_by_id(
    int $id
): ?array {

    return findById(
        'uploads',
        $id
    );
}

function upload_delete(
    int $id
): bool {

    return deleteRecord(
        'uploads',
        $id
    );
}
