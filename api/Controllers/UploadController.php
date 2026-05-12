<?php

namespace Shive\BlogManagementSystem\Controllers;

use Shive\BlogManagementSystem\Helpers\ResponseHelper;
use Shive\BlogManagementSystem\Helpers\ValidationHelper;
use Shive\BlogManagementSystem\Providers\CloudinaryProvider;
use Shive\BlogManagementSystem\Models\UploadModel;

class UploadController
{
    public function upload()
    {
        try {

            $file = $_FILES['image'] ?? null;

            if (!ValidationHelper::required($file)) {
                ResponseHelper::error(
                    'Image is required',
                    200
                );
            }

            if (!ValidationHelper::image($file)) {
                ResponseHelper::error(
                    'Invalid image type',
                    200
                );
            }

            if (!ValidationHelper::maxFileSize(
                $file,
                5 * 1024 * 1024
            )) {
                ResponseHelper::error(
                    'Image exceeds 5MB size limit',
                    200
                );
            }

            if (!ValidationHelper::allowedExtensions(
                $file,
                ['jpg', 'jpeg', 'png', 'webp']
            )) {
                ResponseHelper::error(
                    'Invalid file extension',
                    200
                );
            }

            $provider = new CloudinaryProvider();

            $result = $provider->upload(
                $file['tmp_name']
            );

            $model = new UploadModel();
            $dbError = null;

            try {
                $model->create([

                    'blog_id' => 1,

                    'type' => 'IMAGE',

                    'url' => $result['secure_url'],

                    'public_id' => $result['public_id'],

                    'folder' =>
                        'blog-management-system',

                    'filename' =>
                        $result['original_filename']
                ]);
            } catch (\Throwable $e) {
                $dbError = $e->getMessage();
            }

            $data = [
                'public_id' =>
                    $result['public_id'],

                'secure_url' =>
                    $result['secure_url'],

                'original_filename' =>
                    $result['original_filename']
            ];

            if ($dbError !== null) {
                $data['db_error'] = $dbError;

                ResponseHelper::error(
                    'Upload succeeded but database insert failed',
                    200,
                    $data
                );
            }

            ResponseHelper::success(
                'Upload successful',
                $data
            );

        } catch (\Throwable $e) {
            ResponseHelper::error(
                $e->getMessage(),
                200
            );
        }
    }

    public function fetch(): void
    {
        $model = new UploadModel();

        $uploads =
            $model->getAll();

        ResponseHelper::success(
            'Uploads fetched',
            ['uploads' => $uploads]
        );
    }

    public function delete(): void
    {
        try {
            $id = isset($_POST['id'])
                ? (int) $_POST['id']
                : 0;

            if ($id <= 0) {
                ResponseHelper::error(
                    'Something went wrong',
                    200
                );
            }

            $model = new UploadModel();

            $upload = $model->findById($id);

            if (!$upload || empty($upload['public_id'])) {
                ResponseHelper::error(
                    'Something went wrong',
                    200
                );
            }

            $provider = new CloudinaryProvider();

            $deleteResult = $provider->delete(
                $upload['public_id']
            );

            if ($deleteResult === false) {
                ResponseHelper::error(
                    'Something went wrong',
                    200
                );
            }

            if (
                is_array($deleteResult)
                && isset($deleteResult['result'])
                && $deleteResult['result'] !== 'ok'
                && $deleteResult['result'] !== 'not found'
            ) {
                ResponseHelper::error(
                    'Something went wrong',
                    200
                );
            }

            if (!$model->delete($id)) {
                ResponseHelper::error(
                    'Something went wrong',
                    200
                );
            }

            ResponseHelper::success(
                'Upload deleted successfully'
            );
        } catch (\Throwable $exception) {
            ResponseHelper::error(
                $exception->getMessage(),
                200
            );
        }
    }
}