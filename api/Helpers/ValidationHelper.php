<?php

function validation_required(mixed $value): bool
{
    if (is_array($value)) {
        if (!isset($value['name'])) {
            return false;
        }

        $error = $value['error'] ?? UPLOAD_ERR_NO_FILE;

        return $value['name'] !== ''
            && $error !== UPLOAD_ERR_NO_FILE;
    }

    return isset($value) && $value !== '';
}

function validation_image(array $file): bool
{
    if (!isset($file['tmp_name'])) {
        return false;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return false;
    }

    return getimagesize($file['tmp_name']) !== false;
}

function validation_max_file_size(
    array $file,
    int $sizeInBytes
): bool {

    if (!isset($file['size'])) {
        return false;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return false;
    }

    return $file['size'] <= $sizeInBytes;
}

function validation_allowed_extensions(
    array $file,
    array $allowed
): bool {

    if (!isset($file['name'])) {
        return false;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return false;
    }

    $extension = strtolower(
        pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
        )
    );

    return in_array(
        $extension,
        $allowed,
        true
    );
}

function validation_video(array $file): bool
{
    if (!isset($file['tmp_name'])) {
        return false;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return false;
    }

    $mimeType = mime_content_type(
        $file['tmp_name']
    );

    $allowedMimes = [
        'video/mp4',
        'video/quicktime',
        'video/webm'
    ];

    return in_array(
        $mimeType,
        $allowedMimes,
        true
    );
}

function validation_allowed_video_extensions(
    array $file,
    array $allowed
): bool {

    if (!isset($file['name'])) {
        return false;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return false;
    }

    $extension = strtolower(
        pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
        )
    );

    return in_array(
        $extension,
        $allowed,
        true
    );
}
