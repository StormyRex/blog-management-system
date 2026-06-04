<?php

$baseEndpoint = '/api';

$authBaseEndpoint = $baseEndpoint . '/auth';

return [

    /*
    |--------------------------------------------------------------------------
    | Upload
    |--------------------------------------------------------------------------
    */

    $baseEndpoint . '/upload' => 'api/upload.php',


    /*
    |--------------------------------------------------------------------------
    | Auth
    |--------------------------------------------------------------------------
    */

    $authBaseEndpoint . '/login' => 'api/auth/login.php',

    $authBaseEndpoint . '/login.php' => 'api/auth/login.php',

    $authBaseEndpoint . '/register' => 'api/auth/register.php',

    $authBaseEndpoint . '/register.php' => 'api/auth/register.php',

    $authBaseEndpoint . '/admin-login' => 'api/auth/admin-login.php',

    $authBaseEndpoint . '/verify-otp' => 'api/auth/verify-otp.php',

    $authBaseEndpoint . '/forgot-password' => 'api/auth/forgot-password.php',

    $authBaseEndpoint . '/verify-reset-token' => 'api/auth/verify-reset-token.php',

    $authBaseEndpoint . '/reset-password' => 'api/auth/reset-password.php',

    
    /*
    |--------------------------------------------------------------------------
    | Blogs
    |--------------------------------------------------------------------------
    */
    $baseEndpoint . '/blogs/create'=> 'api/blogs/create.php',
    $baseEndpoint . '/blogs/like' => 'api/blogs/like.php',
    $baseEndpoint . '/blogs/share' => 'api/blogs/share.php',
    $baseEndpoint . '/search' => 'api/search.php',

    $baseEndpoint . '/user/profile' => 'api/user/profile.php',

    $baseEndpoint . '/user/profile.php' => 'api/user/profile.php',

    $baseEndpoint . '/user/blogs' => 'api/user/blogs.php',

    $baseEndpoint . '/user/blogs.php' => 'api/user/blogs.php',

    $baseEndpoint . '/user/update-profile' => 'api/user/update-profile.php',

    $baseEndpoint . '/user/update-profile.php' => 'api/user/update-profile.php',
    
    $baseEndpoint . '/blogs/delete' => 'api/blogs/delete.php',

    $baseEndpoint . '/blogs/update' => 'api/blogs/update.php',


    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    */
    $baseEndpoint .'/admin/dashboard' => 'api/admin/dashboard.php',

    $baseEndpoint . '/admin/users' => 'api/admin/users.php',

    $baseEndpoint . '/admin/user/status' => 'api/admin/user/status.php',

    $baseEndpoint . '/admin/blogs' => 'api/admin/blogs.php',

    $baseEndpoint . '/admin/blog/status' => 'api/admin/blog/status.php',

    $baseEndpoint . '/admin/blog/delete' => 'api/admin/blog/delete.php',

        /*
    |--------------------------------------------------------------------------
    | Admin Permissions
    |--------------------------------------------------------------------------
    */

    $baseEndpoint . '/admin/permissions' => 'api/admin/permissions.php',

    $baseEndpoint . '/admin/permissions-update' => 'api/admin/permission-update.php',

    /*
    |--------------------------------------------------------------------------
    | Contact Messages
    |--------------------------------------------------------------------------
    */
    
    $baseEndpoint . '/contact' => 'api/contact.php',
    
    $baseEndpoint . '/admin/contacts' => 'api/admin/contacts.php',

];


