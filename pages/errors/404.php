<?php
http_response_code(404);
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found | BlogSphere</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fff; color: #000; }
        .error-section { min-height: 80vh; display: flex; align-items: center; justify-content: center; }
        .error-code { font-size: 7rem; font-weight: 800; letter-spacing: -0.04em; line-height: 1; }
        .navbar { background: #fff !important; border-bottom: 1px solid #dee2e6; }
        .navbar-brand { font-weight: 700; color: #000 !important; }
        footer { background: #000; color: #fff; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $baseUrl; ?>/">BlogSphere</a>
        </div>
    </nav>

    <div class="container error-section flex-grow-1">
        <div class="text-center">
            <div class="error-code mb-3">404</div>
            <h1 class="h3 fw-bold mb-2">Page Not Found</h1>
            <p class="text-muted mb-4">The page you requested could not be found.<br>It may have been moved or deleted.</p>
            <a href="<?php echo $baseUrl; ?>/" class="btn btn-dark me-2">Go Home</a>
            <a href="<?php echo $baseUrl; ?>/explore" class="btn btn-outline-dark">Explore Blogs</a>
        </div>
    </div>

    <footer class="py-3">
        <div class="container text-center">
            <span class="text-white small">BlogSphere — Write. Share. Inspire.</span>
        </div>
    </footer>
</body>
</html>
