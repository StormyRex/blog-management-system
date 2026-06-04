<?php
$pageTitle = 'Login | BlogSphere';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';

if (!empty($_SESSION['user'])) {
    header('Location: ' . $baseUrl . '/');
    exit;
}
$apiBaseUrl = defined('BASE_API_URL') ? BASE_API_URL : '';
ob_start();
?>
<div class="text-center mb-4">	
	<div class="section-title">Authentication</div>
	<h1 class="h4 mt-2" style="font-weight: 700; letter-spacing: -0.02em;">Login</h1>
</div>

<div
    id="loginAlert"
    class="alert alert-danger d-none"
    role="alert"
></div>


<form id="loginForm" method="POST" action="<?php echo $apiBaseUrl; ?>/auth/login" novalidate>
	<div class="mb-3">
		<label class="form-label" for="identity">Email or Username</label>
		<input
			type="text"
			class="form-control"
			id="identity"
			name="identity"
			placeholder="you@example.com"
		>
	</div>
	<div class="mb-3">
		<label class="form-label" for="password">Password</label>
        <div class="input-group password-field">
			<input
				type="password"
				class="form-control"
				id="password"
				name="password"
				placeholder="Enter your password"
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
    <div class="text-end mb-3">
		<a href="<?php echo $baseUrl; ?>/auth/forgot-password" class="small text-muted text-decoration-none">
			Forgot Password?
		</a>
	</div>
	<button class="btn btn-dark w-100 py-2" type="submit">Login</button>
</form>

<div class="text-center mt-4">
	<span class="text-muted small">New here?</span>
	<a href="<?php echo $baseUrl; ?>/auth/register" class="text-primary small text-decoration-none ms-1">Create an account</a>
</div>

<div class="text-center mt-3">
    <a href="<?php echo $baseUrl; ?>/" class="text-muted small">Continue as Guest</a>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

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

    $("#loginForm").on("submit", function (e) {

        e.preventDefault();

        var form = $(this);
        var identity = $("#identity").val();
        var password = $("#password").val();
        var errors = {};

        $("#loginAlert")
            .addClass("d-none")
            .text("");

        // Clear previous errors
        $(".is-invalid").removeClass("is-invalid");
        $(".invalid-feedback").remove();

        // Simple validation
        if (!identity) {
            errors['identity'] = 'Email or username is required';
        }

        if (!password) {
            errors['password'] = 'Password is required';
        }

        if (Object.keys(errors).length > 0) {
            Object.keys(errors).forEach(function (field) {
                var input = $("#" + field);
                input.addClass("is-invalid");
                if (input.closest(".password-field").length) {
                    input.closest(".password-field").after('<div class="invalid-feedback d-block">' + errors[field] + '</div>');
                } else {
                    input.after('<div class="invalid-feedback d-block">' + errors[field] + '</div>');
                }
            });
            return;
        }

        // AJAX Request
        $.ajax({
            url: form.attr("action"),
            type: "POST",
            data: form.serialize(),
            dataType: "json",

            beforeSend: function () {
                $("button[type='submit']").prop("disabled", true).text("Processing...");
            },

            success: function (response) {
                if (response.success) {
                    window.location.assign("<?php echo $baseUrl; ?>/");
                } else if (response.message) {
                    $("#loginAlert")
                        .removeClass("d-none")
                        .text(response.message);
                }
            },

            error: function (xhr) {
                if (xhr.status === 422 || xhr.status === 401) {
                    var response = xhr.responseJSON;
                    var hasFieldErrors =
                        response &&
                        response.errors &&
                        Object.keys(response.errors).length > 0;

                    if (hasFieldErrors) {
                        Object.keys(response.errors).forEach(function (field) {
                            var input = $("#" + field);
                            input.addClass("is-invalid");
                            if (input.closest(".password-field").length) {
                                input.closest(".password-field").after('<div class="invalid-feedback d-block">' + response.errors[field] + '</div>');
                            } else {
                                input.after('<div class="invalid-feedback d-block">' + response.errors[field] + '</div>');
                            }
                        });
                    } else if (response && response.message) {
                        $("#loginAlert")
                            .removeClass("d-none")
                            .text(response.message);
                    } else {
                        $("#loginAlert")
                            .removeClass("d-none")
                            .text("Login failed. Please try again.");
                    }
                } else {
                    $("#loginAlert")
                        .removeClass("d-none")
                        .text("Something went wrong. Please try again.");
                }
            },

            complete: function () {
                $("button[type='submit']").prop("disabled", false).text("Login");
            }
        });
    });

});

</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/AuthLayout.php';
?>