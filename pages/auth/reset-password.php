<?php

$pageTitle = 'Reset Password | BlogSphere';

$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$apiBaseUrl = defined('BASE_API_URL') ? BASE_API_URL : '';

$token = trim($_GET['token'] ?? '');

ob_start();

?>

<div class="text-center mb-4">
    <div class="section-title">Authentication</div>
    <h1 class="h4 mt-2" style="font-weight: 700; letter-spacing: -0.02em;">Reset Password</h1>
    <p class="text-muted small mb-0">Enter your new password</p>
</div>

<!-- Invalid Token Alert -->
<div
    id="invalidTokenAlert"
    class="alert alert-danger d-none"
>
    Invalid or expired reset link.
</div>

<!-- Form Alert -->
<div
    id="formAlert"
    class="alert d-none"
></div>

<!-- Reset Form -->
<form
    id="resetPasswordForm"
    method="POST"
    action="<?php echo $apiBaseUrl; ?>/auth/reset-password"
    novalidate
    class="d-none"
>

    <!-- Hidden Token -->
    <input
        type="hidden"
        id="token"
        name="token"
        value="<?php echo htmlspecialchars($token); ?>"
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
            disabled
        >

    </div>

    <!-- Password Row -->
    <div class="row">

        <!-- Password -->
        <div class="col-md-6 mb-3">

            <label
                class="form-label"
                for="password"
            >
                Password
            </label>

            <div class="input-group password-field">

                <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    placeholder="Enter password"
                >

                <button
                    type="button"
                    class="btn toggle-password"
                    data-target="#password"
                >
                    <i class="bi bi-eye"></i>
                </button>

            </div>

        </div>

        <!-- Confirm Password -->
        <div class="col-md-6 mb-3">

            <label
                class="form-label"
                for="confirmPassword"
            >
                Confirm Password
            </label>

            <div class="input-group password-field">

                <input
                    type="password"
                    class="form-control"
                    id="confirmPassword"
                    name="confirmPassword"
                    placeholder="Confirm password"
                >

                <button
                    type="button"
                    class="btn toggle-password"
                    data-target="#confirmPassword"
                >
                    <i class="bi bi-eye"></i>
                </button>

            </div>

        </div>

    </div>

    <!-- Password Hint -->
    <div class="mb-3 password-hint">

        <span class="small text-muted fw-semibold">Password must contain:</span>

        <ul class="mb-0 ps-3 mt-1">

            <li>8–16 characters</li>

            <li>1 uppercase letter</li>

            <li>1 lowercase letter</li>

            <li>1 number</li>

            <li>1 special character (@ _ &)</li>

        </ul>

    </div>

    <!-- Submit -->
    <button
        type="submit"
        class="btn btn-dark w-100 py-2"
    >
        Reset Password
    </button>

</form>

<!-- Back -->
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
    | Verify Token
    |--------------------------------------------------------------------------
    */

    $.ajax({

        url:
            "<?php echo $apiBaseUrl; ?>/auth/verify-reset-token",

        type: "GET",

        data: {

            token:
                $("#token").val()

        },

        dataType: "json",

        success: function (response) {

            if (response.success) {

                $("#email").val(
                    response.data.email
                );

                $("#resetPasswordForm")
                    .removeClass("d-none");

            }

        },

        error: function () {

            $("#invalidTokenAlert")
                .removeClass("d-none");

        }

    });

    /*
    |--------------------------------------------------------------------------
    | Toggle Password
    |--------------------------------------------------------------------------
    */

    $(".toggle-password").on("click", function () {

        let target = $(this).data("target");

        let input = $(target);

        let icon = $(this).find("i");

        if (input.attr("type") === "password") {

            input.attr("type", "text");

            icon
                .removeClass("bi-eye")
                .addClass("bi-eye-slash");

        } else {

            input.attr("type", "password");

            icon
                .removeClass("bi-eye-slash")
                .addClass("bi-eye");

        }

    });

    /*
    |--------------------------------------------------------------------------
    | Custom Regex Validation
    |--------------------------------------------------------------------------
    */

    $.validator.addMethod(

        "regex",

        function (value, element, regexp) {

            let re = new RegExp(regexp);

            return this.optional(element) || re.test(value);

        },

        "Invalid format."

    );

    /*
    |--------------------------------------------------------------------------
    | Form Validation
    |--------------------------------------------------------------------------
    */

    $("#resetPasswordForm").validate({

        errorClass: "is-invalid",

        validClass: "is-valid",

        errorElement: "div",

        errorPlacement: function (error, element) {

            error.addClass("invalid-feedback");

            if (element.closest(".password-field").length) {

                error.insertAfter(
                    element.closest(".password-field")
                );

            } else {

                error.insertAfter(element);

            }

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

            password: {

                required: true,
                minlength: 8,
                maxlength: 16,

                regex:
                    "^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[@_&])[A-Za-z\\d@_&]{8,16}$"

            },

            confirmPassword: {

                required: true,
                equalTo: "#password"

            }

        },

        messages: {

            password: {

                required: "Password is required.",
                minlength: "Minimum 8 characters required.",
                maxlength: "Maximum 16 characters allowed.",

                regex:
                    "Must contain uppercase, lowercase, number and special character."

            },

            confirmPassword: {

                required: "Confirm your password.",
                equalTo: "Passwords do not match."

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

                        setTimeout(function () {

                            window.location.assign(
                                "<?php echo $baseUrl; ?>/auth/login"
                            );

                        }, 1500);

                    }

                },

                error: function (xhr) {

                    if (xhr.status === 422) {

                        let response = xhr.responseJSON;

                        if (response.errors) {

                            $.each(response.errors, function (field, message) {

                                let input = $("#" + field);

                                input.addClass("is-invalid");

                                if (
                                    input.closest(".password-field").length
                                ) {

                                    input
                                        .closest(".password-field")
                                        .after(
                                            '<div class="invalid-feedback backend-error d-block">' +
                                            message +
                                            '</div>'
                                        );

                                } else {

                                    input.after(
                                        '<div class="invalid-feedback backend-error d-block">' +
                                        message +
                                        '</div>'
                                    );

                                }

                            });

                        }

                    } else {

                        $("#formAlert")
                            .removeClass(
                                "d-none alert-success"
                            )
                            .addClass("alert-danger")
                            .text(
                                "Invalid or expired reset link."
                            );

                    }

                },

                complete: function () {

                    $("button[type='submit']")
                        .prop("disabled", false)
                        .text("Reset Password");

                }

            });

            return false;

        }

    });

});

</script>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../../layouts/AuthLayout.php'; ?>
