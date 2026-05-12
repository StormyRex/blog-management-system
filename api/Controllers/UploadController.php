<?php

namespace Shive\BlogManagementSystem\Controllers;

use Shive\BlogManagementSystem\Providers\CloudinaryProvider;
use Shive\BlogManagementSystem\Models\UploadModel;

class UploadController
{
    public function upload()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {

            if (!isset($_FILES['image'])) {

                echo json_encode([
                    'success' => false,
                    'message' => 'No file uploaded'
                ]);

                return;
            }

            $provider = new CloudinaryProvider();

            $result = $provider->upload(
                $_FILES['image']['tmp_name']
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

            $response = [
                'success' => $dbError === null,
                'message' => $dbError === null
                    ? 'Upload successful'
                    : 'Upload succeeded but database insert failed',

                'data' => [
                    'public_id' =>
                        $result['public_id'],

                    'secure_url' =>
                        $result['secure_url'],

                    'original_filename' =>
                        $result['original_filename']
                ]
            ];

            if ($dbError !== null) {
                // Temporary debugging output for database failures.
                $response['db_error'] = $dbError;
            }

            echo json_encode($response);

        } catch (\Throwable $e) {

            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function fetch(): void
    {
        header(
            'Content-Type: application/json'
        );

        $model = new UploadModel();

        $uploads =
            $model->getAll();

        echo json_encode([
            'success' => true,
            'uploads' => $uploads
        ]);
    }

    public function delete(): void
    {
        header('Content-Type: application/json');

        try {
            $id = isset($_POST['id'])
                ? (int) $_POST['id']
                : 0;

            if ($id <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Something went wrong'
                ]);

                return;
            }

            $model = new UploadModel();

            $upload = $model->findById($id);

            if (!$upload || empty($upload['public_id'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Something went wrong'
                ]);

                return;
            }

            $provider = new CloudinaryProvider();

            $deleteResult = $provider->delete(
                $upload['public_id']
            );

            if ($deleteResult === false) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Something went wrong'
                ]);

                return;
            }

            if (
                is_array($deleteResult)
                && isset($deleteResult['result'])
                && $deleteResult['result'] !== 'ok'
                && $deleteResult['result'] !== 'not found'
            ) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Something went wrong'
                ]);

                return;
            }

            if (!$model->delete($id)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Something went wrong'
                ]);

                return;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Upload deleted successfully'
            ]);
        } catch (\Throwable $exception) {
            echo json_encode([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }
}