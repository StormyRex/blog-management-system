<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['pending_admin_id'])) {

    header(
        'Location: ' . $baseUrl . '/auth/admin-login'
    );

    exit;
}

$pageTitle = 'OTP Verification | BlogSphere';

$apiBaseUrl = defined('BASE_API_URL') ? BASE_API_URL : '';

ob_start();

?>

<div class="text-center mb-4">
    <div class="section-title">Authentication</div>
    <h1 class="h4 mt-2" style="font-weight: 700; letter-spacing: -0.02em;">OTP Verification</h1>
    <p class="text-muted small mb-0">Enter the OTP sent to your email.</p>
</div>

<div
    id="alertContainer"
    class="alert alert-danger d-none"
    role="alert"
></div>

<form
    id="otpVerificationForm"
    method="POST"
    action="<?php echo $apiBaseUrl; ?>/auth/verify-otp"
    novalidate
>

    <div class="mb-3">

        <label
            class="form-label"
            for="otp"
        >
            OTP Code
        </label>

        <input
            type="text"
            name="otp"
            id="otp"
            class="form-control"
            maxlength="6"
            placeholder="Enter 6-digit OTP"
        >

        <div
            class="invalid-feedback"
            data-error="otp"
        ></div>

    </div>

    <button
        type="submit"
        class="btn btn-dark w-100 py-2"
        id="verifyOtpButton"
    >
        Verify OTP
    </button>

</form>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>

$(document).ready(function () {

    $('#otpVerificationForm').on('submit', function (e) {

        e.preventDefault();

        $('.is-invalid').removeClass('is-invalid');

        $('.invalid-feedback').text('');

        $('#alertContainer')
            .addClass('d-none')
            .text('');

        const $button = $('#verifyOtpButton');

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
                        '<?php echo $baseUrl; ?>/admin/dashboard';

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

                $button.html('Verify OTP');
            }
        });
    });

});

</script>

<?php

$content = ob_get_clean();

require_once __DIR__ . '/../../layouts/AuthLayout.php';