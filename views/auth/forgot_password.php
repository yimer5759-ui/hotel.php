<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Forgot Password — Grand Azure Hotel</title>
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
        <div class="text-center mb-4">
          <div style="width:60px;height:60px;background:rgba(201,168,76,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
            <i class="bi bi-key fs-2 text-warning"></i>
          </div>
          <h5 class="fw-bold">Forgot Password?</h5>
          <p class="text-muted small">Enter your email and we'll send a reset link.</p>
        </div>

        <?php if ($message): ?>
          <div class="alert alert-<?= $type ?> alert-modern mb-3">
            <i class="bi bi-<?= $type==='success'?'check-circle':'exclamation-circle' ?>-fill me-2"></i>
            <?= Helper::e($message) ?>
          </div>
          <?php if (Auth::hasFlash('reset_url')): ?>
            <div class="alert alert-info p-2 small">
              <strong>Demo Reset Link:</strong><br>
              <a href="<?= Auth::getFlash('reset_url') ?>" class="text-break"><?= Auth::getFlash('reset_url') ?></a>
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <form method="POST">
          <?= $csrf ?>
          <div class="mb-3">
            <label class="form-label">Email Address</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
              <input type="email" name="email" class="form-control border-start-0" placeholder="you@example.com" required autofocus>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
            <i class="bi bi-send me-2"></i>Send Reset Link
          </button>
        </form>
        <div class="text-center mt-3">
          <a href="<?= APP_URL ?>/auth/login" class="small text-muted"><i class="bi bi-arrow-left me-1"></i>Back to Login</a>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
