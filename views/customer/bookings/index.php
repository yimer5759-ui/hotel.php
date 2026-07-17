<?php include VIEWS_PATH . '/layouts/admin_layout.php'; ?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= APP_URL ?>/customer/dashboard">Dashboard</a></li><li class="breadcrumb-item active">My Bookings</li></ol>
</nav>

<div class="panel-card">
  <div class="panel-card-header">
    <h6 class="panel-card-title"><i class="bi bi-calendar-check me-2"></i>My Bookings <span class="badge bg-primary ms-1"><?= $pager['total'] ?></span></h6>
    <a href="<?= APP_URL ?>/rooms" class="btn btn-primary btn-sm"><i class="bi bi-plus me-1"></i>New Booking</a>
  </div>

  <?php if (empty($pager['data'])): ?>
    <div class="text-center py-5">
      <i class="bi bi-calendar-x fs-1 text-muted d-block mb-3"></i>
      <h5 class="text-muted">No bookings yet</h5>
      <p class="text-muted small">Browse our rooms and make your first reservation!</p>
      <a href="<?= APP_URL ?>/rooms" class="btn btn-primary px-4">Browse Rooms</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>Booking</th><th>Room</th><th>Check-In</th><th>Check-Out</th><th>Nights</th><th>Total</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($pager['data'] as $b): ?>
            <tr>
              <td>
                <a href="<?= APP_URL ?>/customer/bookings/view/<?= $b['id'] ?>" class="fw-semibold text-primary"><?= Helper::e($b['booking_ref']) ?></a>
                <div class="small text-muted"><?= Helper::formatDate($b['created_at'],'M d, Y') ?></div>
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <?php if ($b['thumbnail']): ?>
                    <img src="<?= UPLOADS_URL ?>/rooms/<?= Helper::e($b['thumbnail']) ?>" style="width:40px;height:35px;object-fit:cover;border-radius:6px;">
                  <?php endif; ?>
                  <div>
                    <div class="small fw-semibold"><?= Helper::e($b['room_name']) ?></div>
                    <div style="font-size:.75rem;" class="text-muted"><?= Helper::e($b['category_name']) ?></div>
                  </div>
                </div>
              </td>
              <td class="small"><?= Helper::formatDate($b['check_in']) ?></td>
              <td class="small"><?= Helper::formatDate($b['check_out']) ?></td>
              <td class="text-center"><span class="badge bg-secondary"><?= $b['nights'] ?>N</span></td>
              <td class="fw-bold"><?= Helper::money($b['total_amount']) ?></td>
              <td><?= Helper::bookingStatusBadge($b['status']) ?></td>
              <td>
                <div class="d-flex gap-1">
                  <a href="<?= APP_URL ?>/customer/bookings/view/<?= $b['id'] ?>" class="btn btn-sm btn-icon btn-outline-secondary" data-bs-toggle="tooltip" title="View"><i class="bi bi-eye"></i></a>
                  <?php if (in_array($b['status'],['pending','confirmed'])): ?>
                    <button class="btn btn-sm btn-icon btn-outline-danger"
                            id="cancel-booking-btn"
                            data-cancel-url="<?= APP_URL ?>/customer/bookings/cancel/<?= $b['id'] ?>"
                            data-bs-toggle="tooltip" title="Cancel">
                      <i class="bi bi-x-circle"></i>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if ($pager['pages'] > 1): ?>
      <div class="p-3 d-flex justify-content-end">
        <?= Helper::pagination($pager, APP_URL.'/customer/bookings') ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Auth::generateCsrf() ?>">
<?php include VIEWS_PATH . '/layouts/admin_footer.php'; ?>
