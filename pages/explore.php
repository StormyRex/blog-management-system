<?php

require_once __DIR__ . '/../api/Helpers/DatabaseHelper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUser   = $_SESSION['user'] ?? [];
$currentUserId = (int) ($currentUser['id'] ?? 0);
$isLoggedIn    = !empty($_SESSION['user']);

$pageTitle  = 'Explore | BlogSphere';
$activePage = 'explore';
$baseUrl    = defined('BASE_URL') ? BASE_URL : '';

$q = trim($_GET['q'] ?? '');

$query = "
    SELECT
        blogs.*,
        users.name,
        users.username,
        (SELECT COUNT(*) FROM likes    WHERE blog_id = blogs.id)                      AS like_count,
        (SELECT COUNT(*) FROM likes    WHERE blog_id = blogs.id AND user_id = ?)      AS is_liked,
        (SELECT COUNT(*) FROM shares   WHERE blog_id = blogs.id)                      AS share_count,
        (SELECT COUNT(*) FROM comments WHERE blog_id = blogs.id)                      AS comment_count
    FROM blogs
    INNER JOIN users ON users.id = blogs.user_id
    WHERE blogs.visibility = 'PUBLIC'
    AND blogs.status = 'ACTIVE'
";

$params = [$currentUserId];

if ($q !== '') {

    if (str_starts_with($q, '#')) {

        $tag     = substr($q, 1);
        $query  .= " AND blogs.hashtags LIKE ?";
        $params[] = '%' . $tag . '%';

    } else if (str_starts_with($q, '@')) {

        $username = substr($q, 1);
        $query   .= " AND users.username LIKE ?";
        $params[] = '%' . $username . '%';

    } else {

        $query   .= " AND (blogs.title LIKE ? OR blogs.description LIKE ?)";
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';

    }

}

$query .= " ORDER BY blogs.created_at DESC";

$blogs = fetchAll($query, $params);

$blogIds    = array_column($blogs, 'id');
$thumbnails = getBlogThumbnailMap($blogIds);

foreach ($blogs as &$blog) {
    $blog['thumbnail'] = $thumbnails[$blog['id']] ?? null;
}
unset($blog);

ob_start();

?>

<!-- Page header -->
<div class="explore-header">

    <div class="hero-pill">✦ Discover</div>

    <h1 class="hero-title" style="font-size: clamp(1.8rem, 4vw, 3rem);">
        Explore Blogs & Creators
    </h1>

    <p class="hero-sub">
        Search creators, topics and fresh ideas shared across BlogSphere.
    </p>

    <form method="GET" class="search-box" style="max-width: 560px;">
        <i class="bi bi-search"></i>
        <input
            type="text"
            name="q"
            id="exploreSearch"
            placeholder="Search blogs, @creator or #hashtag"
            value="<?php echo htmlspecialchars($q); ?>"
            autocomplete="off"
        >
        <button type="submit" class="search-btn">Search</button>
    </form>

    <?php if ($q !== '') { ?>
        <div class="mt-3" style="font-size: 0.88rem; color: #666;">
            Showing results for <strong>"<?php echo htmlspecialchars($q); ?>"</strong>
            &nbsp;·&nbsp;
            <a href="<?php echo $baseUrl; ?>/explore" style="color:#111;">Clear</a>
        </div>
    <?php } ?>

</div>

<!-- Results -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="text-muted" style="font-size:.72rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase;">Results</div>
        <h2 style="font-size:1.35rem; font-weight:800; letter-spacing:-.02em; margin:0;">
            <?php echo $q !== '' ? 'Search Results' : 'All Public Blogs'; ?>
        </h2>
    </div>
    <span class="text-muted" style="font-size:.85rem;"><?php echo count($blogs); ?> blog<?php echo count($blogs) !== 1 ? 's' : ''; ?></span>
</div>

<div id="exploreResults">

    <?php if (empty($blogs)) { ?>

        <div class="text-center py-5 text-muted">
            <i class="bi bi-search" style="font-size:2.5rem; display:block; margin-bottom:12px; opacity:.4;"></i>
            <?php echo $q !== '' ? 'No results found for "' . htmlspecialchars($q) . '".' : 'No public blogs available yet.'; ?>
        </div>

    <?php } else { ?>

    <div class="row g-4">

        <?php foreach ($blogs as $blog) {

            $thumbnail  = $blog['thumbnail'] ?? null;
            $likeCount  = (int) ($blog['like_count']    ?? 0);
            $isLiked    = (int) ($blog['is_liked']      ?? 0) > 0;
            $shareCount = (int) ($blog['share_count']   ?? 0);
            $cmtCount   = (int) ($blog['comment_count'] ?? 0);

        ?>

        <div class="col-md-6 col-lg-4">

            <div class="blog-card h-100 d-flex flex-column">

                <!-- Thumbnail -->
                <a href="<?php echo $baseUrl; ?>/blogs/details?id=<?php echo (int) $blog['id']; ?>" class="blog-card-thumb" style="text-decoration:none;">
                    <?php if ($thumbnail && !empty($thumbnail['url'])) { ?>

                        <?php if ($thumbnail['type'] === 'IMAGE') { ?>
                            <img src="<?php echo htmlspecialchars($thumbnail['url']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>">
                        <?php } else { ?>
                            <video preload="metadata" muted>
                                <source src="<?php echo htmlspecialchars($thumbnail['url']); ?>">
                            </video>
                        <?php } ?>

                    <?php } else { ?>
                        <div class="no-media"><i class="bi bi-image"></i></div>
                    <?php } ?>
                </a>

                <!-- Body -->
                <div class="p-3 pb-2 flex-grow-1 d-flex flex-column">

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="blog-badge"><?php echo htmlspecialchars($blog['category']); ?></span>
                        <span class="text-muted" style="font-size:.78rem;">@<?php echo htmlspecialchars($blog['username']); ?></span>
                    </div>

                    <a href="<?php echo $baseUrl; ?>/blogs/details?id=<?php echo (int) $blog['id']; ?>" class="blog-card-title mb-2" style="text-decoration:none; color:inherit;">
                        <?php echo htmlspecialchars($blog['title']); ?>
                    </a>

                    <div class="blog-card-desc mb-2 flex-grow-1">
                        <?php echo htmlspecialchars(substr($blog['description'], 0, 120)); ?>…
                    </div>

                    <?php if (!empty($blog['hashtags'])) { ?>
                        <div class="mb-2" style="font-size:.75rem; color:#999;">
                            <?php echo htmlspecialchars($blog['hashtags']); ?>
                        </div>
                    <?php } ?>

                    <div style="font-size:.76rem; color:#999;">
                        <i class="bi bi-person"></i> <?php echo htmlspecialchars($blog['name']); ?>
                        &nbsp;·&nbsp;
                        <i class="bi bi-calendar3"></i> <?php echo date('M d, Y', strtotime($blog['created_at'])); ?>
                    </div>

                </div>

                <!-- Actions -->
                <div class="px-3 py-2 d-flex flex-wrap gap-2" style="border-top:1px solid #f0f0f0;">

                    <button
                        class="action-btn <?php echo $isLiked ? 'liked' : ''; ?>"
                        data-action="like"
                        data-requires-login="1"
                        data-blog-id="<?php echo (int) $blog['id']; ?>"
                        data-like-count="<?php echo $likeCount; ?>"
                        data-liked="<?php echo $isLiked ? '1' : '0'; ?>"
                        data-title="<?php echo htmlspecialchars($blog['title']); ?>"
                    >
                        <i class="bi <?php echo $isLiked ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                        <span class="like-count"><?php echo $likeCount; ?></span>
                    </button>

                    <button
                        class="action-btn"
                        data-action="comment"
                        data-requires-login="1"
                        data-blog-id="<?php echo (int) $blog['id']; ?>"
                        data-title="<?php echo htmlspecialchars($blog['title']); ?>"
                    >
                        <i class="bi bi-chat"></i>
                        <span id="cmt-<?php echo $blog['id']; ?>"><?php echo $cmtCount; ?></span>
                    </button>

                    <button
                        class="action-btn"
                        data-action="share"
                        data-share-url="<?php echo $baseUrl; ?>/blogs/details?id=<?php echo (int) $blog['id']; ?>"
                        data-blog-id="<?php echo (int) $blog['id']; ?>"
                        data-share-count="<?php echo $shareCount; ?>"
                        data-title="<?php echo htmlspecialchars($blog['title']); ?>"
                    >
                        <i class="bi bi-share"></i>
                        <span class="share-count"><?php echo $shareCount; ?></span>
                    </button>

                    <a
                        href="<?php echo $baseUrl; ?>/blogs/details?id=<?php echo (int) $blog['id']; ?>"
                        class="action-btn view ms-auto"
                    >
                        Read <i class="bi bi-arrow-right"></i>
                    </a>

                </div>

            </div>

        </div>

        <?php } ?>

    </div>

    <?php } ?>

</div>

<?php require __DIR__ . '/../components/Comment.php'; ?>

<?php

$content = ob_get_clean();

$baseUrlJs = json_encode($baseUrl);

$pageScripts = ($pageScripts ?? '') . '
<script>
    var baseUrl = ' . $baseUrlJs . ';
';

$pageScripts = ($pageScripts ?? '') . <<<'SCRIPT'
$(document).ready(function () {
    var isLoggedIn = $('body').attr('data-is-logged-in') === '1';

    var initialHtml = $('#exploreResults').html();
    var timer       = null;

    $('#exploreSearch').closest('form').on('submit', function (e) {
        if ($('#exploreSearch').val().trim().length === 0) {
            e.preventDefault();
            $('#exploreResults').html(initialHtml);
        }
    });

    $('#exploreSearch').on('keyup', function () {
        var q = $(this).val().trim();
        clearTimeout(timer);

        if (q.length === 0) {
            $('#exploreResults').html(initialHtml);
            return;
        }

        timer = setTimeout(function () {
            $.get(baseUrl + '/api/search', { q: q }, function (res) {
                if (res && res.success) {
                    renderResults(res.data || { blogs: [], users: [] });
                }
            }, 'json');
        }, 300);
    });

    window.updateBlogCommentCount = function (blogId, count) {
        $('#cmt-' + blogId).text(parseInt(count, 10) || 0);
    };

    $(document).on('click', '[data-action]', function () {
        var $btn   = $(this);
        var action = $btn.data('action');
        var blogId = $btn.data('blog-id');
        var title  = $btn.data('title') || '';

        if ($btn.data('requires-login') && !isLoggedIn) {
            if (window.BlogSphere && window.BlogSphere.showGuestModal) {
                window.BlogSphere.showGuestModal();
            }
            return;
        }

        if (action === 'like') {
            var liked    = $btn.data('liked') == '1';
            var newLiked = !liked;
            var count    = parseInt($btn.data('like-count'), 10) || 0;
            var newCount = newLiked ? count + 1 : Math.max(0, count - 1);

            $btn.toggleClass('liked', newLiked);
            $btn.find('i').toggleClass('bi-heart-fill', newLiked).toggleClass('bi-heart', !newLiked);
            $btn.find('.like-count').text(newCount);
            $btn.data({ liked: newLiked ? '1' : '0', 'like-count': newCount });

            $.post(baseUrl + '/api/blogs/like', { blogId: blogId });
        }

        if (action === 'comment') {
            if (window.BlogSphere && window.BlogSphere.openCommentModal) {
                window.BlogSphere.openCommentModal(blogId, title);
            }
        }

        if (action === 'share') {
            var shareUrl = $btn.data('share-url');
            var sc       = parseInt($btn.data('share-count'), 10) || 0;

            $btn.find('.share-count').text(sc + 1);
            $btn.data('share-count', sc + 1);

            $.post(baseUrl + '/api/blogs/share', { blogId: blogId });

            if (navigator.share) {
                navigator.share({ title: title, url: shareUrl }).catch(function () {});
            } else {
                navigator.clipboard.writeText(shareUrl).then(function () {
                    var orig = $btn.html();
                    $btn.html('<i class="bi bi-check2"></i> Copied!');
                    setTimeout(function () { $btn.html(orig); }, 1800);
                });
            }
        }
    });

    function escapeHtml(val) {
        return String(val || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderResults(data) {
        var blogs = data.blogs || [];
        var users = data.users || [];

        if (!blogs.length && !users.length) {
            $('#exploreResults').html(
                '<div class="text-center py-5 text-muted">' +
                '<i class="bi bi-search" style="font-size:2.5rem;display:block;margin-bottom:12px;opacity:.4;"></i>' +
                'No results found.</div>'
            );
            return;
        }

        var html = '<div class="row g-4">';

        blogs.forEach(function (blog) {
            var blogId  = parseInt(blog.id, 10) || 0;
            var thumb   = blog.thumbnail && blog.thumbnail.url ? escapeHtml(blog.thumbnail.url) : '';
            var ttype   = blog.thumbnail && blog.thumbnail.type ? blog.thumbnail.type : '';
            var mediaHtml = '<div class="no-media"><i class="bi bi-image"></i></div>';

            if (thumb) {
                mediaHtml = ttype === 'VIDEO'
                    ? '<video preload="metadata" muted style="width:100%;height:100%;object-fit:cover;"><source src="' + thumb + '"></video>'
                    : '<img src="' + thumb + '" alt="' + escapeHtml(blog.title) + '" style="width:100%;height:100%;object-fit:cover;">';
            }

            var likeCount  = parseInt(blog.like_count || 0, 10);
            var isLiked    = parseInt(blog.is_liked || 0, 10) > 0;
            var shareCount = parseInt(blog.share_count || 0, 10);
            var cmtCount   = parseInt(blog.comment_count || 0, 10);
            var likedClass = isLiked ? 'liked' : '';
            var heartIcon  = isLiked ? 'bi-heart-fill' : 'bi-heart';

            var safeTitle    = escapeHtml(blog.title || 'Untitled');
            var safeCategory = escapeHtml(blog.category || '');
            var safeUsername = escapeHtml(blog.username || '');
            var safeDesc     = escapeHtml((blog.description || '').substring(0, 120));
            var safeTags     = escapeHtml(blog.hashtags || '');
            var safeName     = escapeHtml(blog.name || '');

            html +=
                '<div class="col-md-6 col-lg-4">' +
                '<div class="blog-card h-100 d-flex flex-column">' +
                '<a href="' + baseUrl + '/blogs/details?id=' + blogId + '" class="blog-card-thumb" style="text-decoration:none;">' +
                    mediaHtml +
                '</a>' +
                '<div class="p-3 pb-2 flex-grow-1 d-flex flex-column">' +
                    '<div class="d-flex align-items-center justify-content-between mb-2">' +
                        '<span class="blog-badge">' + safeCategory + '</span>' +
                        '<span class="text-muted" style="font-size:.78rem;">@' + safeUsername + '</span>' +
                    '</div>' +
                    '<a href="' + baseUrl + '/blogs/details?id=' + blogId + '" class="blog-card-title mb-2" style="text-decoration:none;color:inherit;">' + safeTitle + '</a>' +
                    '<div class="blog-card-desc mb-2 flex-grow-1">' + safeDesc + '…</div>' +
                    (safeTags ? '<div class="mb-2" style="font-size:.75rem;color:#999;">' + safeTags + '</div>' : '') +
                    '<div style="font-size:.76rem;color:#999;"><i class="bi bi-person"></i> ' + safeName + '</div>' +
                '</div>' +
                '<div class="px-3 py-2 d-flex flex-wrap gap-2" style="border-top:1px solid #f0f0f0;">' +
                    '<button class="action-btn ' + likedClass + '" data-action="like" data-requires-login="1" data-blog-id="' + blogId + '" data-like-count="' + likeCount + '" data-liked="' + (isLiked ? '1' : '0') + '" data-title="' + safeTitle + '">' +
                        '<i class="bi ' + heartIcon + '"></i> <span class="like-count">' + likeCount + '</span>' +
                    '</button>' +
                    '<button class="action-btn" data-action="comment" data-requires-login="1" data-blog-id="' + blogId + '" data-title="' + safeTitle + '">' +
                        '<i class="bi bi-chat"></i> <span>' + cmtCount + '</span>' +
                    '</button>' +
                    '<button class="action-btn" data-action="share" data-share-url="' + baseUrl + '/blogs/details?id=' + blogId + '" data-blog-id="' + blogId + '" data-share-count="' + shareCount + '" data-title="' + safeTitle + '">' +
                        '<i class="bi bi-share"></i> <span class="share-count">' + shareCount + '</span>' +
                    '</button>' +
                    '<a href="' + baseUrl + '/blogs/details?id=' + blogId + '" class="action-btn view ms-auto">Read <i class="bi bi-arrow-right"></i></a>' +
                '</div>' +
                '</div></div>';
        });

        users.forEach(function (user) {
            var userId = parseInt(user.id, 10) || 0;
            html +=
                '<div class="col-md-6 col-lg-4">' +
                '<div class="blog-card h-100">' +
                '<div class="p-4">' +
                    '<span class="blog-badge mb-3 d-inline-block" style="background:#555;">Creator</span>' +
                    '<div class="blog-card-title mb-1">' + escapeHtml(user.name || '') + '</div>' +
                    '<div style="font-size:.82rem;color:#888;margin-bottom:8px;">@' + escapeHtml(user.username || '') + '</div>' +
                    '<div class="blog-card-desc mb-3">' + escapeHtml(user.bio || '') + '</div>' +
                    '<a href="' + baseUrl + '/user/profile?id=' + userId + '" class="action-btn view">View Profile <i class="bi bi-arrow-right"></i></a>' +
                '</div>' +
                '</div></div>';
        });

        html += '</div>';
        $('#exploreResults').html(html);
    }
});
</script>
SCRIPT;

require __DIR__ . '/../layouts/MainLayout.php';

?>