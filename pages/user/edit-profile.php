<?php

$pageTitle = 'Edit Profile | BlogSphere';
$activePage = 'profile';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$apiBaseUrl = defined('BASE_API_URL') ? BASE_API_URL : '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user'])) {
    header('Location: ' . $baseUrl . '/auth/login');
    exit;
}

ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-card">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start gap-4 mb-4">
                    <div>
                        <a href="<?php echo $baseUrl; ?>/user/profile" class="btn-outline mb-3 d-inline-flex align-items-center py-1.5 px-3" style="font-size: 0.8rem; border-radius: 8px;">
                            <i class="bi bi-arrow-left me-1"></i> Back to Profile
                        </a>
                        <h1 class="hero-title text-start mb-1" style="font-size: 2.2rem; font-weight: 800; letter-spacing: -0.04em;">Edit Profile</h1>
                        <p class="text-muted mb-0" style="font-size: 0.95rem;">Update your account details and avatar image.</p>
                    </div>
                    <div class="text-center align-self-sm-center">
                        <img
                            id="avatarPreview"
                            src=""
                            alt="Avatar preview"
                            class="rounded-circle border shadow-sm d-none"
                            style="width: 88px; height: 88px; object-fit: cover;"
                        >
                    </div>
                </div>

                <hr class="my-4" style="border-top: 1px solid #e8e8e8; opacity: 1;">

                <div class="alert alert-danger d-none mb-4" id="editProfileAlert" role="alert"></div>

                <form id="editProfileForm" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="_method" value="PUT">

                    <div class="mb-4">
                        <label class="form-label">Avatar Image</label>
                        <input
                            type="file"
                            class="form-control"
                            name="avatar"
                            id="avatarInput"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        >
                        <div class="form-text" style="font-size: 0.78rem; color: #999;">Allowed formats: jpg, jpeg, png, webp</div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="Enter your full name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" id="username" placeholder="Choose a username">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="email" placeholder="Enter your email">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" id="phone" placeholder="Enter phone number">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender" id="gender">
                                <option value="">Select gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Birth Date</label>
                            <input type="date" class="form-control" name="birthDate" id="birthDate">
                        </div>

                        <div class="col-12">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" id="city" placeholder="Enter your city">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Bio</label>
                            <textarea class="form-control" name="bio" id="bio" rows="4" placeholder="Write a short bio about yourself..."></textarea>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn-solid py-2 px-4" id="saveProfileBtn">Save Changes</button>
                        <a href="<?php echo $baseUrl; ?>/user/profile" class="btn-outline py-2 px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php

$apiBaseUrlJs = json_encode($apiBaseUrl);
$baseUrlJs = json_encode($baseUrl);

$pageScripts = '
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function () {
    const apiBaseUrl = ' . $apiBaseUrlJs . ';
    const baseUrl = ' . $baseUrlJs . ';

    function showAlert(message) {
        $("#editProfileAlert").removeClass("d-none").text(message);
    }

    function hideAlert() {
        $("#editProfileAlert").addClass("d-none").text("");
    }

    function clearValidation() {
        $(".form-control, .form-select").removeClass("is-invalid");
        $(".invalid-feedback").remove();
    }

    function renderAvatarPreview(url) {
        if (url) {
            $("#avatarPreview").attr("src", url).removeClass("d-none");
        } else {
            $("#avatarPreview").attr("src", "").addClass("d-none");
        }
    }

    function fillForm(profile) {
        $("#name").val(profile.name || "");
        $("#username").val(profile.username || "");
        $("#email").val(profile.email || "");
        $("#phone").val(profile.phone || "");
        $("#gender").val(profile.gender || "");
        $("#birthDate").val(profile.birthDate || "");
        $("#city").val(profile.city || "");
        $("#bio").val(profile.bio || "");
        renderAvatarPreview(profile.avatar || "");
    }

    function loadProfile() {
        $.ajax({
            url: apiBaseUrl + "/user/profile",
            type: "GET",
            dataType: "json"
        })
        .done(function (response) {
            if (response && response.success && response.data && response.data.profile) {
                fillForm(response.data.profile);
                hideAlert();
                return;
            }
            showAlert(response && response.message ? response.message : "Unable to load profile.");
        })
        .fail(function (xhr) {
            var response = xhr && xhr.responseJSON ? xhr.responseJSON : null;
            showAlert(response && response.message ? response.message : "Unable to load profile.");
        });
    }

    $("#avatarInput").on("change", function () {
        var file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) {
            renderAvatarPreview($("#avatarPreview").attr("src"));
            return;
        }

        var reader = new FileReader();
        reader.onload = function (event) {
            renderAvatarPreview(event.target.result);
        };
        reader.readAsDataURL(file);
    });

    $("#editProfileForm").on("submit", function (event) {
        event.preventDefault();
        clearValidation();
        hideAlert();

        var formData = new FormData(this);
        $("#saveProfileBtn").prop("disabled", true).text("Saving...");

        $.ajax({
            url: apiBaseUrl + "/user/update-profile",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json"
        })
        .done(function (response) {
            if (response && response.success) {
                window.location.href = baseUrl + "/user/profile";
                return;
            }
            showAlert(response && response.message ? response.message : "Unable to update profile.");
        })
        .fail(function (xhr) {
            var response = xhr && xhr.responseJSON ? xhr.responseJSON : null;
            var hasFieldErrors = response && response.errors && Object.keys(response.errors).length > 0;

            if (hasFieldErrors) {
                Object.keys(response.errors).forEach(function (field) {
                    var message = response.errors[field];
                    var input = $("[name=\'" + field + "\']");
                    input.addClass("is-invalid");
                    input.after("<div class=\'invalid-feedback\'>" + message + "</div>");
                });
            } else {
                showAlert(response && response.message ? response.message : "Unable to update profile.");
            }
        })
        .always(function () {
            $("#saveProfileBtn").prop("disabled", false).text("Save Changes");
        });
    });

    loadProfile();
});
</script>
';

?>

<?php

$content = ob_get_clean();

require __DIR__ . '/../../layouts/MainLayout.php';

?>