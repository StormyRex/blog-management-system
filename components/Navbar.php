<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$isLoggedIn = !empty($_SESSION['user']);
$activePage = $activePage ?? '';
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
	<div class="container">
		<a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?php echo $baseUrl; ?>/">
			<img
				class="brand-logo"
				src="<?php echo $baseUrl; ?>/assets/images/BlogSphere.png"
				alt="BlogSphere logo"
			>
			<span>BlogSphere</span>
		</a>
		<button
			class="navbar-toggler"
			type="button"
			data-bs-toggle="collapse"
			data-bs-target="#mainNavbar"
			aria-controls="mainNavbar"
			aria-expanded="false"
			aria-label="Toggle navigation"
		>
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="mainNavbar">
			<ul class="navbar-nav mx-auto mb-2 mb-lg-0">
				<li class="nav-item">
					<a class="nav-link <?php echo $activePage === 'home' ? 'active' : ''; ?>" href="<?php echo $baseUrl; ?>/">Home</a>
				</li>
				<li class="nav-item">
					<a class="nav-link <?php echo $activePage === 'explore' ? 'active' : ''; ?>" href="<?php echo $baseUrl; ?>/explore">Explore</a>
				</li>
				<li class="nav-item">
					<a class="nav-link <?php echo $activePage === 'contact' ? 'active' : ''; ?>" href="<?php echo $baseUrl; ?>/contact-us">Contact</a>
				</li>
			</ul>
			<div class="d-flex gap-2">
				<?php if ($isLoggedIn) { ?>
					<a class="btn btn-outline-secondary" href="<?php echo $baseUrl; ?>/user/profile">Profile</a>
					<a class="btn btn-primary" href="<?php echo $baseUrl; ?>/logout">Logout</a>
				<?php } else { ?>
						<a class="btn btn-outline-secondary" href="<?php echo $baseUrl; ?>/login">Login</a>
					<a class="btn btn-primary" href="<?php echo $baseUrl; ?>/register">Register</a>
				<?php } ?>
			</div>
		</div>
	</div>
</nav>

