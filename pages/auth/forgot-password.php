<?php

$pageTitle = 'Forgot Password | BlogSphere';

$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$apiBaseUrl = defined('BASE_API_URL') ? BASE_API_URL : '';

ob_start();

?>

<div class="text-center mb-4">
    <div class="section-title">Authentication</div>
    <h1 class="h4 mt-2" style="font-weight: 700; letter-spacing: -0.02em;">Forgot Password</h1>
    <p class="text-muted small mb-0">Enter your email to receive a password reset link</p>
</div>

<!-- Alert -->
<div
    id="formAlert"
    class="alert d-none"
></div>

<form
    id="forgotPasswordForm"
    method="POST"
    action="<?php echo $apiBaseUrl; ?>/auth/forgot-password"
    novalidate
>

    <!-- Email -->
    <div class="mb-3">

        <label
            class="form-label"
            for="email"
        >
            Email
        </label>

        <input
            type="email"
            class="form-control"
            id="email"
            name="email"
            placeholder="Enter your email"
        >

    </div>

    <!-- Submit -->
    <button
        type="submit"
        class="btn btn-dark w-100 py-2"
    >
        Send Reset Link
    </button>

</form>

<!-- Login -->
<div class="text-center mt-4">

    <a
        class="text-primary small text-decoration-none"
        href="<?php echo $baseUrl; ?>/auth/login"
    >
        Back to Login
    </a>

</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- jQuery Validation -->
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

<script>

$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | Form Validation
    |--------------------------------------------------------------------------
    */

    $("#forgotPasswordForm").validate({

        errorClass: "is-invalid",

        validClass: "is-valid",

        errorElement: "div",

        errorPlacement: function (error, element) {

            error
                .addClass("invalid-feedback");

            error.insertAfter(element);

        },

        highlight: function (element) {

            $(element)
                .addClass("is-invalid")
                .removeClass("is-valid");

        },

        unhighlight: function (element) {

            $(element)
                .removeClass("is-invalid")
                .addClass("is-valid");

        },

        rules: {

            email: {

                required: true,
                email: true,
                maxlength: 100

            }

        },

        messages: {

            email: {

                required: "Email is required.",
                email: "Enter valid email address.",
                maxlength: "Maximum 100 characters allowed."

            }

        },

        submitHandler: function (form) {

            /*
            |--------------------------------------------------------------------------
            | Clear Backend Errors
            |--------------------------------------------------------------------------
            */

            $(".backend-error").remove();

            $(".form-control").removeClass("is-invalid");

            $("#formAlert")
                .addClass("d-none")
                .removeClass("alert-success alert-danger")
                .text("");

            /*
            |--------------------------------------------------------------------------
            | AJAX Request
            |--------------------------------------------------------------------------
            */

            $.ajax({

                url: $(form).attr("action"),

                type: "POST",

                data: $(form).serialize(),

                dataType: "json",

                beforeSend: function () {

                    $("button[type='submit']")
                        .prop("disabled", true)
                        .text("Processing...");

                },

                success: function (response) {

                    if (response.success) {

                        $("#formAlert")
                            .removeClass(
                                "d-none alert-danger"
                            )
                            .addClass("alert-success")
                            .text(response.message);

                        $("#forgotPasswordForm")[0].reset();

                        $(".form-control")
                            .removeClass("is-valid");

                    }

                    else if (response.message) {

                        $("#formAlert")
                            .removeClass(
                                "d-none alert-success"
                            )
                            .addClass("alert-danger")
                            .text(response.message);

                    }

                },

                error: function (xhr) {

                    if (xhr.status === 422) {

                        let response = xhr.responseJSON;

                        if (response.errors) {

                            $.each(response.errors, function (field, message) {

                                let input = $("#" + field);

                                input.addClass("is-invalid");

                                input.after(
                                    '<div class="invalid-feedback backend-error d-block">' +
                                    message +
                                    '</div>'
                                );

                            });

                        }

                    } else {

                        $("#formAlert")
                            .removeClass(
                                "d-none alert-success"
                            )
                            .addClass("alert-danger")
                            .text(
                                "Something went wrong. Please try again."
                            );

                    }

                },

                complete: function () {

                    $("button[type='submit']")
                        .prop("disabled", false)
                        .text("Send Reset Link");

                }

            });

            return false;

        }

    });

});

</script>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../../layouts/AuthLayout.php'; ?>
