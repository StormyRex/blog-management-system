<?php

$pageTitle = 'Admin Dashboard | BlogSphere';

$baseUrl = defined('BASE_URL') ? BASE_URL : '';

ob_start();

?>

<div class="mb-4">

    <h1 class="h3 mb-2">
        Admin Dashboard
    </h1>

    <p class="text-muted mb-0">
        Welcome to the BlogSphere admin panel.
    </p>

</div>

<div class="row g-4">

    <div class="col-md-3">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <h6 class="text-muted">
                    Total Users
                </h6>

                <h2
                    class="mb-0"
                    id="totalUsersCount"
                >
                    0
                </h2>

            </div>

        </div>

    </div>

    <div class="col-md-3">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <h6 class="text-muted">
                    Total Blogs
                </h6>

                <h2
                    class="mb-0"
                    id="totalBlogsCount"
                >
                    0
                </h2>

            </div>

        </div>

    </div>

    <div class="col-md-3">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <h6 class="text-muted">
                    Active Users
                </h6>

                <h2
                    class="mb-0"
                    id="totalActiveUsersCount"
                >
                    0
                </h2>

            </div>

        </div>

    </div>

    <div class="col-md-3">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <h6 class="text-muted">
                    Restricted Blogs
                </h6>

                <h2
                    class="mb-0"
                    id="totalRestrictedBlogsCount"
                >
                    0
                </h2>

            </div>

        </div>

    </div>

</div>

<div class="card border-0 shadow-sm mt-4">

    <div class="card-body">

        <h4 class="mb-4">
            Recent Activity
        </h4>

        <div id="recentBlogsList">

            <p class="text-muted mb-0">
                Loading recent blogs...
            </p>

        </div>

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
                    <div class="border-bottom pb-3 mb-3">

                        <a
                            href="${BASE_URL}/blogs/details?id=${blog.id}"
                            class="fw-semibold text-decoration-none d-block mb-1"
                        >

                            ${blog.title}

                        </a>

                        <small class="text-muted d-block">
                            by ${blog.creator_name}
                        </small>

                        <div class="small text-muted mt-1">
                            ${blog.created_at}
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