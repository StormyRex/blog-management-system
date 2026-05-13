<?php
$pageTitle = 'Login | BlogSphere';
$errors = $errors ?? [];
$old = $old ?? ['identity' => ''];
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
ob_start();
?>
<div class="text-center mb-3">
	<div class="section-title">Authentication</div>
	<h1 class="h5 mt-2">Login</h1>
</div>

<?php if (!empty($errors['credentials'])) { ?>
	<div class="alert alert-danger">
		<?php echo htmlspecialchars($errors['credentials'], ENT_QUOTES, 'UTF-8'); ?>
	</div>
<?php } ?>

<?php if (!empty($errors['general'])) { ?>
	<div class="alert alert-danger">
		<?php echo htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?>
	</div>
<?php } ?>

<form method="POST" action="<?php echo $baseUrl; ?>/login">
	<div class="mb-3">
		<label class="form-label" for="loginIdentity">Email or Username</label>
		<input
			type="text"
			class="form-control <?php echo !empty($errors['identity']) ? 'is-invalid' : ''; ?>"
			id="loginIdentity"
			name="identity"
			placeholder="you@example.com"
			value="<?php echo htmlspecialchars($old['identity'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
		>
		<?php if (!empty($errors['identity'])) { ?>
			<div class="invalid-feedback">
				<?php echo htmlspecialchars($errors['identity'], ENT_QUOTES, 'UTF-8'); ?>
			</div>
		<?php } ?>
	</div>
	<div class="mb-3">
		<label class="form-label" for="loginPassword">Password</label>
		<input
			type="password"
			class="form-control <?php echo !empty($errors['password']) ? 'is-invalid' : ''; ?>"
			id="loginPassword"
			name="password"
			placeholder="Enter your password"
		>
		<?php if (!empty($errors['password'])) { ?>
			<div class="invalid-feedback">
				<?php echo htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8'); ?>
			</div>
		<?php } ?>
	</div>
	<button class="btn btn-primary w-100" type="submit">Login</button>
</form>
<div class="text-center mt-3">
	<span class="text-muted">New here?</span>
	<a href="<?php echo $baseUrl; ?>/register" class="text-decoration-none">Create an account</a>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/AuthLayout.php';
?>