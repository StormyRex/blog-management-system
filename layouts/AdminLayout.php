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

$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$isDashboard = str_contains($currentPath, '/admin/dashboard') || (str_ends_with(rtrim($currentPath, '/'), '/admin'));
$isUsers = str_contains($currentPath, '/admin/users');
$isBlogs = str_contains($currentPath, '/admin/blogs');
$isPermissions = str_contains($currentPath, '/admin/permissions');
$isContacts = str_contains($currentPath, '/admin/contacts');

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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fcfcfc;
            color: #111111;
        }

        .navbar-dark.bg-dark {
            background-color: #111111 !important;
            border-bottom: 1px solid #222222;
            padding: 14px 20px;
        }

        .sidebar {
            background-color: #ffffff;
            border-right: 1px solid #e8e8e8;
            padding: 24px 16px;
        }

        .sidebar .list-group-item {
            font-size: 0.875rem;
            font-weight: 500;
            color: #555555 !important;
            padding: 11px 16px;
            border-radius: 10px !important;
            border: none !important;
            margin-bottom: 4px;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            background: transparent;
        }

        .sidebar .list-group-item:hover {
            background-color: #f5f5f5 !important;
            color: #111111 !important;
        }

        .sidebar .list-group-item.active {
            background-color: #111111 !important;
            color: #ffffff !important;
            font-weight: 600;
        }

        .sidebar .list-group-item.active:hover {
            background-color: #111111 !important;
            color: #ffffff !important;
        }

        /* ── Typography & Cards ────────────────────────────── */
        h1, h2, h3, h4, h5, h6 {
            letter-spacing: -0.02em;
            font-weight: 700;
        }

        .form-card,
        .admin-card {
            background: #ffffff;
            border: 1px solid #e8e8e8;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.015);
            padding: 32px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e8e8e8;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.015);
            transition: transform 0.15s ease, border-color 0.15s ease;
        }

        .stat-card:hover {
            transform: translateY(-1px);
            border-color: #ccc;
        }

        .hero-pill {
            display: inline-block;
            background: #f4f4f4;
            border: 1px solid #e0e0e0;
            border-radius: 100px;
            padding: 4px 16px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #555;
            margin-bottom: 12px;
        }
        
        .btn-solid,
        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            font-family: inherit;
            text-decoration: none;
            cursor: pointer;
            border: 1.5px solid transparent;
            transition: background 0.15s, border-color 0.15s, transform 0.15s;
            white-space: nowrap;
        }

        .btn-solid {
            background: #111;
            color: #fff;
            border-color: #111;
        }

        .btn-solid:hover {
            background: #333;
            color: #fff;
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            color: #111;
            border-color: #e0e0e0;
        }

        .btn-outline:hover {
            border-color: #111;
            background: #f5f5f5;
            color: #111;
            transform: translateY(-1px);
        }

        .admin-card table td a {
            text-decoration: none;
            color: #111;
        }
        .admin-card table td a:hover {
            text-decoration: underline !important;
        }

        /* ── Standardized Modern Modals ─────────────────────── */
        .modal-content {
            border: none !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08) !important;
            padding: 8px !important;
        }

        .modal-header {
            border-bottom: none !important;
            padding: 24px 24px 8px !important;
        }

        .modal-header .modal-title {
            font-weight: 700 !important;
            font-size: 1.25rem !important;
            letter-spacing: -0.02em !important;
        }

        .modal-body {
            padding: 8px 24px 20px !important;
            color: #555 !important;
        }

        .modal-footer {
            border-top: none !important;
            padding: 12px 24px 24px !important;
            gap: 8px !important;
        }

        .modal-footer .btn-primary,
        .modal-footer .btn-secondary,
        .modal-footer .btn-dark,
        .modal-footer .btn-danger,
        .modal-footer .btn-outline-dark,
        .modal-footer .btn-outline {
            padding: 9px 20px !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
            margin: 0 !important;
        }

        .modal-footer .btn-secondary {
            background-color: #6c757d !important;
            border: none !important;
            color: #ffffff !important;
        }

        .modal-footer .btn-secondary:hover {
            background-color: #5a6268 !important;
        }

        .modal-footer .btn-dark,
        .modal-footer .btn-primary {
            background-color: #111111 !important;
            border: none !important;
            color: #ffffff !important;
        }

        .modal-footer .btn-dark:hover,
        .modal-footer .btn-primary:hover {
            background-color: #333333 !important;
        }

        .modal-footer .btn-danger {
            background-color: #dc3545 !important;
            border: none !important;
            color: #ffffff !important;
        }

        .modal-footer .btn-danger:hover {
            background-color: #bb2d3b !important;
        }

        .modal-footer .btn-outline-dark,
        .modal-footer .btn-outline {
            background-color: transparent !important;
            border: 1.5px solid #e0e0e0 !important;
            color: #111111 !important;
        }

        .modal-footer .btn-outline-dark:hover,
        .modal-footer .btn-outline:hover {
            background-color: #f5f5f5 !important;
            border-color: #111111 !important;
            color: #111111 !important;
        }
    </style>

</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">

        <div class="container-fluid">

            <a
                class="navbar-brand fw-bold"
                href="<?php echo $baseUrl; ?>/admin/dashboard"
                style="letter-spacing: -0.03em;"
            >
                BlogSphere Admin
            </a>

            <div class="d-flex align-items-center gap-3">

                <span class="text-white small fw-medium">
                    <?php
                    echo htmlspecialchars(
                        $_SESSION['user']['name']
                    );
                    ?>
                </span>

                <a
                    href="<?php echo $baseUrl; ?>/logout"
                    class="btn btn-outline-light btn-sm"
                    style="border-radius: 8px; font-weight: 600;"
                >
                    Logout
                </a>

            </div>

        </div>

    </nav>

    <div class="container-fluid">

        <div class="row">

            <div class="col-md-2 sidebar min-vh-100">

                <div class="list-group list-group-flush">

                    <a
                        href="<?php echo $baseUrl; ?>/admin/dashboard"
                        class="list-group-item list-group-item-action <?php echo $isDashboard ? 'active' : ''; ?>"
                    >
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>

                    <a
                        href="<?php echo $baseUrl; ?>/admin/users"
                        class="list-group-item list-group-item-action <?php echo $isUsers ? 'active' : ''; ?>"
                    >
                        <i class="bi bi-people"></i> Users
                    </a>

                    <a
                        href="<?php echo $baseUrl; ?>/admin/blogs"
                        class="list-group-item list-group-item-action <?php echo $isBlogs ? 'active' : ''; ?>"
                    >
                        <i class="bi bi-journal-text"></i> Blogs
                    </a>

                    <a
                        href="<?php echo $baseUrl; ?>/admin/permissions"
                        class="list-group-item list-group-item-action <?php echo $isPermissions ? 'active' : ''; ?>"
                    >
                        <i class="bi bi-shield-lock"></i> Permissions
                    </a>

                    <a
                        href="<?php echo $baseUrl; ?>/admin/contacts"
                        class="list-group-item list-group-item-action <?php echo $isContacts ? 'active' : ''; ?>"
                    >
                        <i class="bi bi-envelope"></i> Contact Messages
                    </a>

                </div>

            </div>

            <div class="col-md-10 p-4 p-lg-5">

                <?php echo $content ?? ''; ?>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php echo $scripts ?? ''; ?>

</body>

</html>