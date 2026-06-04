<?php

$pageTitle = 'Profile | BlogSphere';
$activePage = 'profile';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$apiBaseUrl = defined('BASE_API_URL') ? BASE_API_URL : '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$profileId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// If no profile id provided, require login to view own profile.
// If a profile id is provided, allow anonymous viewing of public profiles.
$currentUser = $_SESSION['user'] ?? null;
$currentUserId = (int) ($currentUser['id'] ?? 0);

if ($profileId <= 0 && empty($currentUser)) {
    header('Location: ' . $baseUrl . '/auth/login');
    exit;
}

$isOwnProfile = $profileId <= 0 || $profileId === $currentUserId;
$heroTitle = $isOwnProfile ? 'My Profile' : 'Profile';
$heroDescription = $isOwnProfile
    ? 'Review your account details and manage the blogs you have published.'
    : 'Review this creator profile and their published blogs.';

ob_start();
?>

<div class="container py-5">

    <!-- Header section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-5 mt-3">
        <div>
            <span class="hero-pill mb-2">Profile</span>
            <h1 class="hero-title text-start mb-1" style="font-size: 2.2rem; font-weight: 800; letter-spacing: -0.04em;">
                <?php echo htmlspecialchars($heroTitle, ENT_QUOTES, 'UTF-8'); ?>
            </h1>
            <p class="text-muted mb-0" style="font-size: 0.95rem;">
                <?php echo htmlspecialchars($heroDescription, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        </div>
    </div>

    <div class="alert alert-danger d-none mb-4" id="profileAlert" role="alert"></div>

    <!-- Profile overview card -->
    <section class="mb-5">
        <div class="form-card">
            <div class="row g-4 align-items-center">
                <div class="col-md-4 col-lg-3 text-center text-md-start">
                    <div id="profileAvatarWrap" class="mb-3 mb-md-0"></div>
                </div>
                <div class="col-md-8 col-lg-9">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
                        <div>
                            <span class="hero-pill mb-3">User Overview</span>
                            <h2 class="fw-bold mb-1" id="profileName" style="letter-spacing: -0.03em;">Loading profile...</h2>
                            <p class="text-muted mb-3" id="profileUsername" style="font-weight: 500;"></p>
                            <p class="mb-4 text-secondary" id="profileBio" style="font-size: 1rem; line-height: 1.6;"></p>
                            <div class="d-flex flex-wrap gap-2 align-items-center text-muted small">
                                <span id="profileCity"><i class="bi bi-geo-alt me-1"></i></span>
                                <span id="profileDivider" class="d-none">·</span>
                                <span id="profileBlogsCount"><i class="bi bi-journal-text me-1"></i></span>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-2 align-self-start">
                            <a
                                href="<?php echo $baseUrl; ?>/user/edit-profile"
                                class="btn-solid py-2 px-4 d-none"
                                id="editProfileBtn"
                            >
                                <i class="bi bi-pencil me-1"></i> Edit Profile
                            </a>
                            <a
                                href="<?php echo $baseUrl; ?>/blogs/create"
                                class="btn-outline py-2 px-4 d-none"
                                id="createBlogBtn"
                            >
                                <i class="bi bi-plus-lg me-1"></i> Create Blog
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Blogs list -->
    <section class="mb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <span class="hero-pill mb-2">Blog Listing</span>
                <h3 class="fw-bold mb-0" style="letter-spacing: -0.03em;">Published Blogs</h3>
            </div>
            <div class="text-muted" id="blogMeta" style="font-size: 0.9rem; font-weight: 500;"></div>
        </div>

        <div id="blogLoading" class="text-muted py-4"><i class="bi bi-arrow-clockwise animate-spin me-2"></i>Loading blogs...</div>
        <div class="row g-4 d-none" id="blogList"></div>
        <div class="d-none" id="emptyBlogsState">
            <div class="form-card text-center py-5">
                <i class="bi bi-journal-text text-muted" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                <h4 class="fw-bold mb-2">No blogs found</h4>
                <p class="text-muted mb-0">This user hasn't published any blogs yet.</p>
            </div>
        </div>

        <nav class="mt-5 d-none" id="blogPaginationWrap" aria-label="Blog pagination">
            <ul class="pagination justify-content-center mb-0" id="blogPagination"></ul>
        </nav>
    </section>

</div>

<?php

$apiBaseUrlJs = json_encode($apiBaseUrl);
$profileIdJs = json_encode((int) $profileId);

$pageScripts = '
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function () {
    const apiBaseUrl = ' . $apiBaseUrlJs . ';
    const profileId = ' . $profileIdJs . ';

    function escapeHtml(value) {
        return $("<div>").text(value || "").html();
    }

    function formatDate(value) {
        if (!value) return "";

        var normalized = String(value).trim().replace(" ", "T");
        if (!normalized.endsWith("Z") && normalized.indexOf("+") === -1 && (normalized.length < 19 || normalized.indexOf("-", 11) === -1)) {
            normalized += "Z";
        }
        var date = new Date(normalized);
        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return date.toLocaleDateString("en-IN", {
            day: "2-digit",
            month: "short",
            year: "numeric"
        });
    }

    function profileQueryString() {
        return profileId > 0 ? "?id=" + profileId : "";
    }

    function blogQueryString(page) {
        var query = "?page=" + page + "&limit=10";
        if (profileId > 0) {
            query += "&id=" + profileId;
        }
        return query;
    }

    function showAlert(message) {
        $("#profileAlert").removeClass("d-none").text(message);
    }

    function hideAlert() {
        $("#profileAlert").addClass("d-none").text("");
    }

    function renderAvatar(profile) {
        var avatar = profile.avatar || "";
        var name = profile.name || "User";
        var initial = name.charAt(0).toUpperCase();
        var html = "";

        if (avatar) {
            html = `<img src="${escapeHtml(avatar)}" alt="${escapeHtml(name)}" class="rounded-circle border border-3 border-white shadow-sm profile-avatar">`;
        } else {
            html = `<div class="rounded-circle profile-placeholder shadow-sm fw-bold display-6">${escapeHtml(initial)}</div>`;
        }

        $("#profileAvatarWrap").html(html);
    }

    function loadProfile() {
        return $.ajax({
            url: apiBaseUrl + "/user/profile" + profileQueryString(),
            type: "GET",
            dataType: "json"
        })
        .done(function (response) {
            if (!response || !response.success || !response.data || !response.data.profile) {
                showAlert(response && response.message ? response.message : "Unable to load profile.");
                return;
            }

            var profile = response.data.profile;
            hideAlert();
            renderAvatar(profile);

            $("#profileName").text(profile.name || "");
            $("#profileUsername").text(profile.username ? "@" + profile.username : "");
            $("#profileBio").text(profile.bio || "No bio added yet.");

            if (profile.city) {
                $("#profileCity").text(profile.city).removeClass("d-none");
                $("#profileDivider").removeClass("d-none");
            } else {
                $("#profileCity").addClass("d-none").text("");
                $("#profileDivider").addClass("d-none");
            }

            $("#profileBlogsCount").text(profile.totalBlogsCount + " total blogs");

            if (profile.canEdit) {
                $("#editProfileBtn").removeClass("d-none");
                $("#createBlogBtn").removeClass("d-none");
            }

            $("#blogMeta").text(profile.totalBlogsCount + " blog(s)");
        })
        .fail(function (xhr) {
            var response = xhr && xhr.responseJSON ? xhr.responseJSON : null;
            showAlert(response && response.message ? response.message : "Unable to load profile.");
        });
    }

    function renderPagination(pagination) {
        var $wrap = $("#blogPaginationWrap");
        var $pagination = $("#blogPagination");

        $pagination.empty();

        if (!pagination || pagination.totalPages <= 1) {
            $wrap.addClass("d-none");
            return;
        }

        var page = pagination.page || 1;
        var totalPages = pagination.totalPages || 1;
        var startPage = Math.max(1, page - 2);
        var endPage = Math.min(totalPages, page + 2);

        if (page > 1) {
            $pagination.append(`<li class="page-item"><button class="page-link" data-page="${page - 1}">Previous</button></li>`);
        }

        for (var index = startPage; index <= endPage; index++) {
            var activeClass = index === page ? " active" : "";
            $pagination.append(`<li class="page-item${activeClass}"><button class="page-link" data-page="${index}">${index}</button></li>`);
        }

        if (page < totalPages) {
            $pagination.append(`<li class="page-item"><button class="page-link" data-page="${page + 1}">Next</button></li>`);
        }

        $wrap.removeClass("d-none");

        $pagination.find("button").on("click", function () {
            var nextPage = parseInt($(this).data("page"), 10) || 1;
            loadBlogs(nextPage);
        });
    }

    function renderBlogs(blogs) {
        var $blogList = $("#blogList");
        var html = "";

        if (!blogs || !blogs.length) {
            $("#blogLoading").addClass("d-none");
            $blogList.addClass("d-none");
            $("#emptyBlogsState").removeClass("d-none");
            return;
        }

        blogs.forEach(function (blog) {
            var thumbnail = blog.thumbnail && blog.thumbnail.url ? blog.thumbnail.url : "";
            var mediaHtml = "";
            if (thumbnail) {
                if (blog.thumbnail.type === "VIDEO") {
                    mediaHtml = `<video preload="metadata" muted><source src="${escapeHtml(thumbnail)}"></video>`;
                } else {
                    mediaHtml = `<img src="${escapeHtml(thumbnail)}" alt="${escapeHtml(blog.title || "")}">`;
                }
            } else {
                mediaHtml = \'<div class="no-media"><i class="bi bi-image"></i></div>\';
            }

            var safeTitle = escapeHtml(blog.title || "");
            var safeDesc = escapeHtml(blog.shortDescription || "");
            var safeCategory = escapeHtml(blog.category || "General");

            html += `
                <div class="col-md-6 col-lg-4">
                    <div class="blog-card h-100 d-flex flex-column">
                        <a href="<?php echo $baseUrl; ?>/blogs/details?id=${blog.id}" class="blog-card-thumb" style="text-decoration:none;">
                            ${mediaHtml}
                        </a>
                        <div class="p-3 pb-2 flex-grow-1 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="blog-badge">${safeCategory}</span>
                            </div>
                            <a href="<?php echo $baseUrl; ?>/blogs/details?id=${blog.id}" class="blog-card-title mb-2" style="text-decoration:none; color:inherit;">
                                ${safeTitle}
                            </a>
                            <div class="blog-card-desc mb-3 flex-grow-1">
                                ${safeDesc}
                            </div>
                            <div style="font-size:.76rem; color:#999;">
                                <i class="bi bi-calendar3"></i> ${escapeHtml(formatDate(blog.createdAt))}
                            </div>
                        </div>
                        <div class="px-3 py-2 d-flex flex-wrap gap-2" style="border-top:1px solid #f0f0f0;">
                            <a href="<?php echo $baseUrl; ?>/blogs/details?id=${blog.id}" class="action-btn view ms-auto">
                                Read <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>`;
        });

        $("#emptyBlogsState").addClass("d-none");
        $("#blogLoading").addClass("d-none");
        $blogList.removeClass("d-none").html(html);
    }

    function loadBlogs(page) {
        currentPage = page || 1;

        $("#blogLoading").removeClass("d-none").text("Loading blogs...");
        $("#blogList").addClass("d-none").empty();
        $("#emptyBlogsState").addClass("d-none");

        $.ajax({
            url: apiBaseUrl + "/user/blogs" + blogQueryString(currentPage),
            type: "GET",
            dataType: "json"
        })
        .done(function (response) {
            if (!response || !response.success) {
                $("#blogLoading").addClass("d-none");
                showAlert(response && response.message ? response.message : "Unable to load blogs.");
                return;
            }

            renderBlogs(response.data && response.data.blogs ? response.data.blogs : []);
            renderPagination(response.data && response.data.pagination ? response.data.pagination : null);
        })
        .fail(function (xhr) {
            $("#blogLoading").addClass("d-none");
            var response = xhr && xhr.responseJSON ? xhr.responseJSON : null;
            showAlert(response && response.message ? response.message : "Unable to load blogs.");
        });
    }

    loadProfile().done(function () {
        loadBlogs(1);
    });
});
</script>
';

$content = ob_get_clean();

require __DIR__ . '/../../layouts/MainLayout.php';

?>
