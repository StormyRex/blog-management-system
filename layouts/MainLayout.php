<?php
$pageTitle = $pageTitle ?? 'BlogSphere';
$activePage = $activePage ?? '';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
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
	<style>
		body {
			background: #f6f7f8;
			color: #1c1c1c;
		}

		.navbar-brand {
			letter-spacing: 0.02em;
		}

		.brand-logo {
			height: 32px;
			width: 32px;
			object-fit: contain;
		}

		.hero-card {
			background: #ffffff;
			border: 1px solid #ededed;
			border-radius: 10px;
		}

		.section-title {
			font-size: 0.95rem;
			font-weight: 600;
			color: #6c757d;
			text-transform: uppercase;
			letter-spacing: 0.02em;
		}

		.card-image-placeholder {
			height: 180px;
			background: #e9ecef;
			border-radius: 6px;
		}

		.meta-text {
			color: #6c757d;
			font-size: 0.9rem;
		}

		.action-button {
			display: inline-flex;
			align-items: center;
			gap: 6px;
		}

		.action-button .iconify {
			font-size: 18px;
		}

		.action-button.is-active {
			border-color: #0d6efd;
			color: #0d6efd;
			background-color: transparent;
			box-shadow: none;
		}

		.footer {
			background: #ffffff;
			border-top: 1px solid #ededed;
		}

		.footer-logo {
			height: 20px;
			width: 20px;
			object-fit: contain;
		}
	</style>
	<?php if (!empty($pageStyles)) {
		echo $pageStyles;
	} ?>
</head>

<body>
	<?php require __DIR__ . '/../components/Navbar.php'; ?>

	<main class="container py-4">
		<?php echo $content ?? ''; ?>
	</main>

	<?php require __DIR__ . '/../components/Footer.php'; ?>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://code.iconify.design/2/2.2.1/iconify.min.js"></script>
	<?php if (!empty($pageScripts)) {
		echo $pageScripts;
	} ?>
</body>

</html>

