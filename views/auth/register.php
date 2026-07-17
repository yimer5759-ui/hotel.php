<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Register — Grand Azure Hotel</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<script>window.APP_URL='<?= APP_URL ?>';</script>
</head>
<body style="background:linear-gradient(135deg,#0d1f33 0%,#1a3c5e 60%,#1e5a8a 100%);min-height:100vh;">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
      <div class="text-center mb-4">
        <a href="<?= APP_URL ?>/" class="text-decoration-none">
          <div class="auth-logo">Grand <span>Azure</span></div>
        </a>
        <p class="text-white-50 mt-1 small">Create your account</p>
      </div>

      <div class="auth-card" style="max-width:100%;">
        <h4 class="fw-bold mb-1">Create Account</h4>
        <p class="text-muted small mb-4">Join us and start booking your perfect stay</p>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger alert-modern mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div><?= implode('<br>', array_map([Helper::class,'e'], $errors)) ?></div>
          </div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/auth/register">
          <?= $csrf ?>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label">First Name *</label>
              <input type="text" name="first_name" class="form-control" placeholder="John" required
                     value="<?= Helper::e($old['first_name'] ?? '') ?>">
            </div>
            <div class="col-6">
              <label class="form-label">Last Name *</label>
              <input type="text" name="last_name" class="form-control" placeholder="Doe" required
                     value="<?= Helper::e($old['last_name'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Email Address *</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                <input type="email" name="email" class="form-control border-start-0" placeholder="you@example.com" required
                       value="<?= Helper::e($old['email'] ?? '') ?>">
              </div>
            </div>
            <div class="col-12">
              <label class="form-label">Phone Number</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-phone text-muted"></i></span>
                <input type="tel" name="phone" class="form-control border-start-0" placeholder="+1 234 567 8900"
                       value="<?= Helper::e($old['phone'] ?? '') ?>">
              </div>
            </div>
            <div class="col-6">
              <label class="form-label">Password *</label>
              <input type="password" name="password" class="form-control" placeholder="Min 8 characters" required minlength="8">
            </div>
            <div class="col-6">
              <label class="form-label">Confirm Password *</label>
              <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="terms" required>
                <label class="form-check-label small" for="terms">
                  I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                </label>
              </div>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                <i class="bi bi-person-plus me-2"></i>Create Account
              </button>
            </div>
          </div>
        </form>

        <div class="text-center mt-3">
          <span class="text-muted small">Already have an account? </span>
          <a href="<?= APP_URL ?>/auth/login" class="small fw-semibold">Sign In</a>
        </div>
      </div>
      <div class="text-center mt-3">
        <a href="<?= APP_URL ?>/" class="text-white-50 small"><i class="bi bi-arrow-left me-1"></i>Back to website</a>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
