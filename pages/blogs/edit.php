<?php

require_once __DIR__ . '/../../api/Helpers/DatabaseHelper.php';
require_once __DIR__ . '/../../api/Helpers/PermissionHelper.php';
require_once __DIR__ . '/../../api/Helpers/UploadHelper.php';

$pageTitle = 'Edit Blog | BlogSphere';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user'])) {

    header('Location: ' . $baseUrl . '/auth/login');
    exit;
}

$userId = $_SESSION['user']['id'];
$canUpdateBlog = current_user_has_permission('BLOG_UPDATE');

if (!$canUpdateBlog) {
    http_response_code(403);
}

$id = $_GET['id'] ?? null;

if (empty($id)) {

    header('Location: ' . $baseUrl . '/blogs/my-blogs');
    exit;
}

$blog = fetchOne(
    "
        SELECT *
        FROM blogs
        WHERE id = ?
        AND user_id = ?
    ",
    [$id, $userId]
);

if (!$blog) {

    header('Location: ' . $baseUrl . '/blogs/my-blogs');
    exit;
}

$uploads = upload_get_by_blog_id(
    (int) $id
);

ob_start();

?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-8">

            <?php if (!$canUpdateBlog) { ?>

                <div class="alert alert-warning border-0 shadow-sm">
                    You do not have permission to edit blogs.
                </div>

            <?php } else { ?>

            <div class="form-card">

                <div class="mb-4">
                    <a
                        href="<?php echo $baseUrl; ?>/blogs/my-blogs"
                        class="btn-edit mb-4"
                    >
                        <i class="bi bi-arrow-left me-1"></i>
                        Back to My Blogs
                    </a>
                    
                    <h1 class="hero-title text-start mb-2" style="font-size: 2.2rem; font-weight: 800; letter-spacing: -0.04em;">
                        Edit Blog
                    </h1>

                    <p class="text-muted mb-0">
                        Update your blog details
                    </p>

                </div>

                <form id="editBlogForm">

                    <input
                        type="hidden"
                        name="id"
                        value="<?php echo $blog['id']; ?>"
                    >

                    <div class="mb-4">

                        <label class="form-label">
                            Title
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="title"
                            value="<?php echo htmlspecialchars($blog['title']); ?>"
                        >

                    </div>

                    <div class="mb-4">

                        <label class="form-label">
                            Description
                        </label>

                        <textarea
                            class="form-control"
                            rows="6"
                            name="description"
                        ><?php echo htmlspecialchars($blog['description']); ?></textarea>

                    </div>

                    <div class="mb-4">

                        <label class="form-label">
                            Category
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="category"
                            value="<?php echo htmlspecialchars($blog['category']); ?>"
                        >

                    </div>

                    <div class="mb-4">

                        <label class="form-label">
                            Hashtags
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="hashtags"
                            value="<?php echo htmlspecialchars($blog['hashtags']); ?>"
                        >

                    </div>

                    <div class="mb-4">

                        <label class="form-label">
                            Visibility
                        </label>

                        <select
                            class="form-select"
                            name="visibility"
                        >

                            <option
                                value="PUBLIC"
                                <?php echo $blog['visibility'] === 'PUBLIC' ? 'selected' : ''; ?>
                            >
                                PUBLIC
                            </option>

                            <option
                                value="PRIVATE"
                                <?php echo $blog['visibility'] === 'PRIVATE' ? 'selected' : ''; ?>
                            >
                                PRIVATE
                            </option>

                        </select>

                    </div>

                    <div class="mb-4">

                        <label class="form-label">
                            Media (read-only)
                        </label>

                        <p class="text-muted small mb-3">
                            Media cannot be changed after upload.
                        </p>

                        <?php if (empty($uploads)) { ?>

                            <div class="alert alert-light border">
                                No media uploaded.
                            </div>

                        <?php } else { ?>

                            <div class="row g-3">

                                <?php foreach ($uploads as $upload) { ?>

                                    <div class="col-md-6">

                                        <?php if ($upload['type'] === 'IMAGE') { ?>

                                            <img
                                                src="<?php echo htmlspecialchars($upload['url']); ?>"
                                                class="img-fluid rounded shadow-sm"
                                                alt="Blog media"
                                            >

                                        <?php } ?>

                                        <?php if ($upload['type'] === 'VIDEO') { ?>

                                            <video
                                                controls
                                                class="w-100 rounded shadow-sm"
                                            >
                                                <source
                                                    src="<?php echo htmlspecialchars($upload['url']); ?>"
                                                >
                                            </video>

                                        <?php } ?>

                                    </div>

                                <?php } ?>

                            </div>

                        <?php } ?>

                    </div>

                    <button
                        type="submit"
                        class="btn-solid py-2 px-4 mt-2"
                    >
                        Update Blog
                    </button>

                </form>

            </div>

            <?php } ?>

        </div>

    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>

$(document).ready(function () {

    $('#editBlogForm').on('submit', function (e) {

        e.preventDefault();

        $('.form-control').removeClass('is-invalid');

        $('.invalid-feedback').remove();

        let form = $(this);

        $.ajax({

            url: '<?php echo $baseUrl; ?>/api/blogs/update',

            type: 'POST',

            data: form.serialize(),

            dataType: 'json',

            success: function (response) {

                if (response.success) {

                    window.location.href =
                        response.redirect;

                }

            },

            error: function (xhr) {

                let response = xhr.responseJSON;

                let hasFieldErrors =
                    response &&
                    response.errors &&
                    Object.keys(response.errors).length > 0;

                if (hasFieldErrors) {

                    $.each(response.errors, function (field, message) {

                        let input = $('[name="' + field + '"]');

                        input.addClass('is-invalid');

                        input.after(
                            '<div class="invalid-feedback">' +
                            message +
                            '</div>'
                        );

                    });

                } else {

                    showAlert(
                        'danger',
                        response && response.message
                            ? response.message
                            : 'Something went wrong'
                    );

                }

            }

        });

    });

    function showAlert(type, message) {

        $('.custom-alert').remove();

        let alert = `
            <div class="alert alert-${type} custom-alert mt-3">
                ${message}
            </div>
        `;

        $('#editBlogForm').prepend(alert);

    }

});

</script>

<?php

$content = ob_get_clean();

require __DIR__ . '/../../layouts/MainLayout.php';