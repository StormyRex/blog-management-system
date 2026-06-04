<?php
$pageTitle = 'Contact Us | BlogSphere';
$activePage = 'contact';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
ob_start();
?>
<div class="hero-section">
    <div class="container">
        <span class="hero-pill">Contact BlogSphere</span>
        <h1 class="hero-title">Get in touch</h1>
        <p class="hero-sub mx-auto" style="max-width: 600px;">
            Have questions, suggestions, or feedback for BlogSphere? Send a message below.
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="alert d-none mb-3" id="contactAlert" role="alert"></div>
            <div class="form-card">
                <form id="contactForm">
                    <div class="mb-3">
                        <label class="form-label" for="contactName">Name</label>
                        <input type="text" class="form-control" id="contactName" placeholder="Your name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="contactEmail">Email</label>
                        <input type="email" class="form-control" id="contactEmail" placeholder="you@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="contactSubject">Subject</label>
                        <input type="text" class="form-control" id="contactSubject" placeholder="Message subject" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="contactMessage">Message</label>
                        <textarea class="form-control" id="contactMessage" rows="5" placeholder="Write your message..." required></textarea>
                    </div>
                    <button class="btn-solid w-100 justify-content-center py-2" type="submit" id="contactSubmitBtn">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$scripts = '
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
const BASE_URL = "' . $baseUrl . '";

$(document).ready(function () {
    const $form = $("#contactForm");
    const $submitBtn = $("#contactSubmitBtn");
    const $alert = $("#contactAlert");

    function showAlert(type, message) {
        if (!message) {
            $alert.addClass("d-none").removeClass("alert-success alert-danger").text("");
            return;
        }
        $alert.removeClass("d-none alert-success alert-danger").addClass("alert-" + type).text(message);
        $("html, body").animate({ scrollTop: 0 }, "fast");
    }

    $form.on("submit", function (e) {
        e.preventDefault();
        
        const name = $("#contactName").val().trim();
        const email = $("#contactEmail").val().trim();
        const subject = $("#contactSubject").val().trim();
        const message = $("#contactMessage").val().trim();

        if (!name || !email || !subject || !message) {
            showAlert("danger", "Please fill in all fields.");
            return;
        }

        showAlert("", "");
        $submitBtn.prop("disabled", true).text("Sending...");

        $.ajax({
            url: BASE_URL + "/api/contact",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                name: name,
                email: email,
                subject: subject,
                message: message
            }),
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    showAlert("success", response.message || "Message sent successfully!");
                    $form[0].reset();
                } else {
                    showAlert("danger", response.message || "Failed to send message.");
                }
            },
            error: function (xhr) {
                const res = xhr.responseJSON;
                showAlert("danger", res && res.message ? res.message : "An error occurred.");
            },
            complete: function () {
                $submitBtn.prop("disabled", false).text("Send Message");
            }
        });
    });
});
</script>
';
require __DIR__ . '/../layouts/MainLayout.php';
?>