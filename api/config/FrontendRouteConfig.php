<?php

use Shive\BlogManagementSystem\Controllers\AdminController;
use Shive\BlogManagementSystem\Controllers\AuthController;
use Shive\BlogManagementSystem\Controllers\BlogController;
use Shive\BlogManagementSystem\Controllers\HomeController;
use Shive\BlogManagementSystem\Controllers\UploadPageController;
use Shive\BlogManagementSystem\Controllers\UserController;

return [

    '/' => [HomeController::class, 'index'],

    '/login' => [AuthController::class, 'login'],

    '/register' => [AuthController::class, 'register'],

    '/user/dashboard' => [UserController::class, 'dashboard'],

    '/user/profile' => [UserController::class, 'profile'],

    '/blogs/my-blogs' => [BlogController::class, 'myBlogs'],

    '/blogs/create' => [BlogController::class, 'create'],

    '/admin/dashboard' => [AdminController::class, 'dashboard'],

    '/upload' => [UploadPageController::class, 'index'],

];