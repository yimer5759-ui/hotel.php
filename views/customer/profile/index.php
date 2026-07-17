<?php include VIEWS_PATH . '/layouts/admin_layout.php'; ?>
<?php $u = Auth::user(); ?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb"><li class="breadcrumb-item active">My Profile</li></ol>
</nav>

<div class="row g-4">
  <div class="col-lg-4">
    <!-- Profile Card -->
    <div class="panel-card mb-4">
      <div class="panel-card-body text-center py-4">
        <div class="position-relative d-inline-block mb-3">
          <?php if ($user['avatar']): ?>
            <img src="<?= UPLOADS_URL ?>/avatars/<?= Helper::e($user['avatar']) ?>" style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--accent);">
          <?php else: ?>
            <div style="width:90px;height:90px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:700;border:3px solid var(--accent);">
              <?= strtoupper(substr($user['first_name'],0,1)) ?>
            </div>
          <?php endif; ?>
        </div>
        <h5 class="fw-bold mb-0"><?= Helper::e($user['first_name'].' '.$user['last_name']) ?></h5>
        <p class="text-muted small"><?= Helper::e($user['email']) ?></p>
        <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Valued Guest</span>
        <div class="mt-3">
          <small class="text-muted">Member since <?= Helper::formatDate($user['created_at'],'M Y') ?></small>
        </div>
      </div>
    </div>

    <!-- Change Password Card -->
    <div class="panel-card">
      <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-lock me-2"></i>Change Password</h6></div>
      <div class="panel-card-body">
        <form id="change-password-form">
          <?= Auth::csrfField() ?>
          <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" minlength="8" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary w-100 fw-bold">Update Password</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="panel-card">
      <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-person-circle me-2"></i>Edit Profile</h6></div>
      <div class="panel-card-body">
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger alert-modern mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= implode('<br>', array_map([Helper::class,'e'], $errors)) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/customer/profile" enctype="multipart/form-data">
          <?= $csrf ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">First Name *</label>
              <input type="text" name="first_name" class="form-control" value="<?= Helper::e($user['first_name']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Last Name *</label>
              <input type="text" name="last_name" class="form-control" value="<?= Helper::e($user['last_name']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control bg-light" value="<?= Helper::e($user['email']) ?>" disabled>
              <small class="text-muted">Email cannot be changed.</small>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone Number</label>
              <input type="tel" name="phone" class="form-control" value="<?= Helper::e($user['phone'] ?? '') ?>" placeholder="+1 234 567 8900">
            </div>
            <div class="col-12">
              <label class="form-label">Profile Photo</label>
              <input type="file" name="avatar" class="form-control" accept="image/*">
              <small class="text-muted">Max 5MB. JPG, PNG, or WebP.</small>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-primary fw-bold px-4">
                <i class="bi bi-save me-2"></i>Save Changes
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Change Password Modal (triggered by form above) -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm"></div>
</div>

<?php include VIEWS_PATH . '/layouts/admin_footer.php'; ?>
