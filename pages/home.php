<?php
$pageTitle = 'Home | BlogSphere';
$activePage = 'home';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
ob_start();
?>
<section class="hero-card p-4 p-lg-5 mb-4 text-center">
	<div class="section-title mb-2">Write. Share. Inspire.</div>
	<h1 class="fw-bold mb-2">Welcome to BlogSphere</h1>
	<p class="text-muted mb-4">
		Write, share and explore blogs from creators around the world in this educational PHP MVC project.
	</p>
	<div class="row justify-content-center">
		<div class="col-lg-8">
			<div class="input-group">
				<input
					type="text"
					class="form-control"
					placeholder="Search here..."
				>
				<button class="btn btn-primary" type="button">Search</button>
			</div>
		</div>
	</div>
</section>

<section class="mb-5">
	<div class="row">
		<div class="col-lg-8">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<h2 class="h5 mb-0">Latest Blogs</h2>
				<a href="<?php echo $baseUrl; ?>/explore" class="text-decoration-none">Explore more</a>
			</div>

			<div class="card mb-3">
				<div class="card-body">
					<div class="card-image-placeholder mb-3"></div>
					<h5 class="card-title">How to Start Writing Consistently</h5>
					<p class="card-text text-muted">
						Simple routines and goals that help creators publish regularly without burnout.
					</p>
					<div class="meta-text mb-3">Creator: Shive · May 13, 2026</div>
					<div class="d-flex flex-wrap gap-2">
						<button class="btn btn-sm btn-outline-primary" type="button">View Details</button>
						<button
							class="btn btn-sm btn-outline-secondary action-button"
							type="button"
							data-action="like"
							data-title="Building a Simple MVC Blog"
						>
							<span class="iconify" data-icon="mdi:heart-outline"></span>
							<span class="action-count">12</span>
						</button>
						<button
							class="btn btn-sm btn-outline-secondary action-button"
							type="button"
							data-action="comment"
							data-title="Building a Simple MVC Blog"
						>
							<span class="iconify" data-icon="mdi:comment-outline"></span>
							<span class="action-count">3</span>
						</button>
						<button
							class="btn btn-sm btn-outline-secondary action-button"
							type="button"
							data-action="share"
							data-title="Building a Simple MVC Blog"
						>
							<span class="iconify" data-icon="mdi:share-outline"></span>
							<span class="action-count">1</span>
						</button>
					</div>
				</div>
			</div>

			<div class="card mb-3">
				<div class="card-body">
					<div class="card-image-placeholder mb-3"></div>
					<h5 class="card-title">Top Productivity Habits for Bloggers</h5>
					<p class="card-text text-muted">
						Time-saving techniques to plan, draft, and publish blogs with clarity.
					</p>
					<div class="meta-text mb-3">Creator: Amina · May 12, 2026</div>
					<div class="d-flex flex-wrap gap-2">
						<button class="btn btn-sm btn-outline-primary" type="button">View Details</button>
						<button
							class="btn btn-sm btn-outline-secondary action-button"
							type="button"
							data-action="like"
							data-title="Managing Uploads in PHP"
						>
							<span class="iconify" data-icon="mdi:heart-outline"></span>
							<span class="action-count">9</span>
						</button>
						<button
							class="btn btn-sm btn-outline-secondary action-button"
							type="button"
							data-action="comment"
							data-title="Managing Uploads in PHP"
						>
							<span class="iconify" data-icon="mdi:comment-outline"></span>
							<span class="action-count">2</span>
						</button>
						<button
							class="btn btn-sm btn-outline-secondary action-button"
							type="button"
							data-action="share"
							data-title="Managing Uploads in PHP"
						>
							<span class="iconify" data-icon="mdi:share-outline"></span>
							<span class="action-count">1</span>
						</button>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="card-body">
					<div class="card-image-placeholder mb-3"></div>
					<h5 class="card-title">Designing a Better Reading Experience</h5>
					<p class="card-text text-muted">
						Improve readability with layout choices, spacing, and clean typography.
					</p>
					<div class="meta-text mb-3">Creator: Rohan · May 11, 2026</div>
					<div class="d-flex flex-wrap gap-2">
						<button class="btn btn-sm btn-outline-primary" type="button">View Details</button>
						<button
							class="btn btn-sm btn-outline-secondary action-button"
							type="button"
							data-action="like"
							data-title="Routing Essentials for MVC"
						>
							<span class="iconify" data-icon="mdi:heart-outline"></span>
							<span class="action-count">7</span>
						</button>
						<button
							class="btn btn-sm btn-outline-secondary action-button"
							type="button"
							data-action="comment"
							data-title="Routing Essentials for MVC"
						>
							<span class="iconify" data-icon="mdi:comment-outline"></span>
							<span class="action-count">1</span>
						</button>
						<button
							class="btn btn-sm btn-outline-secondary action-button"
							type="button"
							data-action="share"
							data-title="Routing Essentials for MVC"
						>
							<span class="iconify" data-icon="mdi:share-outline"></span>
							<span class="action-count">0</span>
						</button>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-4">
			<div class="card">
				<div class="card-body">
					<div class="section-title mb-2">Recently Viewed</div>
					<div class="mb-3">
						<div class="fw-semibold">Database Schema Walkthrough</div>
						<div class="meta-text">2 hours ago</div>
					</div>
					<div class="mb-3">
						<div class="fw-semibold">MVC Folder Structure</div>
						<div class="meta-text">Yesterday</div>
					</div>
					<div>
						<div class="fw-semibold">Validation Helper Basics</div>
						<div class="meta-text">2 days ago</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<?php
$content = ob_get_clean();
$pageScripts = <<<'SCRIPT'
<script>
document.addEventListener('DOMContentLoaded', function () {
	var actionButtons = document.querySelectorAll('.action-button');

	function updateCount(button, delta) {
		var countElement = button.querySelector('.action-count');
		if (!countElement) {
			return;
		}
		var current = parseInt(countElement.textContent, 10) || 0;
		var next = Math.max(current + delta, 0);
		countElement.textContent = String(next);
	}

	function toggleAction(button) {
		var isActive = button.classList.contains('is-active');
		if (isActive) {
			button.classList.remove('is-active');
			button.classList.remove('btn-outline-primary');
			button.classList.add('btn-outline-secondary');
			updateCount(button, -1);
		} else {
			button.classList.add('is-active');
			button.classList.remove('btn-outline-secondary');
			button.classList.add('btn-outline-primary');
			updateCount(button, 1);
		}
	}

	function handleShare(button) {
		var title = button.getAttribute('data-title') || 'Blog post';
		var shareData = {
			title: title,
			text: 'BlogSphere post',
			url: window.location.href
		};

		if (navigator.share) {
			navigator.share(shareData)
				.then(function () {
					updateCount(button, 1);
				})
				.catch(function () {
				});
		} else {
			alert('Sharing is not supported in this browser.');
		}
	}

	actionButtons.forEach(function (button) {
		button.addEventListener('click', function () {
			var action = button.getAttribute('data-action');
			if (action === 'share') {
				handleShare(button);
				return;
			}
			toggleAction(button);
		});
	});
});
</script>
SCRIPT;
require __DIR__ . '/../layouts/MainLayout.php';
?>