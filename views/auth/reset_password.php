<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reset Password — Grand Azure Hotel</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body style="background:linear-gradient(135deg,#0d1f33,#1a3c5e,#1e5a8a);min-height:100vh;">
<div class="container py-5">
  <div class="row justify-content-center align-items-center min-vh-100">
    <div class="col-md-5 col-lg-4">
      <div class="text-center mb-4"><div class="auth-logo">Grand <span>Azure</span></div></div>
      <div class="auth-card">
        <h5 class="fw-bold mb-1">Set New Password</h5>
        <p class="text-muted small mb-4">Choose a strong password for your account.</p>

        <?php if ($error): ?>
          <div class="alert alert-danger alert-modern mb-3">
            <i class="bi bi-exclamation-circle-fill me-2"></i><?= Helper::e($error) ?>
          </div>
        <?php endif; ?>

        <form method="POST">
          <?= $csrf ?>
          <input type="hidden" name="token" value="<?= Helper::e($token) ?>">
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="password" class="form-control" placeholder="Min 8 characters" required minlength="8" autofocus>
          </div>
          <div class="mb-4">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
          </div>
          <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
            <i class="bi bi-check-circle me-2"></i>Reset Password
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
