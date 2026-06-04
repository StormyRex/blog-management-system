<?php

$pageTitle = 'Create Blog | BlogSphere';

$baseUrl = defined('BASE_URL')
    ? BASE_URL
    : '';

require_once __DIR__ . '/../../api/Helpers/PermissionHelper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user'])) {

    header(
        'Location: ' .
        $baseUrl .
        '/auth/login'
    );

    exit;
}

$canCreateBlog = current_user_has_permission('BLOG_CREATE');

ob_start();

if (!$canCreateBlog) {
    http_response_code(403);
}

?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-8">

            <?php if (!$canCreateBlog) { ?>

                <div class="alert alert-warning border-0 shadow-sm">
                    You do not have permission to create blogs.
                </div>

            <?php } else { ?>

            <div class="form-card">

                <h1 class="hero-title text-start mb-2" style="font-size: 2.2rem; font-weight: 800; letter-spacing: -0.04em;">
                    Create Blog
                </h1>

                <p class="text-muted mb-4">
                    Share your thoughts with the world.
                </p>

                <div
                    id="alertBox"
                    class="alert d-none mb-4"
                ></div>

                <form id="createBlogForm">

                    <div class="mb-4">

                        <label
                            class="form-label"
                            for="title"
                        >
                            Title
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="title"
                            name="title"
                            placeholder="Enter a compelling title"
                        >

                        <div
                            class="invalid-feedback"
                            data-error-for="title"
                        ></div>

                    </div>

                    <div class="mb-4">

                        <label
                            class="form-label"
                            for="description"
                        >
                            Description
                        </label>

                        <textarea
                            class="form-control"
                            id="description"
                            name="description"
                            rows="6"
                            placeholder="Write your story..."
                        ></textarea>

                        <div
                            class="invalid-feedback"
                            data-error-for="description"
                        ></div>

                    </div>

                    <div class="mb-4">

                        <label
                            class="form-label"
                            for="category"
                        >
                            Category
                        </label>

                        <select
                            class="form-select"
                            id="category"
                            name="category"
                        >

                            <option value="">
                                Select Category
                            </option>

                            <option value="Programming">
                                Programming
                            </option>

                            <option value="Technology">
                                Technology
                            </option>

                            <option value="Travel">
                                Travel
                            </option>

                            <option value="Fitness">
                                Fitness
                            </option>

                            <option value="Finance">
                                Finance
                            </option>

                        </select>

                        <div
                            class="invalid-feedback"
                            data-error-for="category"
                        ></div>

                    </div>

                    <div class="mb-4">

                        <label
                            class="form-label"
                            for="hashtags"
                        >
                            Hashtags
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="hashtags"
                            name="hashtags"
                            placeholder="#coding #php"
                        >

                    </div>

                    <div class="mb-4">

                        <label
                            class="form-label"
                            for="media"
                        >
                            Upload Media
                        </label>

                        <input
                            type="file"
                            class="form-control"
                            id="media"
                            accept="image/*,video/*"
                        >

                    </div>

                    <div class="mb-4">

                        <label
                            class="form-label"
                            for="visibility"
                        >
                            Visibility
                        </label>

                        <select
                            class="form-select"
                            id="visibility"
                            name="visibility"
                        >

                            <option value="PUBLIC">
                                Public
                            </option>

                            <option value="PRIVATE">
                                Private
                            </option>

                        </select>

                    </div>

                    <button
                        type="submit"
                        class="btn-solid py-2 px-4 mt-2"
                        id="submitBtn"
                    >
                        Create Blog
                    </button>

                </form>

            </div>

            <?php } ?>

        </div>

    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
$(function () {
    const $form = $('#createBlogForm');
    const $alert = $('#alertBox');
    const $submitBtn = $('#submitBtn');

    // Alert helpers
    function showAlert(type, message) {
        $alert.removeClass('d-none alert-success alert-danger alert-warning')
              .addClass('alert-' + type)
              .html(message);
    }

    function hideAlert() {
        $alert.addClass('d-none').html('');
    }

    // Validation helpers
    function clearErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    function clearFieldError(field) {
        $('[name="' + field + '"]').removeClass('is-invalid');
        $('[data-error-for="' + field + '"]').text('');
    }

    function setFieldError(field, message) {
        $('[name="' + field + '"]').addClass('is-invalid');
        $('[data-error-for="' + field + '"]').text(message);
    }

    function applyErrors(errors) {
        $.each(errors, function (key, value) {
            setFieldError(key, value);
        });
    }

    // Live validation
    $('#title').on('input', function () {
        const val = $(this).val().trim();
        if (!val) {
            setFieldError('title', 'Title is required');
        } else if (val.length < 5) {
            setFieldError('title', 'Title must be at least 5 characters');
        } else {
            clearFieldError('title');
        }
    });

    $('#description').on('input', function () {
        const val = $(this).val().trim();
        if (!val) {
            setFieldError('description', 'Description is required');
        } else if (val.length < 20) {
            setFieldError('description', 'Description must be at least 20 characters');
        } else {
            clearFieldError('description');
        }
    });

    $('#category').on('change', function () {
        const val = $(this).val();
        if (!val) {
            setFieldError('category', 'Category is required');
        } else {
            clearFieldError('category');
        }
    });

    // Validate entire form before submission
    function validateForm() {
        clearErrors();
        let isValid = true;
        
        const title = $('#title').val().trim();
        const description = $('#description').val().trim();
        const category = $('#category').val();

        if (!title) {
            setFieldError('title', 'Title is required');
            isValid = false;
        } else if (title.length < 5) {
            setFieldError('title', 'Title must be at least 5 characters');
            isValid = false;
        }

        if (!description) {
            setFieldError('description', 'Description is required');
            isValid = false;
        } else if (description.length < 20) {
            setFieldError('description', 'Description must be at least 20 characters');
            isValid = false;
        }

        if (!category) {
            setFieldError('category', 'Category is required');
            isValid = false;
        }

        return isValid;
    }

    // Handle form submit
    $form.on('submit', function (event) {
        event.preventDefault();
        hideAlert();
        clearErrors();

        if (!validateForm()) {
            showAlert('danger', 'Please fix the validation errors.');
            return;
        }

        $submitBtn.prop('disabled', true)
                  .html('<span class="spinner-border spinner-border-sm me-2"></span>Creating Blog...');

        // Step 1: Create the blog metadata
        $.ajax({
            url: '<?php echo $baseUrl; ?>/api/blogs/create',
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json'
        })
        .done(function (response) {
            if (!response.success) {
                if (response.errors) {
                    applyErrors(response.errors);
                }
                showAlert('danger', response.message || 'Validation failed.');
                $submitBtn.prop('disabled', false).text('Create Blog');
                return;
            }

            const blogId = response.data.id;
            const fileInput = document.getElementById('media');
            const file = fileInput.files[0];

            // If no media is uploaded, redirect immediately
            if (!file) {
                showAlert('success', 'Blog created successfully.');
                setTimeout(function () {
                    window.location.href = response.redirect;
                }, 1000);
                return;
            }

            // Step 2: Upload media if selected
            const formData = new FormData();
            formData.append('blog_id', blogId);

            if (file.type.indexOf('video/') === 0) {
                formData.append('video', file);
            } else {
                formData.append('image', file);
            }

            const originalButtonHtml = $submitBtn.html();
            $submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Uploading Media...');

            $.ajax({
                url: '<?php echo $baseUrl; ?>/api/upload',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            })
            .done(function () {
                showAlert('success', 'Blog created successfully.');
                setTimeout(function () {
                    window.location.href = response.redirect;
                }, 1000);
            })
            .fail(function (xhr) {
                const uploadResponse = xhr.responseJSON;
                showAlert('danger', uploadResponse?.message || 'Media upload failed.');
                $submitBtn.prop('disabled', false).html('Create Blog');
            });
        })
        .fail(function (xhr) {
            const response = xhr.responseJSON;
            if (response && response.errors) {
                applyErrors(response.errors);
            }
            showAlert('danger', response?.message || 'Something went wrong.');
            $submitBtn.prop('disabled', false).text('Create Blog');
        });
    });
});
</script>

<?php

$content = ob_get_clean();

require __DIR__ .
    '/../../layouts/MainLayout.php';

?>