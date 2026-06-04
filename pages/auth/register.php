<?php 
$title = 'Register | BlogSphere';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$apiBaseUrl = defined('BASE_API_URL') ? BASE_API_URL : '';
$errors = $errors ?? [];
$old = $old ?? [];

ob_start();
?><div class="text-center mb-4">
    <div class="section-title">
        Authentication
    </div>
    <h1 class="h4 mt-2" style="font-weight: 700; letter-spacing: -0.02em;">
        Register
    </h1>
    <p class="text-muted small mb-0">
        Create your BlogSphere account
    </p>
</div>

<form 
    id="registerForm"
    method="POST"
    action="<?php echo $apiBaseUrl; ?>/auth/register"
    novalidate
>

    <!-- Name + Username -->
    <div class="row">

        <!-- Name -->
        <div class="col-md-6 mb-3">

            <label class="form-label" for="name">
                Name
            </label>

            <input
                type="text"
                class="form-control"
                id="name"
                name="name"
                placeholder="Enter your full name"
                value="<?php echo htmlspecialchars($old['name'] ?? ''); ?>"
            >

        </div>

        <!-- Username -->
        <div class="col-md-6 mb-3">

            <label class="form-label" for="username">
                Username
            </label>

            <input
                type="text"
                class="form-control"
                id="username"
                name="username"
                placeholder="Enter your username"
                value="<?php echo htmlspecialchars($old['username'] ?? ''); ?>"
            >

        </div>

    </div>

    <!-- Email -->
    <div class="mb-3">

        <label class="form-label" for="email">
            Email
        </label>

        <input
            type="text"
            class="form-control"
            id="email"
            name="email"
            placeholder="Enter your email"
            value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>"
        >

    </div>

    <!-- Password Row -->
    <div class="row">

        <!-- Password -->
        <div class="col-md-6 mb-3">

            <label class="form-label" for="password">
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
                    aria-label="Show password"
                    title="Show password"
                    data-target="#password"
                >
                    <i class="bi bi-eye"></i>
                </button>

            </div>

        </div>

        <!-- Confirm Password -->
        <div class="col-md-6 mb-3">

            <label class="form-label" for="confirmPassword">
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
                    aria-label="Show password"
                    title="Show password"
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
        Register
    </button>

</form>

<!-- Login -->
<div class="text-center mt-4">

    <span class="text-muted small">
        Already have an account?
    </span>

    <a 
        class="text-primary small text-decoration-none ms-1"
        href="<?php echo $baseUrl; ?>/auth/login"
    >
        Login
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
    | Toggle Password
    |--------------------------------------------------------------------------
    */

    $(".toggle-password").on("click", function () {

        let target = $(this).data("target");

        let input = $(target);
        let icon = $(this).find("i");

        if (input.attr("type") === "password") {

            input.attr("type", "text");

            icon.removeClass("bi-eye").addClass("bi-eye-slash");
            $(this).attr("aria-label", "Hide password").attr("title", "Hide password");

        } else {

            input.attr("type", "password");

            icon.removeClass("bi-eye-slash").addClass("bi-eye");
            $(this).attr("aria-label", "Show password").attr("title", "Show password");

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

    $("#registerForm").validate({

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

            name: {

                required: true,
                minlength: 3,
                maxlength: 50,
                regex: "^[A-Za-z ]+$"

            },

            username: {

                required: true,
                minlength: 4,
                maxlength: 30,
                regex: "^[a-zA-Z0-9_]+$"

            },

            email: {

                required: true,
                email: true,
                maxlength: 100

            },

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

            name: {

                required: "Name is required.",
                minlength: "Minimum 3 characters required.",
                maxlength: "Maximum 50 characters allowed.",
                regex: "Only alphabets and spaces allowed."

            },

            username: {

                required: "Username is required.",
                minlength: "Minimum 4 characters required.",
                maxlength: "Maximum 30 characters allowed.",
                regex: "Only letters, numbers and underscore allowed."

            },

            email: {

                required: "Email is required.",
                email: "Enter valid email address.",
                maxlength: "Maximum 100 characters allowed."

            },

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

                        window.location.assign(
                            "<?php echo $baseUrl; ?>/auth/login"
                        );

                    }

                    else if (response.message) {

                        alert(response.message);

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
                                    input.closest(".position-relative").length
                                ) {

                                    input
                                        .closest(".position-relative")
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

                        alert(
                            "Something went wrong. Please try again."
                        );

                    }

                },

                complete: function () {

                    $("button[type='submit']")
                        .prop("disabled", false)
                        .text("Register");

                }

            });

            return false;

        }

    });

});

</script>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../../layouts/AuthLayout.php'; ?>