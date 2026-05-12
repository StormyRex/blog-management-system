<?php

namespace Shive\BlogManagementSystem\Providers;

use Cloudinary\Cloudinary;

use Shive\BlogManagementSystem\Config\CloudinaryConfig;

class CloudinaryProvider
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        CloudinaryConfig::load();

        $this->cloudinary = new Cloudinary(
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
    }

    public function upload(
        string $filePath,
        string $folder = 'blog-management-system'
    ) {

        $result = $this->cloudinary
            ->uploadApi()
            ->upload(
                $filePath,
                [
                    'folder' => $folder
                ]
            );

        return $result;
    }

    public function delete(
        string $publicId
    ): array|bool {

        $response = $this->cloudinary
            ->uploadApi()
            ->destroy($publicId);

        if (is_object($response) && method_exists($response, 'getArrayCopy')) {
            return $response->getArrayCopy();
        }

        return (array) $response;
    }
}