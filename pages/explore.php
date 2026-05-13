<?php
$pageTitle = 'Explore | BlogSphere';
$activePage = 'explore';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
ob_start();
?>
<section class="hero-card p-4 p-lg-5 mb-4 text-center">
    <div class="section-title mb-2">BlogSphere</div>
    <h1 class="fw-bold mb-2">Explore Blogs and Creators</h1>
    <p class="text-muted mb-0">
        Discover creators, writing communities, and fresh ideas shared across BlogSphere.
    </p>
</section>

<section class="mb-5">
    <h2 class="h5 mb-3">Featured Creators</h2>
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-image-placeholder mb-3"></div>
                    <h5 class="card-title">Amina Noor</h5>
                    <p class="card-text text-muted">
                        Shares practical productivity habits for writers building a consistent schedule.
                    </p>
                    <a class="btn btn-sm btn-outline-primary" href="<?php echo $baseUrl; ?>/user/profile">View Profile</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-image-placeholder mb-3"></div>
                    <h5 class="card-title">Shive Patel</h5>
                    <p class="card-text text-muted">
                        Focuses on building writing routines and sharing knowledge with clarity.
                    </p>
                    <a class="btn btn-sm btn-outline-primary" href="<?php echo $baseUrl; ?>/user/profile">View Profile</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-image-placeholder mb-3"></div>
                    <h5 class="card-title">Rohan Mehta</h5>
                    <p class="card-text text-muted">
                        Writes about making blogs easier to read with clean layout and structure.
                    </p>
                    <a class="btn btn-sm btn-outline-primary" href="<?php echo $baseUrl; ?>/user/profile">View Profile</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/MainLayout.php';
?>