<?php

use Shive\BlogManagementSystem\Controllers\UploadController;

return [

    '/api/upload' => [
        UploadController::class,
        'upload'
    ],

    '/api/uploads' => [
        UploadController::class,
        'fetch'
    ],

    '/api/upload/delete' => [
        UploadController::class,
        'delete'
    ]

];
