<?php
$pageTitle = $pageTitle ?? 'BlogSphere';
$activePage = $activePage ?? '';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$isLoggedIn = !empty($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
	<link
		href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
		rel="stylesheet"
	>
	<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
	>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
	<style>
		body {
			font-family: 'Inter', sans-serif;
			background: #ffffff;
			color: #111111;
			-webkit-font-smoothing: antialiased;
		}

		/* Sticky blurred navbar */
		.navbar {
			background: rgba(255,255,255,0.95) !important;
			backdrop-filter: blur(10px);
			-webkit-backdrop-filter: blur(10px);
			border-bottom: 1px solid #e8e8e8 !important;
			position: sticky;
			top: 0;
			z-index: 1000;
		}

		.navbar-brand {
			letter-spacing: 0.02em;
			color: #000000 !important;
			font-weight: 700;
		}

		.nav-link {
			color: #000000 !important;
		}

		.nav-link.active {
			color: #000000 !important;
			font-weight: 600;
		}

		.brand-logo {
			height: 28px;
			width: 28px;
			object-fit: contain;
		}

		/* ── Navbar buttons ──────────────────────────────────── */
		.btn-solid,
		.btn-outline {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 7px 16px;
			border-radius: 10px;
			font-size: 0.875rem;
			font-weight: 600;
			font-family: inherit;
			text-decoration: none;
			cursor: pointer;
			border: 1.5px solid transparent;
			transition: background 0.15s, border-color 0.15s, transform 0.15s;
			white-space: nowrap;
		}

		.btn-solid {
			background: #111;
			color: #fff;
			border-color: #111;
		}

		.btn-solid:hover {
			background: #333;
			color: #fff;
			transform: translateY(-1px);
		}

		.btn-outline {
			background: transparent;
			color: #111;
			border-color: #e0e0e0;
		}

		.btn-outline:hover {
			border-color: #111;
			background: #f5f5f5;
			color: #111;
			transform: translateY(-1px);
		}

		.btn-icon-round {
			width: 36px;
			height: 36px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			background: #111;
			color: #fff;
			border-radius: 50%;
			border: none;
			font-size: 1rem;
			text-decoration: none;
			transition: background 0.15s;
		}

		.btn-icon-round:hover { background: #333; color: #fff; }

		/* ── Hero ─────────────────────────────────────────── */
		.hero-section {
			padding: 72px 0 64px;
			text-align: center;
			border-bottom: 1px solid #e8e8e8;
		}

		.hero-pill {
			display: inline-block;
			background: #f4f4f4;
			border: 1px solid #e0e0e0;
			border-radius: 100px;
			padding: 4px 16px;
			font-size: 0.75rem;
			font-weight: 600;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			color: #555;
			margin-bottom: 20px;
		}

		.hero-title {
			font-size: clamp(2.2rem, 5.5vw, 3.6rem);
			font-weight: 900;
			letter-spacing: -0.04em;
			line-height: 1.1;
			margin-bottom: 16px;
		}

		.hero-sub {
			color: #666;
			font-size: 1rem;
			max-width: 440px;
			margin: 0 auto 32px;
		}

		/* ── Search bar ────────────────────────────────────── */
		.search-box {
			position: relative;
			max-width: 520px;
			margin: 0 auto;
		}

		.search-box input {
			width: 100%;
			padding: 13px 120px 13px 42px;
			border: 1.5px solid #e0e0e0;
			border-radius: 12px;
			font-family: inherit;
			font-size: 0.93rem;
			outline: none;
			transition: border-color 0.2s;
		}

		.search-box input:focus { border-color: #111; }

		.search-box .bi-search {
			position: absolute;
			left: 15px;
			top: 50%;
			transform: translateY(-50%);
			color: #aaa;
			pointer-events: none;
		}

		.search-box .search-btn {
			position: absolute;
			right: 7px;
			top: 50%;
			transform: translateY(-50%);
			background: #111;
			color: #fff;
			border: none;
			border-radius: 9px;
			padding: 7px 18px;
			font-size: 0.85rem;
			font-weight: 600;
			font-family: inherit;
			cursor: pointer;
		}

		/* ── Search dropdown ───────────────────────────────── */
		.search-dropdown {
			position: absolute;
			top: calc(100% + 6px);
			left: 0; right: 0;
			background: #fff;
			border: 1.5px solid #e0e0e0;
			border-radius: 12px;
			box-shadow: 0 8px 30px rgba(0,0,0,0.1);
			overflow: hidden;
			z-index: 1050;
			display: none;
		}

		.search-result-item {
			display: flex;
			align-items: center;
			gap: 12px;
			padding: 11px 14px;
			border-bottom: 1px solid #f4f4f4;
			text-decoration: none;
			color: #111;
		}

		.search-result-item:last-child { border-bottom: none; }
		.search-result-item:hover { background: #f9f9f9; }

		.search-thumb {
			width: 46px; height: 32px;
			border-radius: 6px;
			overflow: hidden;
			background: #f0f0f0;
			flex-shrink: 0;
		}

		.search-thumb img { width: 100%; height: 100%; object-fit: cover; }
		.search-result-title { font-size: 0.86rem; font-weight: 600; }
		.search-result-meta  { font-size: 0.76rem; color: #888; }

		/* ── Blog cards ────────────────────────────────────── */
		.blog-card {
			border: 1.5px solid #e8e8e8;
			border-radius: 16px;
			overflow: hidden;
			transition: box-shadow 0.2s, border-color 0.2s;
		}

		.blog-card:hover {
			box-shadow: 0 6px 24px rgba(0,0,0,0.09);
			border-color: #ccc;
		}

		.blog-card-thumb {
			aspect-ratio: 16/9;
			background: #f4f4f4;
			overflow: hidden;
		}

		.blog-card-thumb img,
		.blog-card-thumb video {
			width: 100%; height: 100%;
			object-fit: cover; display: block;
		}

		.blog-card-thumb .no-media {
			height: 100%;
			display: flex;
			align-items: center;
			justify-content: center;
			color: #bbb;
			font-size: 1.8rem;
		}

		.blog-badge {
			font-size: 0.7rem;
			font-weight: 700;
			letter-spacing: 0.05em;
			text-transform: uppercase;
			background: #111;
			color: #fff;
			padding: 3px 10px;
			border-radius: 100px;
		}

		.blog-card-title {
			font-size: 1rem;
			font-weight: 700;
			letter-spacing: -0.01em;
			line-height: 1.35;
		}

		.blog-card-desc {
			font-size: 0.875rem;
			color: #666;
			display: -webkit-box;
			-webkit-line-clamp: 2;
			-webkit-box-orient: vertical;
			overflow: hidden;
		}

		/* ── Action buttons (like / comment / share) ───────── */
		.action-btn {
			display: inline-flex;
			align-items: center;
			gap: 5px;
			padding: 5px 12px;
			border: 1.5px solid #e0e0e0;
			border-radius: 8px;
			background: transparent;
			font-size: 0.82rem;
			font-weight: 500;
			color: #444;
			cursor: pointer;
			font-family: inherit;
			transition: border-color 0.15s, color 0.15s;
			text-decoration: none;
		}

		.action-btn:hover        { border-color: #111; color: #111; }
		.action-btn.liked        { border-color: #e53935; color: #e53935; }
		.action-btn.view         { background: #111; color: #fff; border-color: #111; font-weight: 600; }
		.action-btn.view:hover   { background: #333; color: #fff; }
		.action-label            { font-size: 0.82rem; }

		/* ── Blog details page ─────────────────────────────── */
		.details-wrap {
			max-width: 780px;
			margin: 0 auto;
			padding: 32px 0 64px;
		}

		.details-back {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			font-size: 0.85rem;
			font-weight: 600;
			color: #555;
			text-decoration: none;
			margin-bottom: 24px;
			transition: color 0.15s;
		}

		.details-back:hover { color: #111; }

		.details-card {
			border: 1.5px solid #e8e8e8;
			border-radius: 20px;
			overflow: hidden;
		}

		.details-header {
			padding: 36px 40px 28px;
			border-bottom: 1px solid #f0f0f0;
		}

		.details-title {
			font-size: clamp(1.5rem, 3.5vw, 2.2rem);
			font-weight: 900;
			letter-spacing: -0.03em;
			line-height: 1.15;
			margin-bottom: 12px;
		}

		.details-meta {
			font-size: 0.82rem;
			color: #888;
		}

		.details-byline {
			font-size: 0.82rem;
			color: #999;
		}

		.details-media-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
			gap: 12px;
			padding: 28px 40px;
			border-bottom: 1px solid #f0f0f0;
		}

		.details-media-item img,
		.details-media-item video {
			width: 100%;
			border-radius: 12px;
			display: block;
		}

		.details-body {
			padding: 32px 40px;
			font-size: 1.05rem;
			line-height: 1.85;
			color: #222;
			border-bottom: 1px solid #f0f0f0;
		}

		.details-actions {
			padding: 16px 40px;
			display: flex;
			flex-wrap: wrap;
			gap: 8px;
			align-items: center;
		}

		@media (max-width: 640px) {
			.details-header  { padding: 24px 20px 20px; }
			.details-body    { padding: 24px 20px; }
			.details-actions { padding: 14px 20px; }
			.details-media-grid { padding: 20px; }
		}

		/* ── Sidebar ───────────────────────────────────────── */
		.sidebar-box {
			border: 1.5px solid #e8e8e8;
			border-radius: 16px;
			overflow: hidden;
			position: sticky;
			top: 76px;
		}

		.sidebar-header {
			padding: 16px 18px 12px;
			border-bottom: 1px solid #f0f0f0;
		}

		.sidebar-title {
			font-size: 0.95rem;
			font-weight: 700;
			letter-spacing: -0.01em;
		}

		.history-link {
			display: block;
			padding: 10px 18px;
			border-bottom: 1px solid #f4f4f4;
			text-decoration: none;
			color: #111;
			font-size: 0.84rem;
			font-weight: 500;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		.history-link:last-child { border-bottom: none; }
		.history-link:hover { background: #f9f9f9; }
		.history-date { font-size: 0.74rem; color: #999; }

		/* ── CTA banner (guests only) ──────────────────────── */
		.cta-block {
			background: #111;
			border-radius: 20px;
			padding: 52px 40px;
			text-align: center;
			color: #fff;
			margin-top: 48px;
		}

		.cta-block h2 { font-size: clamp(1.5rem, 3.5vw, 2.2rem); font-weight: 800; letter-spacing: -0.03em; }
		.cta-block p  { color: rgba(255,255,255,0.6); font-size: 0.95rem; margin-bottom: 24px; }

		.btn-cta {
			background: #fff;
			color: #111;
			font-weight: 700;
			font-family: inherit;
			border: none;
			border-radius: 10px;
			padding: 11px 28px;
			font-size: 0.95rem;
			text-decoration: none;
			display: inline-block;
		}

		@media (max-width: 768px) {
			.hero-section { padding: 48px 0 40px; }
			.cta-block    { padding: 36px 20px; }
		}

		/* ── Explore page header ───────────────────────────── */
		.explore-header {
			text-align: center;
			padding: 52px 0 40px;
			border-bottom: 1px solid #e8e8e8;
			margin-bottom: 40px;
		}

		.hero-card {
			background: #f8f9fa;
			border: 1px solid #000000;
			border-radius: 10px;
		}

		.blog-card-media img,
		.blog-card-media video {
			width: 100%;
			height: 100%;
			object-fit: cover;
			display: block;
		}

		#globalSearchResults {
			background: #fff;
			border: 1px solid #dee2e6;
			border-top: none;
			border-radius: 0 0 0.75rem 0.75rem;
			box-shadow: 0 1rem 2.5rem rgba(0, 0, 0, 0.12);
			max-height: 300px;
			overflow-y: auto;
			position: absolute;
			top: 100%;
			left: 0;
			right: 0;
		}

		#globalSearchResults .search-suggestion-item {
			padding: 0.75rem 1rem;
		}

		#globalSearchResults .search-suggestion-item + .search-suggestion-item {
			border-top: 1px solid #f1f3f5;
		}

		#globalSearchResults .search-suggestion-thumb,
		#globalSearchResults .search-suggestion-avatar {
			width: 44px;
			height: 44px;
			border-radius: 0.65rem;
			overflow: hidden;
			flex: 0 0 44px;
			background: #f8f9fa;
			border: 1px solid #e9ecef;
		}

		#globalSearchResults .search-suggestion-avatar {
			border-radius: 50%;
		}

		#globalSearchResults .search-suggestion-thumb img,
		#globalSearchResults .search-suggestion-thumb video,
		#globalSearchResults .search-suggestion-avatar img {
			width: 100%;
			height: 100%;
			object-fit: cover;
			display: block;
		}

		#globalSearchResults .search-suggestion-placeholder {
			width: 100%;
			height: 100%;
		}

		#globalSearchResults .search-suggestion-badge {
			flex: 0 0 auto;
			font-size: 0.65rem;
			line-height: 1;
			padding: 0.3rem 0.45rem;
		}

		#globalSearchResults .search-suggestion-title {
			font-size: 0.95rem;
			line-height: 1.2;
		}

		.section-title {
			font-size: 0.95rem;
			font-weight: 600;
			color: #000000;
			text-transform: uppercase;
			letter-spacing: 0.02em;
		}

		.btn-primary {
			background-color: #000000;
			border-color: #000000;
		}

		.btn-primary:hover {
			background-color: #1a1a1a;
			border-color: #1a1a1a;
		}

		.btn-outline-primary {
			color: #000000;
			border-color: #000000;
		}

		.btn-outline-primary:hover {
			background-color: #000000;
			border-color: #000000;
			color: #ffffff;
		}

		.btn-outline-secondary {
			color: #000000;
			border-color: #000000;
		}

		.btn-outline-secondary:hover {
			background-color: #000000;
			border-color: #000000;
			color: #ffffff;
		}

		.card {
			border-color: #000000;
		}

		.card-image-placeholder {
			height: 180px;
			background: #e9ecef;
			border-radius: 6px;
		}

		.card-title {
			color: #000000;
		}

		.meta-text {
			color: #000000;
			font-size: 0.9rem;
		}

		.action-button {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			color: #000000;
		}

		.action-button .iconify {
			font-size: 18px;
		}

		.action-button.is-active {
			border-color: #000000;
			color: #000000;
			background-color: #f8f9fa;
			box-shadow: none;
		}

		.footer {
			background: #000000;
			border-top: 1px solid #000000;
			color: #ffffff;
		}

		.footer-text {
			color: #ffffff !important;
		}

		.footer-logo {
			height: 20px;
			width: 20px;
			object-fit: contain;
		}

		.text-muted {
			color: #333333 !important;
		}

		.text-decoration-none {
			color: #000000;
			font-weight: 500;
		}

		.text-decoration-none:hover {
			color: #1a1a1a;
		}

		.form-label {
			font-size: 0.85rem;
			font-weight: 600;
			color: #333333;
			margin-bottom: 6px;
			display: inline-block;
		}

		.form-control {
			border-radius: 8px;
			border: 1px solid #e0e0e0;
			padding: 10px 14px;
			font-size: 0.9rem;
			color: #111111;
			transition: border-color 0.15s ease, box-shadow 0.15s ease;
		}

		.form-control:focus {
			border-color: #111111;
			box-shadow: 0 0 0 3px rgba(17, 17, 17, 0.05);
			outline: none;
		}

		.form-select {
			border-radius: 8px;
			border: 1px solid #e0e0e0;
			padding: 10px 36px 10px 14px;
			font-size: 0.9rem;
			color: #111111;
			transition: border-color 0.15s ease, box-shadow 0.15s ease;
		}

		.form-select:focus {
			border-color: #111111;
			box-shadow: 0 0 0 3px rgba(17, 17, 17, 0.05);
			outline: none;
		}

		.form-control::placeholder {
			color: #999999;
		}

		.form-card {
			background: #ffffff;
			border: 1px solid #e8e8e8;
			border-radius: 12px;
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.015);
			padding: 32px;
		}

		.btn-edit,
		.btn-delete {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 6px 14px;
			border-radius: 8px;
			font-size: 0.8rem;
			font-weight: 600;
			cursor: pointer;
			transition: background 0.15s, border-color 0.15s, color 0.15s, transform 0.15s;
			text-decoration: none;
			border: 1.5px solid transparent;
		}

		.btn-edit {
			border-color: #e0e0e0;
			background: #ffffff;
			color: #111111;
		}

		.btn-edit:hover {
			border-color: #111111;
			background: #f5f5f5;
			color: #111111;
			transform: translateY(-1px);
		}

		.btn-delete {
			border-color: #fde8e8;
			background: #fff5f5;
			color: #dc3545;
		}

		.btn-delete:hover {
			background: #dc3545;
			border-color: #dc3545;
			color: #ffffff;
			transform: translateY(-1px);
		}

		@media (max-width: 575.98px) {
			.form-card {
				padding: 24px 16px;
			}
		}
	</style>

	<!-- Profile page styles -->
	<style>
		/* Profile-specific layout styles moved from pages/user/profile.php */
		.profile-hero {
			background: linear-gradient(135deg, #111827 0%, #1f2937 55%, #0f172a 100%);
			color: #fff;
		}

		.profile-avatar {
			width: 112px;
			height: 112px;
			object-fit: cover;
		}

		.blog-thumb {
			aspect-ratio: 16 / 9;
			object-fit: cover;
			background: #f3f4f6;
		}

		.profile-placeholder {
			width: 112px;
			height: 112px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			background: rgba(255, 255, 255, 0.12);
			color: #fff;
			border: 1px solid rgba(255, 255, 255, 0.16);
		}
	</style>
	<?php if (!empty($pageStyles)) {
		echo $pageStyles;
	} ?>
</head>

		<body
			class="d-flex flex-column min-vh-100"
			data-is-logged-in="<?php echo $isLoggedIn ? '1' : '0'; ?>"
		>
	<?php require __DIR__ . '/../components/Navbar.php'; ?>

				<main class="container py-4 flex-grow-1">
					<?php echo $content ?? ''; ?>
				</main>

	<?php require __DIR__ . '/../components/Footer.php'; ?>

	<div class="modal fade" id="guestActionModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Guest Access</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<p class="mb-0">Please login to continue</p>
				</div>
				<div class="modal-footer">
					<a class="btn btn-dark" href="<?php echo $baseUrl; ?>/auth/login">Login</a>
					<a class="btn btn-outline-dark" href="<?php echo $baseUrl; ?>/auth/register">Register</a>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Confirm Logout</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<p class="mb-0">Are you sure you want to logout?</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-dark" id="confirmLogoutButton">Yes, Logout</button>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://code.iconify.design/2/2.2.1/iconify.min.js"></script>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			var body = document.body;
			var logoutButton = document.getElementById('logoutButton');
			var confirmLogoutButton = document.getElementById('confirmLogoutButton');

			if (logoutButton) {
				logoutButton.addEventListener('click', function () {
					bootstrap.Modal.getOrCreateInstance(document.getElementById('logoutConfirmModal')).show();
				});
			}

			if (confirmLogoutButton) {
				confirmLogoutButton.addEventListener('click', function () {
					window.location.href = logoutButton ? logoutButton.getAttribute('data-logout-url') : '<?php echo $baseUrl; ?>/logout';
				});
			}

			window.BlogSphere = window.BlogSphere || {};
			window.BlogSphere.showGuestModal = function () {
				bootstrap.Modal.getOrCreateInstance(document.getElementById('guestActionModal')).show();
			};
		});
	</script>
	<?php if (!empty($pageScripts)) {
		echo $pageScripts;
	} ?>
</body>

</html>

