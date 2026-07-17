<?php include VIEWS_PATH . '/layouts/admin_layout.php'; ?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb"><li class="breadcrumb-item active">Check-In / Check-Out</li></ol>
</nav>

<div class="row g-4">
  <!-- Check-In -->
  <div class="col-lg-6">
    <div class="panel-card">
      <div class="panel-card-header" style="background:linear-gradient(135deg,#198754,#20c997);border-radius:var(--radius) var(--radius) 0 0;">
        <h6 class="panel-card-title text-white mb-0"><i class="bi bi-box-arrow-in-right me-2"></i>Today's Arrivals — Check In
          <span class="badge bg-white text-success ms-2"><?= count($arrivals) ?></span>
        </h6>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Guest</th><th>Room</th><th>Check-Out</th><th>Paid</th><th>Action</th></tr></thead>
          <tbody>
            <?php if (empty($arrivals)): ?>
              <tr><td colspan="5" class="text-center py-5 text-muted">
                <i class="bi bi-check-circle fs-2 d-block text-success mb-2"></i>
                All guests have been checked in!
              </td></tr>
            <?php else: foreach ($arrivals as $b): ?>
              <tr>
                <td>
                  <div class="fw-semibold small"><?= Helper::e($b['guest_name']) ?></div>
                  <div style="font-size:.75rem;" class="text-muted"><?= Helper::e($b['booking_ref']) ?></div>
                </td>
                <td><span class="badge bg-success"><?= Helper::e($b['room_number']) ?></span></td>
                <td class="small"><?= Helper::formatDate($b['check_out']) ?></td>
                <td><?= Helper::paymentStatusBadge($b['payment_status']) ?></td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="<?= APP_URL ?>/admin/bookings/view/<?= $b['id'] ?>" class="btn btn-sm btn-icon btn-outline-secondary" data-bs-toggle="tooltip" title="View"><i class="bi bi-eye"></i></a>
                    <form method="POST" action="<?= APP_URL ?>/admin/bookings/checkin/<?= $b['id'] ?>">
                      <?= Auth::csrfField() ?>
                      <button class="btn btn-sm btn-success"><i class="bi bi-check2 me-1"></i>Check In</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Check-Out -->
  <div class="col-lg-6">
    <div class="panel-card">
      <div class="panel-card-header" style="background:linear-gradient(135deg,#dc3545,#fd7e14);border-radius:var(--radius) var(--radius) 0 0;">
        <h6 class="panel-card-title text-white mb-0"><i class="bi bi-box-arrow-right me-2"></i>Today's Departures — Check Out
          <span class="badge bg-white text-danger ms-2"><?= count($departures) ?></span>
        </h6>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Guest</th><th>Room</th><th>Check-In Was</th><th>Amount</th><th>Action</th></tr></thead>
          <tbody>
            <?php if (empty($departures)): ?>
              <tr><td colspan="5" class="text-center py-5 text-muted">
                <i class="bi bi-check-circle fs-2 d-block text-warning mb-2"></i>
                All guests have departed!
              </td></tr>
            <?php else: foreach ($departures as $b): ?>
              <tr>
                <td>
                  <div class="fw-semibold small"><?= Helper::e($b['guest_name']) ?></div>
                  <div style="font-size:.75rem;" class="text-muted"><?= Helper::e($b['booking_ref']) ?></div>
                </td>
                <td><span class="badge bg-danger"><?= Helper::e($b['room_number']) ?></span></td>
                <td class="small"><?= Helper::formatDate($b['check_in']) ?></td>
                <td class="fw-semibold small"><?= Helper::money($b['total_amount']) ?></td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="<?= APP_URL ?>/admin/bookings/view/<?= $b['id'] ?>" class="btn btn-sm btn-icon btn-outline-secondary" data-bs-toggle="tooltip" title="View"><i class="bi bi-eye"></i></a>
                    <form method="POST" action="<?= APP_URL ?>/admin/bookings/checkout/<?= $b['id'] ?>">
                      <?= Auth::csrfField() ?>
                      <button class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-right me-1"></i>Check Out</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="mt-4 text-center">
  <a href="<?= APP_URL ?>/receptionist/walk-in" class="btn btn-primary btn-lg px-5">
    <i class="bi bi-person-plus me-2"></i>New Walk-In Booking
  </a>
</div>

<?php include VIEWS_PATH . '/layouts/admin_footer.php'; ?>
