<?php

require_once __DIR__ . '/../api/Helpers/DatabaseHelper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle  = 'Home | BlogSphere';
$activePage = 'home';
$baseUrl    = defined('BASE_URL') ? BASE_URL : '';

$currentUser   = $_SESSION['user'] ?? [];
$currentUserId = (int) ($currentUser['id'] ?? 0);
$isLoggedIn    = !empty($_SESSION['user']);

$blogs = fetchAll(
    "SELECT blogs.*, users.name, users.username,
        (SELECT COUNT(*) FROM likes    WHERE blog_id = blogs.id) AS like_count,
        (SELECT COUNT(*) FROM likes    WHERE blog_id = blogs.id AND user_id = ?) AS is_liked,
        (SELECT COUNT(*) FROM shares   WHERE blog_id = blogs.id) AS share_count,
        (SELECT COUNT(*) FROM comments WHERE blog_id = blogs.id) AS comment_count
     FROM blogs
     INNER JOIN users ON users.id = blogs.user_id
     WHERE blogs.visibility = 'PUBLIC' AND blogs.status = 'ACTIVE'
     ORDER BY blogs.created_at DESC LIMIT 3",
    [$currentUserId]
);

$blogIds    = array_column($blogs, 'id');
$thumbnails = getBlogThumbnailMap($blogIds);


ob_start();

?>

<!-- Hero -->
<section class="hero-section">

    <div class="hero-pill">✦ Write · Share · Inspire</div>

    <h1 class="hero-title">
        Your stories,<br>the world's stage.
    </h1>

    <p class="hero-sub">
        Discover, write and share ideas with creators from around the world.
    </p>

    <div class="search-box">
        <i class="bi bi-search"></i>
        <input
            id="globalSearch"
            type="text"
            placeholder="Search blogs, creators…"
            autocomplete="off"
        >
        <button class="search-btn" id="searchButton">Search</button>
        <div id="globalSearchResults" class="search-dropdown"></div>
    </div>

</section>

<!-- Main content -->
<div class="row g-5 mt-1">

    <!-- Blog feed -->
    <div class="col-lg-8">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <div class="text-muted" style="font-size:.72rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase;">Fresh picks</div>
                <h2 style="font-size:1.35rem; font-weight:800; letter-spacing:-.02em; margin:0;">Latest Blogs</h2>
            </div>
            <a href="<?php echo $baseUrl; ?>/explore" class="btn-outline">
                Explore all <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <?php if (empty($blogs)) { ?>

            <div class="text-center py-5 text-muted">
                <i class="bi bi-journal-text" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
                No blogs yet. Be the first!
            </div>

        <?php } ?>

        <div class="d-flex flex-column gap-4">

        <?php foreach ($blogs as $blog) {

            $thumbnail  = $thumbnails[$blog['id']] ?? null;
            $likeCount  = (int) ($blog['like_count']    ?? 0);
            $isLiked    = (int) ($blog['is_liked']      ?? 0) > 0;
            $shareCount = (int) ($blog['share_count']   ?? 0);
            $cmtCount   = (int) ($blog['comment_count'] ?? 0);

        ?>

            <div class="blog-card">

                <!-- Thumbnail -->
                <div class="blog-card-thumb">
                    <?php if ($thumbnail) { ?>

                        <?php if ($thumbnail['type'] === 'IMAGE') { ?>
                            <img src="<?php echo htmlspecialchars($thumbnail['url']); ?>" alt="">
                        <?php } else { ?>
                            <video preload="metadata" muted>
                                <source src="<?php echo htmlspecialchars($thumbnail['url']); ?>">
                            </video>
                        <?php } ?>

                    <?php } else { ?>
                        <div class="no-media"><i class="bi bi-image"></i></div>
                    <?php } ?>
                </div>

                <!-- Body -->
                <div class="p-4 pb-2">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="blog-badge"><?php echo htmlspecialchars($blog['category']); ?></span>
                        <span class="text-muted" style="font-size:.8rem;">@<?php echo htmlspecialchars($blog['username']); ?></span>
                    </div>

                    <div class="blog-card-title mb-2"><?php echo htmlspecialchars($blog['title']); ?></div>

                    <div class="blog-card-desc mb-3"><?php echo htmlspecialchars(substr($blog['description'], 0, 200)); ?>…</div>

                    <div class="text-muted mb-0" style="font-size:.78rem;">
                        <i class="bi bi-person"></i> <?php echo htmlspecialchars($blog['name']); ?>
                        &nbsp;·&nbsp;
                        <i class="bi bi-calendar3"></i> <?php echo date('M d, Y', strtotime($blog['created_at'])); ?>
                    </div>

                </div>

                <!-- Actions -->
                <div class="px-4 py-3 d-flex flex-wrap gap-2" style="border-top:1px solid #f0f0f0;">

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

        <?php } ?>

        </div>

    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">

        <div class="sidebar-box">

            <div class="sidebar-header">
                <div class="text-muted" style="font-size:.7rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; margin-bottom:2px;">Activity</div>
                <div class="sidebar-title">Recently Visited</div>
            </div>

            <div id="visitHistoryList">
                <div class="p-4 text-center text-muted" style="font-size:.875rem;">
                    <i class="bi bi-clock-history d-block mb-2" style="font-size:1.6rem; opacity:.35;"></i>
                    No blogs visited yet
                </div>
            </div>

        </div>

    </div>

</div>

<!-- CTA banner (guests only) -->
<?php if (!$isLoggedIn) { ?>

    <div class="cta-block mt-5">
        <h2 class="mb-2">Ready to start writing?</h2>
        <p>Join BlogSphere and share your stories with thousands of readers.</p>
        <a href="<?php echo $baseUrl; ?>/register" class="btn-cta">Create a free account →</a>
    </div>

<?php } ?>

<?php require __DIR__ . '/../components/Comment.php'; ?>

<?php

$content = ob_get_clean();

$baseUrlJs = json_encode($baseUrl);

$pageScripts = ($pageScripts ?? '') . <<<'SCRIPT'
<script>

$(document).ready(function () {

    var isLoggedIn = $('body').attr('data-is-logged-in') === '1';
    var baseUrl    =
SCRIPT
. $baseUrlJs .
<<<'SCRIPT'
;

    // ── Recently visited ────────────────────────────────────────────────
    var history = [];
    try { history = JSON.parse(localStorage.getItem('blogVisitHistory') || '[]'); } catch (e) {}

    if (history.length) {

        var $list = $('#visitHistoryList').empty();

        history.slice(0, 6).forEach(function (item) {

            $list.append(
                $('<a>')
                    .addClass('history-link')
                    .attr('href', baseUrl + '/blogs/details?id=' + item.id)
                    .html(
                        '<div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + $('<span>').text(item.title).html() + '</div>' +
                        '<div class="history-date">' + $('<span>').text(item.visitedAt).html() + '</div>'
                    )
            );

        });
    }

    // ── Action buttons (like / comment / share) ─────────────────────────
    $(document).on('click', '[data-action]', function () {

        var $btn   = $(this);
        var action = $btn.data('action');
        var blogId = $btn.data('blog-id');
        var title  = $btn.data('title') || '';

        if ($btn.data('requires-login') && !isLoggedIn) {
            window.BlogSphere && window.BlogSphere.showGuestModal();
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

    // Comment count callback (called by Comment.php)
    window.updateBlogCommentCount = function (blogId, count) {
        $('#cmt-' + blogId).text(parseInt(count, 10) || 0);
    };

    // ── Search ──────────────────────────────────────────────────────────
    var searchTimer = null;
    var $inp        = $('#globalSearch');
    var $res        = $('#globalSearchResults');

    $inp.on('input', function () {

        var q = $(this).val().trim();

        clearTimeout(searchTimer);

        if (q.length < 2) {
            $res.hide().empty();
            return;
        }

        searchTimer = setTimeout(function () {

            $.get(baseUrl + '/api/search', { q: q }, function (data) {

                $res.empty();

                var blogs = (data.success && data.data && data.data.blogs)
                    ? data.data.blogs.slice(0, 5)
                    : [];

                if (!blogs.length) {
                    $res.html('<div style="padding:14px;text-align:center;color:#aaa;font-size:.88rem;">No results found</div>').show();
                    return;
                }

                blogs.forEach(function (b) {

                    var thumbHtml = (b.thumbnail && b.thumbnail.url && b.thumbnail.type === 'IMAGE')
                        ? '<img src="' + b.thumbnail.url + '" style="width:100%;height:100%;object-fit:cover;">'
                        : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#ccc;"><i class="bi bi-image"></i></div>';

                    $res.append(
                        $('<a>')
                            .addClass('search-result-item')
                            .attr('href', baseUrl + '/blogs/details?id=' + b.id)
                            .append($('<div>').addClass('search-thumb').html(thumbHtml))
                            .append(
                                $('<div>').append(
                                    $('<div>').addClass('search-result-title').text(b.title || 'Untitled'),
                                    $('<div>').addClass('search-result-meta').text('By ' + (b.name || '—') + ' · @' + (b.username || '—'))
                                )
                            )
                    );
                });

                $res.show();

            }, 'json');

        }, 280);
    });

    $('#searchButton').on('click', function () {
        var q = $inp.val().trim();
        if (q) {
            window.location.href = baseUrl + '/explore?q=' + encodeURIComponent(q);
        }
    });

    $inp.on('keydown', function (e) {
        if (e.key === 'Enter') {
            $('#searchButton').trigger('click');
        }
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#globalSearch, #globalSearchResults').length) {
            $res.hide();
        }
    });

});

</script>
SCRIPT;

require __DIR__ . '/../layouts/MainLayout.php';

?>