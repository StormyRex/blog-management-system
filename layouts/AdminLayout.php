<?php

$baseUrl = defined('BASE_URL') ? BASE_URL : '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    empty($_SESSION['user']) ||
    $_SESSION['user']['role'] !== 'ADMIN'
) {

    header(
        'Location: ' . $baseUrl . '/auth/admin-login'
    );

    exit;
}

$pageTitle = $pageTitle ?? 'Admin Panel | BlogSphere';

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo htmlspecialchars($pageTitle); ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">

        <div class="container-fluid">

            <a
                class="navbar-brand"
                href="<?php echo $baseUrl; ?>/admin/dashboard"
            >
                BlogSphere Admin
            </a>

            <div class="d-flex align-items-center gap-2">

                <span class="text-white small">

                    <?php
                    echo htmlspecialchars(
                        $_SESSION['user']['name']
                    );
                    ?>

                </span>

                <a
                    href="<?php echo $baseUrl; ?>/logout"
                    class="btn btn-outline-light btn-sm"
                >
                    Logout
                </a>

            </div>

        </div>

    </nav>

    <div class="container-fluid">

        <div class="row">

            <div class="col-md-2 bg-white border-end min-vh-100 p-3">

                <div class="list-group list-group-flush">

                    <a
                        href="<?php echo $baseUrl; ?>/admin/dashboard"
                        class="list-group-item list-group-item-action"
                    >
                        Dashboard
                    </a>

                    <a
                        href="<?php echo $baseUrl; ?>/admin/users"
                        class="list-group-item list-group-item-action"
                    >
                        Users
                    </a>

                    <a
                        href="<?php echo $baseUrl; ?>/admin/blogs"
                        class="list-group-item list-group-item-action"
                    >
                        Blogs
                    </a>

                    <a
                        href="<?php echo $baseUrl; ?>/admin/permissions"
                        class="list-group-item list-group-item-action"
                    >
                        Permissions
                    </a>

                    <a
                        href="<?php echo $baseUrl; ?>/admin/contacts"
                        class="list-group-item list-group-item-action"
                    >
                        Contact Messages
                    </a>

                </div>

            </div>

            <div class="col-md-10 p-4">

                <?php echo $content ?? ''; ?>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php echo $scripts ?? ''; ?>

</body>

</html>