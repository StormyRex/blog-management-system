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
	<link
		href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
		rel="stylesheet"
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

		.auth-left {
			background: linear-gradient(135deg, #121212 0%, #080808 100%);
			position: relative;
			overflow: hidden;
		}

		.auth-left::before {
			content: '';
			position: absolute;
			top: -50%;
			left: -50%;
			width: 200%;
			height: 200%;
			background: radial-gradient(circle, rgba(255,255,255,0.03) 0%, transparent 80%);
			pointer-events: none;
		}

		.auth-logo {
			height: 120px;
			width: 120px;
			object-fit: contain;
			filter: drop-shadow(0 4px 20px rgba(255,255,255,0.15));
			margin-bottom: 16px;
		}

		.auth-right {
			background: #ffffff;
		}

		.auth-form {
			max-width: 420px;
			margin: 0 auto;
		}

		.auth-form .mb-3 {
			margin-bottom: 0.6rem !important;
		}

		/* Consolidated Styles from Pages */
		.toggle-password {
			width: 2.75rem;
			border-top-right-radius: 8px !important;
			border-bottom-right-radius: 8px !important;
			border: 1px solid #e0e0e0;
			border-left: none;
			background: #ffffff;
			color: #555555;
			display: flex;
			align-items: center;
			justify-content: center;
			transition: background 0.15s, border-color 0.15s, color 0.15s;
		}

		.toggle-password:hover {
			background: #f8f9fa;
			color: #111111;
			border-color: #e0e0e0;
		}

		.password-hint {
			margin-top: 6px;
			background: #fbfbfb;
			border: 1px solid #eeeeee;
			border-radius: 8px;
			padding: 6px 10px;
		}

		.password-hint ul {
			margin-bottom: 0;
			padding-left: 1.1rem;
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 2px 12px;
		}

		.password-hint li {
			font-size: 0.7rem;
			color: #666666;
			margin-bottom: 2px;
		}

		.password-hint li::marker {
			color: #999999;
		}

		.section-title {
			display: inline-block;
			background: #f4f4f4;
			border: 1px solid #e0e0e0;
			border-radius: 100px;
			padding: 4px 14px;
			font-size: 0.7rem;
			font-weight: 600;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			color: #555555;
			margin-bottom: 8px;
		}

		/* Styling Inputs */
		.form-label {
			font-size: 0.8rem;
			font-weight: 600;
			color: #333333;
			margin-bottom: 4px;
		}

		.form-control {
			border-radius: 8px;
			border: 1px solid #e0e0e0;
			padding: 6px 12px;
			font-size: 0.85rem;
			transition: border-color 0.15s ease, box-shadow 0.15s ease;
			color: #111111;
		}

		.form-control:focus {
			border-color: #111111;
			box-shadow: 0 0 0 3px rgba(17, 17, 17, 0.05);
			outline: none;
		}

		.input-group .form-control {
			border-top-right-radius: 0;
			border-bottom-right-radius: 0;
		}

		/* Buttons */
		.btn-dark {
			background: #111111 !important;
			border-color: #111111 !important;
			color: #ffffff !important;
			border-radius: 8px;
			padding: 8px 16px !important;
			font-weight: 600;
			font-size: 0.9rem;
			transition: background 0.15s, transform 0.15s;
		}

		.btn-dark:hover:not(:disabled) {
			background: #333333 !important;
			border-color: #333333 !important;
			transform: translateY(-1px);
		}

		.btn-dark:disabled {
			background: #666666 !important;
			border-color: #666666 !important;
			opacity: 0.8;
		}

		.btn-outline-dark {
			border-color: #111111;
			color: #111111;
			border-radius: 8px;
			font-weight: 600;
			transition: background 0.15s, color 0.15s;
		}

		.btn-outline-dark:hover {
			background: #111111;
			color: #ffffff;
		}

		/* Alerts */
		.alert {
			border-radius: 8px;
			font-size: 0.875rem;
			padding: 12px 16px;
			border: 1px solid transparent;
		}

		.alert-danger {
			background-color: #fdf2f2;
			border-color: #fde8e8;
			color: #9b1c1c;
		}

		.alert-success {
			background-color: #f3faf7;
			border-color: #def7ec;
			color: #03543f;
		}

		/* Custom validation state overrides */
		.is-invalid {
			border-color: #dc3545 !important;
		}

		.is-invalid:focus {
			box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1) !important;
		}

		.is-valid {
			border-color: #198754 !important;
		}

		.is-valid:focus {
			box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.1) !important;
		}

		.invalid-feedback {
			font-size: 0.8rem;
			color: #dc3545;
			margin-top: 4px;
			font-weight: 500;
		}

		/* Links and general typography */
		a {
			color: #111111;
			font-weight: 500;
			text-decoration: none;
			transition: color 0.15s;
		}

		a:hover {
			color: #333333;
			text-decoration: underline;
		}

		.text-primary {
			color: #111111 !important;
			font-weight: 600;
		}

		.text-primary:hover {
			color: #333333 !important;
		}

		.text-muted {
			color: #767676 !important;
		}

		a.text-muted:hover {
			color: #111111 !important;
			text-decoration: underline;
		}

		/* Make split layout responsive and look premium */
		.auth-left-content {
			max-width: 380px;
		}

		@media (max-width: 767.98px) {
			.auth-left {
				min-height: 280px !important;
				padding: 48px 0;
			}
			.auth-right {
				padding: 48px 0;
			}
		}
	</style>
</head>

<body>
	<div class="container-fluid min-vh-100">
		<div class="row min-vh-100">
			<div class="col-md-6 auth-left text-white d-flex align-items-center justify-content-center py-5">
				<div class="text-center px-4 auth-left-content">
					<img
						class="auth-logo"
						src="<?php echo $baseUrl; ?>/assets/images/blog-post.svg"
						alt="BlogSphere logo"
					>
					<h1 class="fw-extrabold tracking-tight" style="font-weight: 800; font-size: 2.25rem;">BlogSphere</h1>
					<p class="lead mb-3" style="font-weight: 500; opacity: 0.9;">Write. Share. Inspire.</p>
					<p style="opacity: 0.7; font-size: 0.95rem;">Join a community of writers and readers, and discover stories that matter.</p>
				</div>
			</div>
			<div class="col-md-6 auth-right d-flex align-items-center justify-content-center">
				<div class="auth-form w-100 px-4">
					<?php echo $content ?? ''; ?>
				</div>
			</div>
		</div>
	</div>

</body>

</html>
