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

<section class="hero-card profile-hero p-4 p-lg-5 mb-4">
    <div class="section-title mb-2 text-white-50">Profile</div>
    <h1 class="fw-bold mb-2"><?php echo htmlspecialchars($heroTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="mb-0 text-white-50">
        <?php echo htmlspecialchars($heroDescription, ENT_QUOTES, 'UTF-8'); ?>
    </p>
</section>

<div class="alert alert-danger d-none" id="profileAlert" role="alert"></div>

<section class="mb-5">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-lg-5">
            <div class="row g-4 align-items-center">
                <div class="col-md-4 col-lg-3 text-center text-md-start">
                    <div id="profileAvatarWrap" class="mb-3 mb-md-0"></div>
                </div>
                <div class="col-md-8 col-lg-9">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                        <div>
                            <div class="section-title mb-2">User Overview</div>
                            <h2 class="fw-bold mb-1" id="profileName">Loading profile...</h2>
                            <p class="text-muted mb-2" id="profileUsername"></p>
                            <p class="mb-3" id="profileBio"></p>
                            <div class="d-flex flex-wrap gap-2 text-muted small">
                                <span id="profileCity"></span>
                                <span id="profileDivider" class="d-none">·</span>
                                <span id="profileBlogsCount"></span>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-2 align-self-start">
                            <a
                                href="<?php echo $baseUrl; ?>/user/edit-profile"
                                class="btn btn-dark d-none"
                                id="editProfileBtn"
                            >
                                Edit Profile
                            </a>
                            <a
                                href="<?php echo $baseUrl; ?>/blogs/create"
                                class="btn btn-outline-dark d-none"
                                id="createBlogBtn"
                            >
                                Create Blog
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mb-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
        <div>
            <div class="section-title mb-2">Blog Listing</div>
            <h3 class="fw-bold mb-0">Published Blogs</h3>
        </div>
        <div class="text-muted" id="blogMeta"></div>
    </div>

    <div id="blogLoading" class="text-muted py-4">Loading blogs...</div>
    <div class="row g-4 d-none" id="blogList"></div>
    <div class="d-none" id="emptyBlogsState">
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <h4 class="fw-bold mb-2">No blogs found</h4>
                <p class="text-muted mb-0">Publish your first blog to see it here.</p>
            </div>
        </div>
    </div>

    <nav class="mt-4 d-none" id="blogPaginationWrap" aria-label="Blog pagination">
        <ul class="pagination justify-content-center mb-0" id="blogPagination"></ul>
    </nav>
</section>

<?php

$apiBaseUrlJs = json_encode($apiBaseUrl);
$profileIdJs = json_encode((int) $profileId);

$pageScripts = <<<'SCRIPT'
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>

$(document).ready(function () {

    const apiBaseUrl = 
SCRIPT
. $apiBaseUrlJs .
<<<'SCRIPT'
;
    const profileId = 
SCRIPT
. $profileIdJs .
<<<'SCRIPT'
;

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function formatDate(value) {
        if (!value) {
            return '';
        }

        var normalized = String(value).trim().replace(' ', 'T');
        if (!normalized.endsWith('Z') && normalized.indexOf('+') === -1 && (normalized.length < 19 || normalized.indexOf('-', 11) === -1)) {
            normalized += 'Z';
        }
        var date = new Date(normalized);

        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return date.toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

    function profileQueryString() {
        return profileId > 0 ? '?id=' + profileId : '';
    }

    function blogQueryString(page) {
        var query = '?page=' + page + '&limit=10';

        if (profileId > 0) {
            query += '&id=' + profileId;
        }

        return query;
    }

    function showAlert(message) {
        $('#profileAlert')
            .removeClass('d-none')
            .text(message);
    }

    function hideAlert() {
        $('#profileAlert')
            .addClass('d-none')
            .text('');
    }

    function renderAvatar(profile) {
        var avatar = profile.avatar || '';
        var name = profile.name || 'User';
        var initial = name.charAt(0).toUpperCase();
        var html = '';

        if (avatar) {
            html = '<img src="' + escapeHtml(avatar) + '" alt="' + escapeHtml(name) + '" class="rounded-circle border border-3 border-white shadow-sm profile-avatar">';
        } else {
            html = '<div class="rounded-circle profile-placeholder shadow-sm fw-bold display-6">' + escapeHtml(initial) + '</div>';
        }

        $('#profileAvatarWrap').html(html);
    }

    function loadProfile() {
        return $.ajax({
            url: apiBaseUrl + '/user/profile' + profileQueryString(),
            type: 'GET',
            dataType: 'json'
        })
        .done(function (response) {
            if (!response || !response.success || !response.data || !response.data.profile) {
                showAlert(response && response.message ? response.message : 'Unable to load profile.');
                return;
            }

            var profile = response.data.profile;

            hideAlert();
            renderAvatar(profile);

            $('#profileName').text(profile.name || '');
            $('#profileUsername').text(profile.username ? '@' + profile.username : '');
            $('#profileBio').text(profile.bio || 'No bio added yet.');

            if (profile.city) {
                $('#profileCity').text(profile.city).removeClass('d-none');
                $('#profileDivider').removeClass('d-none');
            } else {
                $('#profileCity').addClass('d-none').text('');
                $('#profileDivider').addClass('d-none');
            }

            $('#profileBlogsCount').text(profile.totalBlogsCount + ' total blogs');

            if (profile.canEdit) {
                $('#editProfileBtn').removeClass('d-none');
                $('#createBlogBtn').removeClass('d-none');
            }

            $('#blogMeta').text(profile.totalBlogsCount + ' blog(s)');

        })
        .fail(function (xhr) {
            var response = xhr && xhr.responseJSON ? xhr.responseJSON : null;
            showAlert(response && response.message ? response.message : 'Unable to load profile.');
        });
    }

    function renderPagination(pagination) {
        var $wrap = $('#blogPaginationWrap');
        var $pagination = $('#blogPagination');

        $pagination.empty();

        if (!pagination || pagination.totalPages <= 1) {
            $wrap.addClass('d-none');
            return;
        }

        var page = pagination.page || 1;
        var totalPages = pagination.totalPages || 1;
        var startPage = Math.max(1, page - 2);
        var endPage = Math.min(totalPages, page + 2);

        if (page > 1) {
            $pagination.append('<li class="page-item"><button class="page-link" data-page="' + (page - 1) + '">Previous</button></li>');
        }

        for (var index = startPage; index <= endPage; index++) {
            var activeClass = index === page ? ' active' : '';
            $pagination.append('<li class="page-item' + activeClass + '"><button class="page-link" data-page="' + index + '">' + index + '</button></li>');
        }

        if (page < totalPages) {
            $pagination.append('<li class="page-item"><button class="page-link" data-page="' + (page + 1) + '">Next</button></li>');
        }

        $wrap.removeClass('d-none');

        $pagination.find('button').on('click', function () {
            var nextPage = parseInt($(this).data('page'), 10) || 1;
            loadBlogs(nextPage);
        });
    }

    function renderBlogs(blogs) {
        var $blogList = $('#blogList');
        var html = '';

        if (!blogs || !blogs.length) {
            $('#blogLoading').addClass('d-none');
            $blogList.addClass('d-none');
            $('#emptyBlogsState').removeClass('d-none');
            return;
        }

        blogs.forEach(function (blog) {
            var thumbnail = blog.thumbnail && blog.thumbnail.url ? blog.thumbnail.url : '';
            var imageHtml = thumbnail
                ? '<img src="' + escapeHtml(thumbnail) + '" class="card-img-top blog-thumb" alt="' + escapeHtml(blog.title || 'Blog') + '">'
                : '<div class="blog-thumb d-flex align-items-center justify-content-center text-muted">No image available</div>';

            html += '\
                <div class="col-md-6 col-lg-4">\
                    <div class="card h-100 border-0 shadow-sm overflow-hidden">\
                        ' + imageHtml + '\
                        <div class="card-body d-flex flex-column">\
                            <h5 class="card-title fw-bold">' + escapeHtml(blog.title || '') + '</h5>\
                            <p class="card-text text-muted flex-grow-1">' + escapeHtml(blog.shortDescription || '') + '</p>\
                            <div class="d-flex justify-content-between align-items-center gap-2">\
                                <small class="text-muted">' + escapeHtml(formatDate(blog.createdAt)) + '</small>\
                                <a href="<?php echo $baseUrl; ?>/blogs/details?id=' + blog.id + '" class="btn btn-sm btn-outline-dark">View details</a>\
                            </div>\
                        </div>\
                    </div>\
                </div>';
        });

        $('#emptyBlogsState').addClass('d-none');
        $('#blogLoading').addClass('d-none');
        $blogList
            .removeClass('d-none')
            .html(html);
    }

    function loadBlogs(page) {
        currentPage = page || 1;

        $('#blogLoading').removeClass('d-none').text('Loading blogs...');
        $('#blogList').addClass('d-none').empty();
        $('#emptyBlogsState').addClass('d-none');

        $.ajax({
            url: apiBaseUrl + '/user/blogs' + blogQueryString(currentPage),
            type: 'GET',
            dataType: 'json'
        })
        .done(function (response) {
            if (!response || !response.success) {
                $('#blogLoading').addClass('d-none');
                showAlert(response && response.message ? response.message : 'Unable to load blogs.');
                return;
            }

            renderBlogs(response.data && response.data.blogs ? response.data.blogs : []);
            renderPagination(response.data && response.data.pagination ? response.data.pagination : null);
        })
        .fail(function (xhr) {
            $('#blogLoading').addClass('d-none');

            var response = xhr && xhr.responseJSON ? xhr.responseJSON : null;
            showAlert(response && response.message ? response.message : 'Unable to load blogs.');
        });
    }

    loadProfile().done(function () {
        loadBlogs(1);
    });

});

</script>
SCRIPT;

$content = ob_get_clean();

require __DIR__ . '/../../layouts/MainLayout.php';

?>
