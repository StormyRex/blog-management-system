<?php

require_once __DIR__ . '/../../api/Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../../api/Helpers/PermissionHelper.php';

$pageTitle = 'My Blogs | BlogSphere';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user'])) {

    header('Location: ' . $baseUrl . '/auth/login');
    exit;
}

$userId = $_SESSION['user']['id'];
$canCreateBlog = current_user_has_permission('BLOG_CREATE');
$canUpdateBlog = current_user_has_permission('BLOG_UPDATE');
$canDeleteBlog = current_user_has_permission('BLOG_DELETE');

$blogs = fetchAll(
    "
        SELECT *
        FROM blogs
        WHERE user_id = ?
        ORDER BY id DESC
    ",
    [$userId]
);

$blogIds = array_column($blogs, 'id');
$thumbnails = getBlogThumbnailMap($blogIds);
foreach ($blogs as &$blog) {
    $blog['thumbnail'] = $thumbnails[$blog['id']] ?? null;
}
unset($blog);

ob_start();

?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-5 mt-3">
        <div>
            <span class="hero-pill mb-2">Dashboard</span>
            <h1 class="hero-title text-start mb-0" style="font-size: 2.2rem; font-weight: 800; letter-spacing: -0.04em;">My Blogs</h1>
        </div>
        <?php if ($canCreateBlog) { ?>
            <a
                href="<?php echo $baseUrl; ?>/blogs/create"
                class="btn-solid py-2 px-4"
            >
                <i class="bi bi-plus-lg me-1"></i> Create Blog
            </a>
        <?php } ?>
    </div>

    <div class="alert d-none mb-4" id="deleteAlert" role="alert"></div>

    <div class="row g-4">

        <?php if (empty($blogs)) { ?>
            <div class="col-12">
                <div class="text-center py-5 my-4">
                    <i class="bi bi-journal-text text-muted" style="font-size: 3.5rem; opacity: 0.3;"></i>
                    <h3 class="fw-bold mt-3 mb-2" style="font-size: 1.35rem;">No Blogs Yet</h3>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 380px; font-size: 0.9rem;">
                        Start writing and share your ideas, insights, and stories with the world.
                    </p>
                    <?php if ($canCreateBlog) { ?>
                        <a
                            href="<?php echo $baseUrl; ?>/blogs/create"
                            class="btn-solid py-2 px-4"
                        >
                            Create Your First Blog
                        </a>
                    <?php } ?>
                </div>
            </div>
        <?php } else { ?>

            <?php foreach ($blogs as $blog) { 
                $thumbnail = $blog['thumbnail'] ?? null;
            ?>

                <div
                    class="col-md-6 col-lg-4"
                    id="blog-<?php echo $blog['id']; ?>"
                >
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
                                <span class="blog-badge" style="background:#f4f4f4; border:1px solid #e0e0e0; color:#555;"><?php echo htmlspecialchars($blog['visibility']); ?></span>
                            </div>

                            <a href="<?php echo $baseUrl; ?>/blogs/details?id=<?php echo (int) $blog['id']; ?>" class="blog-card-title mb-2" style="text-decoration:none; color:inherit;">
                                <?php echo htmlspecialchars($blog['title']); ?>
                            </a>

                            <div class="blog-card-desc mb-3 flex-grow-1">
                                <?php echo htmlspecialchars(substr($blog['description'], 0, 120)); ?>…
                            </div>

                            <div style="font-size:.76rem; color:#999;">
                                <i class="bi bi-calendar3"></i> <?php echo date('M d, Y', strtotime($blog['created_at'])); ?>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="px-3 py-2.5 d-flex gap-2" style="border-top:1px solid #f0f0f0;">
                            <?php if ($canUpdateBlog) { ?>
                                <a
                                    href="<?php echo $baseUrl; ?>/blogs/edit?id=<?php echo $blog['id']; ?>"
                                    class="btn-edit"
                                    onclick="event.stopPropagation();"
                                >
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                            <?php } ?>

                            <?php if ($canDeleteBlog) { ?>
                                <button
                                    class="btn-delete delete-blog-btn"
                                    data-id="<?php echo $blog['id']; ?>"
                                    onclick="event.stopPropagation();"
                                >
                                    <i class="bi bi-trash me-1"></i> Delete
                                </button>
                            <?php } ?>
                        </div>
                    </div>
                </div>

            <?php } ?>

        <?php } ?>

    </div>

</div>

<div
    class="modal fade"
    id="deleteBlogModal"
    tabindex="-1"
    aria-labelledby="deleteBlogModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="deleteBlogModalLabel" style="font-size: 1.15rem; letter-spacing: -0.02em;">
                    Delete Blog
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>
            <div class="modal-body py-3 text-muted" style="font-size: 0.95rem;">
                Are you sure you want to delete this blog? This action cannot be undone.
            </div>
            <div class="modal-footer border-0 pt-0">
                <button
                    type="button"
                    class="btn-edit"
                    data-bs-dismiss="modal"
                    style="padding: 8px 16px;"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    class="btn btn-danger"
                    id="confirmDeleteBtn"
                    style="border-radius: 8px; font-weight: 600; padding: 8px 16px; font-size: 0.85rem;"
                >
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>

$(document).ready(function () {

    var $alert = $('#deleteAlert');
    var $modal = $('#deleteBlogModal');
    var modalInstance = new bootstrap.Modal($modal[0]);
    var $confirmBtn = $('#confirmDeleteBtn');
    var selectedBlogId = null;

    function showAlert(type, message) {
        if (!message) {
            $alert
                .addClass('d-none')
                .removeClass('alert-success alert-danger')
                .text('');
            return;
        }

        $alert
            .removeClass('d-none alert-success alert-danger')
            .addClass('alert-' + type)
            .text(message);
    }

    $('.delete-blog-btn').on('click', function () {
        selectedBlogId = $(this).data('id');
        showAlert('', '');
        modalInstance.show();
    });

    $confirmBtn.on('click', function () {

        if (!selectedBlogId) {
            return;
        }

        $confirmBtn.prop('disabled', true);

        $.ajax({

            url: '<?php echo $baseUrl; ?>/api/blogs/delete',

            type: 'POST',

            data: {
                id: selectedBlogId
            },

            dataType: 'json',

            success: function (response) {

                if (response.success) {

                    $('#blog-' + selectedBlogId).fadeOut(300, function () {

                        $(this).remove();

                    });

                    showAlert('success', response.message);

                } else {

                    showAlert('danger', response.message || 'Something went wrong');

                }
            },

            error: function (xhr) {

                var response = xhr.responseJSON;

                showAlert(
                    'danger',
                    response && response.message
                        ? response.message
                        : 'Something went wrong'
                );

            },

            complete: function () {
                modalInstance.hide();
                $confirmBtn.prop('disabled', false);
                selectedBlogId = null;
            }

        });

    });

});

</script>

<?php

$content = ob_get_clean();

require __DIR__ . '/../../layouts/MainLayout.php';