<?php

use Cloudinary\Cloudinary;
use Dotenv\Dotenv;

function cloudinary_load_env(): void
{
    static $loaded = false;

    if ($loaded) {
        return;
    }

    $dotenv = Dotenv::createImmutable(
        __DIR__ . '/../../'
    );

    $dotenv->load();

    $loaded = true;
}

function cloudinary_client(): Cloudinary
{
    static $client = null;

    if ($client instanceof Cloudinary) {
        return $client;
    }

    cloudinary_load_env();

    $client = new Cloudinary(
        [
            'cloud' => [
                'cloud_name' =>
                    $_ENV['CLOUDINARY_CLOUD_NAME'],

                'api_key' =>
                    $_ENV['CLOUDINARY_API_KEY'],

                'api_secret' =>
                    $_ENV['CLOUDINARY_API_SECRET']
            ],

            'url' => [
                'secure' => true
            ]
        ]
    );

    return $client;
}

function cloudinary_upload(
    string $filePath,
    string $folder = 'blog-management-system',
    array $options = []
): array {

    $uploadOptions = array_merge(
        ['folder' => $folder],
        $options
    );

    $result = cloudinary_client()
        ->uploadApi()
        ->upload(
            $filePath,
            $uploadOptions
        );

    if (is_object($result) && method_exists($result, 'getArrayCopy')) {
        return $result->getArrayCopy();
    }

    return (array) $result;
}

function cloudinary_delete(
    string $publicId
): array|bool {

    $response = cloudinary_client()
        ->uploadApi()
        ->destroy($publicId);

    if (is_object($response) && method_exists($response, 'getArrayCopy')) {
        return $response->getArrayCopy();
    }

    return (array) $response;
}
