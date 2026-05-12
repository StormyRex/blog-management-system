<?php

use Shive\BlogManagementSystem\Controllers\UploadController;

return [

    '/api/upload' => [
        UploadController::class,
        'upload'
    ]

];  