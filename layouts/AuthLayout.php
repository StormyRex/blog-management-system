<?php
$pageTitle = $pageTitle ?? 'BlogSphere';
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

		.auth-card {
			border: 1px solid #ededed;
			border-radius: 10px;
		}

		.brand-logo {
			height: 30px;
			width: 30px;
			object-fit: contain;
		}

		.section-title {
			font-size: 0.85rem;
			font-weight: 600;
			color: #6c757d;
			text-transform: uppercase;
			letter-spacing: 0.02em;
		}
	</style>
</head>

<body>
	<nav class="navbar navbar-light bg-white border-bottom">
		<div class="container">
			<a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?php echo $baseUrl; ?>/">
				<img
					class="brand-logo"
					src="<?php echo $baseUrl; ?>/assets/images/BlogSphere.png"
					alt="BlogSphere logo"
				>
				<span>BlogSphere</span>
			</a>
		</div>
	</nav>

	<main class="container py-5">
		<div class="row justify-content-center">
			<div class="col-md-6 col-lg-5">
				<div class="card auth-card">
					<div class="card-body">
						<?php echo $content ?? ''; ?>
					</div>
				</div>
			</div>
		</div>
	</main>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

