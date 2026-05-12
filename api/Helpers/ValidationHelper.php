<?php

namespace Shive\BlogManagementSystem\Helpers;

class ValidationHelper
{
    public static function required(mixed $value): bool
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

    public static function image(array $file): bool
    {
        if (!isset($file['tmp_name'])) {
            return false;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return false;
        }

        return getimagesize($file['tmp_name']) !== false;
    }

    public static function maxFileSize(
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

    public static function allowedExtensions(
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

    public static function video(array $file): bool
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

    public static function allowedVideoExtensions(
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
}
