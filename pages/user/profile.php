<?php
$pageTitle = 'Profile | BlogSphere';
$activePage = 'profile';
$user = $_SESSION['user'] ?? null;
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
ob_start();
?>
<section class="hero-card p-4 p-lg-5 mb-4">
    <div class="section-title mb-2">Profile</div>
    <h1 class="fw-bold mb-2">Welcome to BlogSphere</h1>
    <p class="text-muted mb-0">
        Manage your profile details and review your activity inside the BlogSphere demo project.
    </p>
</section>

<section class="mb-5">
    <div class="card">
        <div class="card-body">
            <?php if ($user) { ?>
                <h2 class="h5">Hello, <?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>!</h2>
                <p class="text-muted mb-1">Email: <?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="text-muted mb-0">Account ID: <?php echo htmlspecialchars((string) $user['id'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php } else { ?>
                <h2 class="h5">You are not logged in.</h2>
                <p class="text-muted">Please login to view your profile information.</p>
                <a class="btn btn-outline-primary" href="<?php echo $baseUrl; ?>/login">Go to Login</a>
            <?php } ?>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/MainLayout.php';
?>
