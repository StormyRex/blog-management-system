<?php
$baseUrl    = defined('BASE_URL') ? BASE_URL : '';
$isLoggedIn = !empty($_SESSION['user']);
$activePage = $activePage ?? '';

require_once __DIR__ . '/../api/Helpers/PermissionHelper.php';

$canCreateBlog = $isLoggedIn
	? current_user_has_permission('BLOG_CREATE')
	: false;
?>
<nav class="navbar navbar-expand-lg navbar-light">
	<div class="container">

		<a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?php echo $baseUrl; ?>/">
			<img
				class="brand-logo"
				src="<?php echo $baseUrl; ?>/assets/images/blog-post.svg"
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
					<a class="nav-link <?php echo $activePage === 'home'    ? 'active' : ''; ?>" href="<?php echo $baseUrl; ?>/">Home</a>
				</li>
				<li class="nav-item">
					<a class="nav-link <?php echo $activePage === 'explore' ? 'active' : ''; ?>" href="<?php echo $baseUrl; ?>/explore">Explore</a>
				</li>
				<li class="nav-item">
					<a class="nav-link <?php echo $activePage === 'contact' ? 'active' : ''; ?>" href="<?php echo $baseUrl; ?>/contact-us">Contact</a>
				</li>
			</ul>

			<div class="d-flex align-items-center gap-2">
				<?php if ($isLoggedIn) { ?>

					<?php if ($canCreateBlog) { ?>
						<a
							class="btn-icon-round"
							href="<?php echo $baseUrl; ?>/blogs/create"
							title="Create Blog"
						>
							<i class="bi bi-plus-lg"></i>
						</a>
					<?php } ?>

					<a class="btn-outline" href="<?php echo $baseUrl; ?>/blogs/my-blogs">My Blogs</a>

					<a class="btn-outline" href="<?php echo $baseUrl; ?>/user/profile">Profile</a>

					<button
						class="btn-solid"
						type="button"
						id="logoutButton"
						data-logout-url="<?php echo $baseUrl; ?>/logout"
					>
						Logout
					</button>

				<?php } else { ?>

					<a class="btn-outline" href="<?php echo $baseUrl; ?>/login">Login</a>
					<a class="btn-solid"   href="<?php echo $baseUrl; ?>/register">Get started</a>

				<?php } ?>
			</div>

		</div>
	</div>
</nav>

