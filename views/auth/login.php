<?php $settings = (new Settings())->loadAll(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — Grand Azure Hotel</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<script>window.APP_URL='<?= APP_URL ?>';</script>
</head>
<body class="auth-body" style="background:linear-gradient(135deg,#0d1f33 0%,#1a3c5e 60%,#1e5a8a 100%);min-height:100vh;">

<div class="container py-5">
  <div class="row justify-content-center align-items-center min-vh-100">
    <div class="col-md-5 col-lg-4">

      <!-- Logo -->
      <div class="text-center mb-4">
        <a href="<?= APP_URL ?>/" class="text-decoration-none">
          <div class="auth-logo">Grand <span>Azure</span></div>
        </a>
        <p class="text-white-50 mt-1 small">Hotel Management System</p>
      </div>

      <div class="auth-card">
        <h4 class="mb-1 fw-bold" style="color:var(--dark)">Welcome Back</h4>
        <p class="text-muted small mb-4">Sign in to your account to continue</p>

        <?php if (Auth::hasFlash('success')): ?>
          <div class="alert alert-success alert-modern alert-auto-dismiss">
            <i class="bi bi-check-circle-fill"></i> <?= Helper::e(Auth::getFlash('success')) ?>
          </div>
        <?php endif; ?>

        <?php if (Auth::hasFlash('error')): ?>
          <div class="alert alert-danger alert-modern">
            <i class="bi bi-exclamation-circle-fill"></i> <?= Helper::e(Auth::getFlash('error')) ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger alert-modern">
            <i class="bi bi-exclamation-circle-fill"></i> <?= Helper::e($error) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/auth/login" autocomplete="on">
          <?= $csrf ?>

          <div class="mb-3">
            <label class="form-label">Email Address</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
              <input type="email" name="email" class="form-control border-start-0 ps-0"
                     placeholder="you@example.com" required autofocus
                     value="<?= Helper::e($_POST['email'] ?? '') ?>">
            </div>
          </div>

          <div class="mb-3">
            <div class="d-flex justify-content-between">
              <label class="form-label">Password</label>
              <a href="<?= APP_URL ?>/auth/forgot-password" class="small text-muted">Forgot password?</a>
            </div>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
              <input type="password" name="password" id="passwordInput" class="form-control border-start-0 border-end-0 ps-0" placeholder="••••••••" required>
              <button type="button" class="input-group-text bg-light" onclick="togglePass()">
                <i class="bi bi-eye" id="eye-icon"></i>
              </button>
            </div>
          </div>

          <div class="mb-4 d-flex align-items-center">
            <input class="form-check-input me-2" type="checkbox" id="remember" name="remember">
            <label class="form-check-label small" for="remember">Keep me signed in</label>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
          </button>
        </form>

        <div class="text-center mt-4">
          <span class="text-muted small">Don't have an account? </span>
          <a href="<?= APP_URL ?>/auth/register" class="small fw-semibold">Create Account</a>
        </div>

        <!-- Demo credentials -->
        <div class="mt-4 p-3 rounded-3" style="background:#f8f9fa;border:1px dashed #dee2e6;">
          <p class="small fw-bold text-muted mb-2"><i class="bi bi-info-circle me-1"></i>Demo Credentials</p>
          <div class="row g-1">
            <div class="col-4 text-center">
              <button class="btn btn-sm w-100 py-1" style="font-size:.7rem;background:#e8f4fd;color:#0d6efd;border:1px solid #bee2fd;" onclick="fillLogin('admin@hotel.com')">
                <i class="bi bi-shield-check d-block mb-1"></i>Admin
              </button>
            </div>
            <div class="col-4 text-center">
              <button class="btn btn-sm w-100 py-1" style="font-size:.7rem;background:#e8f8f0;color:#198754;border:1px solid #b7e4c7;" onclick="fillLogin('receptionist@hotel.com')">
                <i class="bi bi-person-badge d-block mb-1"></i>Staff
              </button>
            </div>
            <div class="col-4 text-center">
              <button class="btn btn-sm w-100 py-1" style="font-size:.7rem;background:#fff8e1;color:#c9a84c;border:1px solid #ffe082;" onclick="fillLogin('customer@hotel.com')">
                <i class="bi bi-person d-block mb-1"></i>Guest
              </button>
            </div>
          </div>
          <p class="text-muted text-center mt-2 mb-0" style="font-size:.7rem;">Password for all: <strong>password</strong></p>
        </div>
      </div>

      <div class="text-center mt-3">
        <a href="<?= APP_URL ?>/" class="text-white-50 small"><i class="bi bi-arrow-left me-1"></i>Back to website</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/app.js"></script>
<script>
function togglePass(){
  const i=document.getElementById('passwordInput');
  const e=document.getElementById('eye-icon');
  i.type=i.type==='password'?'text':'password';
  e.className=i.type==='password'?'bi bi-eye':'bi bi-eye-slash';
}
function fillLogin(email){
  document.querySelector('[name="email"]').value=email;
  document.querySelector('[name="password"]').value='password';
}
</script>
</body>
</html>
