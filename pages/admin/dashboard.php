<?php

$pageTitle = 'Admin Dashboard | BlogSphere';

$baseUrl = defined('BASE_URL') ? BASE_URL : '';

ob_start();

?>

<div class="mb-5 mt-2">
    <span class="hero-pill mb-2">Overview</span>
    <h1 class="hero-title text-start mb-1" style="font-size: 2.2rem; font-weight: 800; letter-spacing: -0.04em;">Admin Dashboard</h1>
    <p class="text-muted mb-0" style="font-size: 0.95rem;">Welcome to the BlogSphere admin panel.</p>
</div>

<div class="row g-4 mb-5">

    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted mb-2 text-uppercase" style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em;">Total Users</h6>
            <h2 class="mb-0 fw-bold" id="totalUsersCount" style="letter-spacing: -0.03em;">0</h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted mb-2 text-uppercase" style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em;">Total Blogs</h6>
            <h2 class="mb-0 fw-bold" id="totalBlogsCount" style="letter-spacing: -0.03em;">0</h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted mb-2 text-uppercase" style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em;">Active Users</h6>
            <h2 class="mb-0 fw-bold" id="totalActiveUsersCount" style="letter-spacing: -0.03em;">0</h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted mb-2 text-uppercase" style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em;">Restricted Blogs</h6>
            <h2 class="mb-0 fw-bold" id="totalRestrictedBlogsCount" style="letter-spacing: -0.03em;">0</h2>
        </div>
    </div>

</div>

<div class="form-card">

    <h4 class="mb-4 fw-bold" style="letter-spacing: -0.02em;">Recent Activity</h4>

    <div id="recentBlogsList">
        <p class="text-muted mb-0">
            <i class="bi bi-arrow-clockwise animate-spin me-2"></i>Loading recent blogs...
        </p>
    </div>

</div>

<?php

$content = ob_get_clean();

$scripts = '

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>

const BASE_URL =
    "' . $baseUrl . '";

$(document).ready(function () {

    $.ajax({

        url: BASE_URL + "/api/admin/dashboard",

        type: "GET",

        dataType: "json",

        success: function (response) {

            if (!response.success) {
                return;
            }

            $("#totalUsersCount").text(
                response.data.totalUsers
            );

            $("#totalBlogsCount").text(
                response.data.totalBlogs
            );

            $("#totalActiveUsersCount").text(
                response.data.totalActiveUsers
            );

            $("#totalRestrictedBlogsCount").text(
                response.data.totalRestrictedBlogs
            );

            let recentBlogsHtml = "";

            response.data.recentBlogs.forEach(function(blog) {

                recentBlogsHtml += `
                    <div class="border-bottom pb-3 mb-3" style="border-color: #f0f0f0 !important;">

                        <a
                            href="${BASE_URL}/blogs/details?id=${blog.id}"
                            class="fw-semibold text-decoration-none d-block mb-1 text-dark"
                            style="font-size: 1.05rem;"
                        >
                            ${blog.title}
                        </a>

                        <small class="text-muted d-block" style="font-size: 0.82rem;">
                            by <strong>${blog.creator_name}</strong>
                        </small>

                        <div class="small text-muted mt-1" style="font-size: 0.76rem;">
                            <i class="bi bi-calendar3 me-1"></i> ${blog.created_at}
                        </div>

                    </div>
                `;
            });

            $("#recentBlogsList").html(
                recentBlogsHtml || `
                    <p class="text-muted mb-0">
                        No recent blogs found.
                    </p>
                `
            );
        },

        error: function () {

            $("#recentBlogsList").html(`
                <p class="text-danger mb-0">
                    Failed to load recent activity.
                </p>
            `);
        }
    });
});

</script>
';

require_once __DIR__ . '/../../layouts/AdminLayout.php';