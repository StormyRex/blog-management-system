<?php

require_once __DIR__ . '/../../api/Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../../api/Helpers/UploadHelper.php';

$pageTitle  = 'Blog Details | BlogSphere';
$activePage = '';

$baseUrl = defined('BASE_URL') ? BASE_URL : '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: ' . $baseUrl . '/explore');
    exit;
}

$currentUser   = $_SESSION['user'] ?? [];
$currentUserId = (int) ($currentUser['id'] ?? 0);

$blog = fetchOne(
    "SELECT
        blogs.*,
        users.name,
        users.username,
        (SELECT COUNT(*) FROM likes    WHERE blog_id = blogs.id)                 AS like_count,
        (SELECT COUNT(*) FROM likes    WHERE blog_id = blogs.id AND user_id = ?) AS is_liked,
        (SELECT COUNT(*) FROM shares   WHERE blog_id = blogs.id)                 AS share_count,
        (SELECT COUNT(*) FROM comments WHERE blog_id = blogs.id)                 AS comment_count
     FROM blogs
     INNER JOIN users ON users.id = blogs.user_id
     WHERE blogs.id = ?",
    [$currentUserId, $id]
);

if (!$blog) {
    header('Location: ' . $baseUrl . '/explore');
    exit;
}

$currentUserRole = $currentUser['role'] ?? '';
$isOwner         = $currentUserId > 0 && (int) $blog['user_id'] === $currentUserId;
$isAdmin         = $currentUserRole === 'ADMIN';

if ($blog['status'] !== 'ACTIVE') {
    if (!$isOwner && !$isAdmin) {
        header('Location: ' . $baseUrl . '/explore');
        exit;
    }
}

if ($blog['visibility'] === 'PRIVATE' || $blog['status'] === 'RESTRICTED') {
    if (!$isOwner && !$isAdmin) {
        header('Location: ' . $baseUrl . '/explore');
        exit;
    }
}

$uploads = upload_get_by_blog_id((int) $id);

$likeCount    = (int) ($blog['like_count']    ?? 0);
$isLiked      = (int) ($blog['is_liked']      ?? 0) > 0;
$shareCount   = (int) ($blog['share_count']   ?? 0);
$commentCount = (int) ($blog['comment_count'] ?? 0);

$pageTitle = htmlspecialchars($blog['title']) . ' | BlogSphere';

ob_start();

?>

<div class="details-wrap">

    <!-- Back link -->
    <a href="<?php echo $baseUrl; ?>/explore" class="details-back">
        <i class="bi bi-arrow-left"></i> Back to Explore
    </a>

    <!-- Article card -->
    <article class="details-card">

        <!-- Header -->
        <header class="details-header">

            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="blog-badge"><?php echo htmlspecialchars($blog['category']); ?></span>
                <span class="details-meta">@<?php echo htmlspecialchars($blog['username']); ?></span>
            </div>

            <h1 class="details-title"><?php echo htmlspecialchars($blog['title']); ?></h1>

            <div class="details-byline">
                <i class="bi bi-person"></i> <?php echo htmlspecialchars($blog['name']); ?>
                &nbsp;·&nbsp;
                <i class="bi bi-calendar3"></i> <?php echo date('M d, Y', strtotime($blog['created_at'])); ?>
                <?php if (!empty($blog['hashtags'])) { ?>
                    &nbsp;·&nbsp;
                    <span style="color:#999;"><?php echo htmlspecialchars($blog['hashtags']); ?></span>
                <?php } ?>
            </div>

        </header>

        <!-- Media uploads -->
        <?php if (!empty($uploads)) { ?>

            <div class="details-media-grid">
                <?php foreach ($uploads as $upload) { ?>

                    <?php if ($upload['type'] === 'IMAGE') { ?>
                        <div class="details-media-item">
                            <img src="<?php echo htmlspecialchars($upload['url']); ?>" alt="Blog media">
                        </div>
                    <?php } ?>

                    <?php if ($upload['type'] === 'VIDEO') { ?>
                        <div class="details-media-item">
                            <video controls>
                                <source src="<?php echo htmlspecialchars($upload['url']); ?>">
                            </video>
                        </div>
                    <?php } ?>

                <?php } ?>
            </div>

        <?php } ?>

        <!-- Description / body -->
        <div class="details-body">
            <?php echo nl2br(htmlspecialchars($blog['description'])); ?>
        </div>

        <!-- Actions -->
        <div class="details-actions">

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
                <span class="action-label">Like</span>
            </button>

            <button
                class="action-btn"
                data-action="comment"
                data-requires-login="1"
                data-blog-id="<?php echo (int) $blog['id']; ?>"
                data-title="<?php echo htmlspecialchars($blog['title']); ?>"
            >
                <i class="bi bi-chat"></i>
                <span id="detailsCommentCount"><?php echo $commentCount; ?></span>
                <span class="action-label">Comment</span>
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
                <span class="action-label">Share</span>
            </button>

            <?php if ($isOwner) { ?>
                <a
                    href="<?php echo $baseUrl; ?>/blogs/edit?id=<?php echo (int) $blog['id']; ?>"
                    class="action-btn ms-auto"
                >
                    <i class="bi bi-pencil"></i>
                    <span class="action-label">Edit</span>
                </a>
            <?php } ?>

        </div>

    </article>

</div>

<?php require __DIR__ . '/../../components/Comment.php'; ?>

<?php

$content = ob_get_clean();

$baseUrlJs   = json_encode($baseUrl);
$blogIdJs    = json_encode((int) $blog['id']);
$blogTitleJs = json_encode($blog['title']);

$pageScripts = ($pageScripts ?? '') . '
<script>
    var baseUrl = ' . $baseUrlJs . ';
    var blogId = ' . $blogIdJs . ';
    var blogTitle = ' . $blogTitleJs . ';

$(document).ready(function () {
    var isLoggedIn = $("body").attr("data-is-logged-in") === "1";

    // Track visit in localStorage
    (function () {
        const MAX_HISTORY = 10;
        let history = [];

        try {
            history = JSON.parse(localStorage.getItem("blogVisitHistory") || "[]");
        } catch (e) {}

        history = history.filter(item => item.id !== blogId);

        const now = new Date();
        const visitedAt = now.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });

        history.unshift({ id: blogId, title: blogTitle, visitedAt: visitedAt });

        if (history.length > MAX_HISTORY) {
            history = history.slice(0, MAX_HISTORY);
        }

        try { 
            localStorage.setItem("blogVisitHistory", JSON.stringify(history)); 
        } catch (e) {}
    }());

    // Action buttons (like / comment / share)
    $(document).on("click", "[data-action]", function () {
        const $btn = $(this);
        const action = $btn.data("action");
        const clickedBlogId = $btn.data("blog-id");
        const title = $btn.data("title") || "";

        if ($btn.data("requires-login") && !isLoggedIn) {
            if (window.BlogSphere && window.BlogSphere.showGuestModal) {
                window.BlogSphere.showGuestModal();
            }
            return;
        }

        if (action === "like") {
            const liked = $btn.data("liked") == "1";
            const newLiked = !liked;
            const count = parseInt($btn.data("like-count"), 10) || 0;
            const newCount = newLiked ? count + 1 : Math.max(0, count - 1);

            $btn.toggleClass("liked", newLiked);
            $btn.find("i").toggleClass("bi-heart-fill", newLiked).toggleClass("bi-heart", !newLiked);
            $btn.find(".like-count").text(newCount);
            $btn.data({ liked: newLiked ? "1" : "0", "like-count": newCount });

            $.post(baseUrl + "/api/blogs/like", { blogId: clickedBlogId });
        }

        if (action === "comment") {
            if (window.BlogSphere && window.BlogSphere.openCommentModal) {
                window.BlogSphere.openCommentModal(clickedBlogId, title);
            }
        }

        if (action === "share") {
            const shareUrl = $btn.data("share-url");
            const sc = parseInt($btn.data("share-count"), 10) || 0;

            $btn.find(".share-count").text(sc + 1);
            $btn.data("share-count", sc + 1);

            $.post(baseUrl + "/api/blogs/share", { blogId: clickedBlogId });

            if (navigator.share) {
                navigator.share({ title: title, url: shareUrl }).catch(function () {});
            } else {
                navigator.clipboard.writeText(shareUrl).then(function () {
                    const $label = $btn.find(".action-label");
                    const orig = $label.text();
                    $label.text("Copied!");
                    setTimeout(function () { $label.text(orig); }, 1800);
                });
            }
        }
    });

    // Comment count callback (from Comment.php modal)
    window.updateBlogCommentCount = function (id, count) {
        $("#detailsCommentCount").text(parseInt(count, 10) || 0);
    };
});
</script>
';

require __DIR__ . '/../../layouts/MainLayout.php';

?>