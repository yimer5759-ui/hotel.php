<?php include VIEWS_PATH . '/layouts/admin_layout.php'; ?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb"><li class="breadcrumb-item active">Hotel Settings</li></ol>
</nav>

<form method="POST" action="<?= APP_URL ?>/admin/settings" enctype="multipart/form-data">
  <?= $csrf ?>
  <div class="row g-4">

    <!-- General -->
    <div class="col-lg-8">
      <div class="panel-card mb-4">
        <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-building me-2"></i>Hotel Information</h6></div>
        <div class="panel-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Hotel Name</label>
              <input type="text" name="hotel_name" class="form-control" value="<?= Helper::e($all['hotel_name'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="hotel_email" class="form-control" value="<?= Helper::e($all['hotel_email'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="text" name="hotel_phone" class="form-control" value="<?= Helper::e($all['hotel_phone'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Address</label>
              <input type="text" name="hotel_address" class="form-control" value="<?= Helper::e($all['hotel_address'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea name="hotel_description" class="form-control" rows="3"><?= Helper::e($all['hotel_description'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Billing -->
      <div class="panel-card mb-4">
        <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-currency-dollar me-2"></i>Billing & Tax</h6></div>
        <div class="panel-card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Currency</label>
              <input type="text" name="currency" class="form-control" value="<?= Helper::e($all['currency'] ?? 'USD') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Currency Symbol</label>
              <input type="text" name="currency_symbol" class="form-control" value="<?= Helper::e($all['currency_symbol'] ?? '$') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Tax Rate (%)</label>
              <input type="number" name="tax_rate" class="form-control" step="0.01" value="<?= Helper::e($all['tax_rate'] ?? '10') ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Policies -->
      <div class="panel-card mb-4">
        <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-clock me-2"></i>Hotel Policies</h6></div>
        <div class="panel-card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Check-In Time</label>
              <input type="time" name="check_in_time" class="form-control" value="<?= Helper::e($all['check_in_time'] ?? '14:00') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Check-Out Time</label>
              <input type="time" name="check_out_time" class="form-control" value="<?= Helper::e($all['check_out_time'] ?? '11:00') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Cancellation (hours)</label>
              <input type="number" name="cancellation_hours" class="form-control" value="<?= Helper::e($all['cancellation_hours'] ?? '24') ?>">
            </div>
            <div class="col-12">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="booking_auto_confirm" value="1" <?= ($all['booking_auto_confirm']??'0')==='1'?'checked':'' ?>>
                <label class="form-check-label">Auto-confirm new bookings</label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Social -->
      <div class="panel-card mb-4">
        <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-share me-2"></i>Social Media</h6></div>
        <div class="panel-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-facebook me-1"></i>Facebook URL</label>
              <input type="url" name="facebook_url" class="form-control" value="<?= Helper::e($all['facebook_url'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-twitter-x me-1"></i>Twitter URL</label>
              <input type="url" name="twitter_url" class="form-control" value="<?= Helper::e($all['twitter_url'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-instagram me-1"></i>Instagram URL</label>
              <input type="url" name="instagram_url" class="form-control" value="<?= Helper::e($all['instagram_url'] ?? '') ?>">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right sidebar -->
    <div class="col-lg-4">
      <div class="panel-card mb-4">
        <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-envelope me-2"></i>Email (SMTP)</h6></div>
        <div class="panel-card-body">
          <div class="mb-3">
            <label class="form-label">SMTP Host</label>
            <input type="text" name="smtp_host" class="form-control" value="<?= Helper::e($all['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com">
          </div>
          <div class="mb-3">
            <label class="form-label">SMTP Port</label>
            <input type="number" name="smtp_port" class="form-control" value="<?= Helper::e($all['smtp_port'] ?? '587') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">SMTP Username</label>
            <input type="text" name="smtp_user" class="form-control" value="<?= Helper::e($all['smtp_user'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">From Name</label>
            <input type="text" name="smtp_from_name" class="form-control" value="<?= Helper::e($all['smtp_from_name'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">From Email</label>
            <input type="email" name="smtp_from" class="form-control" value="<?= Helper::e($all['smtp_from'] ?? '') ?>">
          </div>
        </div>
      </div>

      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary fw-bold py-2">
          <i class="bi bi-save me-2"></i>Save Settings
        </button>
        <a href="<?= APP_URL ?>/admin/dashboard" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </div>
  </div>
</form>

<?php include VIEWS_PATH . '/layouts/admin_footer.php'; ?>
