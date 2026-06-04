<?php
$pageTitle = 'Admin Login | BlogSphere';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$apiBaseUrl = defined('BASE_API_URL') ? BASE_API_URL : '';

ob_start();
?><div class="text-center mb-4">
    <div class="section-title">Authentication</div>
    <h1 class="h4 mt-2" style="font-weight: 700; letter-spacing: -0.02em;">Admin Login</h1>
</div>

<div
    id="alertContainer"
    class="alert alert-danger d-none"
    role="alert"
></div>

<form id="adminLoginForm" method="POST" action="<?php echo $apiBaseUrl; ?>/auth/admin-login" novalidate>

    <div class="mb-3">

        <label class="form-label" for="identity">
            Email or Username
        </label>

        <input
            type="text"
            name="identity"
            id="identity"
            class="form-control"
            placeholder="you@example.com"
        >

        <div
            class="invalid-feedback"
            data-error="identity"
        ></div>

    </div>

    <div class="mb-3">

        <label class="form-label" for="password">
            Password
        </label>

        <div class="input-group">

            <input
                type="password"
                name="password"
                id="password"
                class="form-control"
                placeholder="Enter your password"
            >

            <button
                class="btn toggle-password"
                type="button"
                data-target="#password"
                aria-label="Show password"
                title="Show password"
            >
                <i class="bi bi-eye"></i>
            </button>

        </div>

        <div
            class="invalid-feedback"
            data-error="password"
        ></div>

    </div>

    <button
        type="submit"
        class="btn btn-dark w-100 py-2"
        id="adminLoginButton"
    >
        Login
    </button>

</form>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>

$(document).ready(function () {

    $(".toggle-password").on("click", function () {

        let target = $(this).data("target");

        let input = $(target);

        let icon = $(this).find("i");

        if (input.attr("type") === "password") {

            input.attr("type", "text");

            icon
                .removeClass("bi-eye")
                .addClass("bi-eye-slash");

            $(this)
                .attr("aria-label", "Hide password")
                .attr("title", "Hide password");

        } else {

            input.attr("type", "password");

            icon
                .removeClass("bi-eye-slash")
                .addClass("bi-eye");

            $(this)
                .attr("aria-label", "Show password")
                .attr("title", "Show password");
        }
    });


    $('#adminLoginForm').on('submit', function (e) {

        e.preventDefault();

        $('.is-invalid').removeClass('is-invalid');

        $('.invalid-feedback').text('');

        $('#alertContainer')
            .addClass('d-none')
            .text('');

        const $button = $('#adminLoginButton');

        $button.prop('disabled', true);

        $button.html(`
            <span
                class="spinner-border spinner-border-sm me-2"
            ></span>
            Verifying...
        `);

        $.ajax({

            url: $(this).attr('action'),

            type: 'POST',

            data: $(this).serialize(),

            dataType: 'json',

            success: function (response) {

                if (response.success) {

                    window.location.href =
                        '<?php echo $baseUrl; ?>/auth/verification';

                    return;
                }

                if (response.errors) {

                    Object.keys(response.errors).forEach(function (key) {

                        const input =
                            $('[name="' + key + '"]');

                        input.addClass('is-invalid');

                        $('[data-error="' + key + '"]')
                            .text(response.errors[key]);
                    });
                }

                $('#alertContainer')
                    .removeClass('d-none')
                    .text(response.message);
            },

            error: function () {

                $('#alertContainer')
                    .removeClass('d-none')
                    .text('Something went wrong.');
            },

            complete: function () {

                $button.prop('disabled', false);

                $button.html('Login');
            }
        });
    });

});

</script>

<?php

$content = ob_get_clean();

require_once __DIR__ . '/../../layouts/AuthLayout.php';