<?php

require_once __DIR__ . '/Helpers/DatabaseHelper.php';
require_once __DIR__ . '/Helpers/PermissionHelper.php';
require_once __DIR__ . '/Helpers/ResponseHelper.php';
require_once __DIR__ . '/Helpers/ValidationHelper.php';
require_once __DIR__ . '/Helpers/UploadHelper.php';
require_once __DIR__ . '/Helpers/CloudinaryHelper.php';

if (session_status() === PHP_SESSION_NONE) {

    session_start();

}

if (empty($_SESSION['user'])) {

    response_error(
        'Unauthorized access',
        401
    );

}

require_permission('BLOG_CREATE');

$file =
    $_FILES['image']
    ?? $_FILES['video']
    ?? null;

$blogId = isset($_POST['blog_id'])
    ? (int) $_POST['blog_id']
    : 0;

if ($blogId <= 0) {

    response_error(
        'Invalid blog',
        400
    );

}

$userId = (int) $_SESSION['user']['id'];

$blog = fetchOne(
    "
        SELECT id
        FROM blogs
        WHERE id = ?
        AND user_id = ?
        LIMIT 1
    ",
    [$blogId, $userId]
);

if (!$blog) {

    response_error(
        'Invalid blog',
        400
    );

}

if (!$file) {

    response_error(
        'File is required',
        400
    );

}

if (
    ($file['error'] ?? UPLOAD_ERR_OK)
    !== UPLOAD_ERR_OK
) {

    response_error(
        'Upload failed',
        400
    );

}

$mimeType = mime_content_type(
    $file['tmp_name']
);

$uploadType = '';

if (
    is_string($mimeType)
    && strpos($mimeType, 'image/') === 0
) {

    $uploadType = 'IMAGE';

    if (
        !validation_image($file)
    ) {

        response_error(
            'Invalid image',
            400
        );

    }

    if (
        !validation_max_file_size(
            $file,
            20 * 1024 * 1024
        )
    ) {

        response_error(
            'Image exceeds 20MB limit',
            400
        );

    }

    if (
        !validation_allowed_extensions(
            $file,
            [
                'jpg',
                'jpeg',
                'png',
                'webp',
                'gif'
            ]
        )
    ) {

        response_error(
            'Invalid image extension',
            400
        );

    }

} elseif (
    is_string($mimeType)
    && strpos($mimeType, 'video/') === 0
) {

    $uploadType = 'VIDEO';

    if (
        !validation_video($file)
    ) {

        response_error(
            'Invalid video',
            400
        );

    }

    if (
        !validation_max_file_size(
            $file,
            50 * 1024 * 1024
        )
    ) {

        response_error(
            'Video exceeds 50MB limit',
            400
        );

    }

    if (
        !validation_allowed_video_extensions(
            $file,
            [
                'mp4',
                'mov',
                'webm'
            ]
        )
    ) {

        response_error(
            'Invalid video extension',
            400
        );

    }

} else {

    response_error(
        'Unsupported file type',
        400
    );

}

$result = cloudinary_upload(
    $file['tmp_name'],
    'blog-management-system',
    [
        'resource_type' => 'auto'
    ]
);

$created = upload_create([

    'blog_id' => $blogId,

    'type' => $uploadType,

    'url' => $result['secure_url'],

    'public_id' => $result['public_id'],

    'folder' =>
        'blog-management-system',

    'filename' =>
        $result['original_filename']

]);

if (!$created) {

    response_error(
        'Failed to save upload',
        400
    );

}

response_success(
    'Upload successful',
    [
        'url' =>
            $result['secure_url'],

        'public_id' =>
            $result['public_id']
    ]
);
