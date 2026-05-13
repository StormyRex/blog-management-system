<?php

use Shive\BlogManagementSystem\Controllers\UploadController;

$baseEndpoint = '/api';
$authBaseEndpoint = $baseEndpoint . '/auth';
return [

    $baseEndpoint.'/upload' => [
        UploadController::class,
        'upload'
    ],
    $authBaseEndpoint.'/login' => "../controllers/AuthController.php",
    $authBaseEndpoint.'/register' => "../controllers/AuthController.php",
    $authBaseEndpoint.'/logout' => "../controllers/AuthController.php",

];  