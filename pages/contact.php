<?php
$pageTitle = 'Contact Us | BlogSphere';
$activePage = 'contact';
ob_start();
?>
<section class="hero-card p-4 p-lg-5 mb-4 text-center">
    <div class="section-title mb-2">Contact BlogSphere</div>
    <h1 class="fw-bold mb-2">Get in touch</h1>
    <p class="text-muted mb-0">
        Have questions, suggestions, or feedback for BlogSphere? Send a message below.
    </p>
</section>

<section class="mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label" for="contactName">Name</label>
                            <input type="text" class="form-control" id="contactName" placeholder="Your name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="contactEmail">Email</label>
                            <input type="email" class="form-control" id="contactEmail" placeholder="you@example.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="contactMessage">Message</label>
                            <textarea class="form-control" id="contactMessage" rows="5" placeholder="Write your message..."></textarea>
                        </div>
                        <button class="btn btn-primary" type="button">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/MainLayout.php';
?>