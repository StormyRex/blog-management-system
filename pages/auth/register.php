<?php
$pageTitle = 'Register | BlogSphere';
$errors = $errors ?? [];
$old = $old ?? [
    'name' => '',
    'email' => '',
    'username' => ''
];
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
ob_start();
?>
<div class="text-center mb-3">
	<div class="section-title">Authentication</div>
	<h1 class="h5 mt-2">Create Account</h1>
</div>
<?php if (!empty($errors['general'])) { ?>
	<div class="alert alert-danger">
		<?php echo htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?>
	</div>
<?php } ?>
<form method="POST" action="<?php echo $baseUrl; ?>/register">
	<div class="mb-3">
		<label class="form-label" for="registerName">Full Name</label>
		<input
			type="text"
			class="form-control <?php echo !empty($errors['name']) ? 'is-invalid' : ''; ?>"
			id="registerName"
			name="name"
			placeholder="Your name"
			value="<?php echo htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
		>
		<?php if (!empty($errors['name'])) { ?>
			<div class="invalid-feedback">
				<?php echo htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8'); ?>
			</div>
		<?php } ?>
	</div>
	<div class="mb-3">
		<label class="form-label" for="registerEmail">Email</label>
		<input
			type="email"
			class="form-control <?php echo !empty($errors['email']) ? 'is-invalid' : ''; ?>"
			id="registerEmail"
			name="email"
			placeholder="you@example.com"
			value="<?php echo htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
		>
		<?php if (!empty($errors['email'])) { ?>
			<div class="invalid-feedback">
				<?php echo htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8'); ?>
			</div>
		<?php } ?>
	</div>
	<div class="mb-3">
		<label class="form-label" for="registerUsername">Username</label>
		<input
			type="text"
			class="form-control <?php echo !empty($errors['username']) ? 'is-invalid' : ''; ?>"
			id="registerUsername"
			name="username"
			placeholder="Choose a username"
			value="<?php echo htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
		>
		<?php if (!empty($errors['username'])) { ?>
			<div class="invalid-feedback">
				<?php echo htmlspecialchars($errors['username'], ENT_QUOTES, 'UTF-8'); ?>
			</div>
		<?php } ?>
	</div>
	<div class="mb-3">
		<label class="form-label" for="registerPassword">Password</label>
		<input
			type="password"
			class="form-control <?php echo !empty($errors['password']) ? 'is-invalid' : ''; ?>"
			id="registerPassword"
			name="password"
			placeholder="Create a password"
		>
		<?php if (!empty($errors['password'])) { ?>
			<div class="invalid-feedback">
				<?php echo htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8'); ?>
			</div>
		<?php } ?>
	</div>
	<div class="mb-3">
		<label class="form-label" for="registerConfirmPassword">Confirm Password</label>
		<input
			type="password"
			class="form-control <?php echo !empty($errors['confirm_password']) ? 'is-invalid' : ''; ?>"
			id="registerConfirmPassword"
			name="confirm_password"
			placeholder="Re-enter your password"
		>
		<?php if (!empty($errors['confirm_password'])) { ?>
			<div class="invalid-feedback">
				<?php echo htmlspecialchars($errors['confirm_password'], ENT_QUOTES, 'UTF-8'); ?>
			</div>
		<?php } ?>
	</div>
	<button class="btn btn-primary w-100" type="submit">Register</button>
</form>
<div class="text-center mt-3">
	<span class="text-muted">Already have an account?</span>
	<a href="<?php echo $baseUrl; ?>/login" class="text-decoration-none">Login</a>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/AuthLayout.php';
?>
