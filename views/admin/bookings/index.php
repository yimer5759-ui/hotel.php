<?php include VIEWS_PATH . '/layouts/admin_layout.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/admin/bookings">Bookings</a></li>
      <li class="breadcrumb-item active">All Bookings</li>
    </ol>
  </nav>
  <a href="<?= APP_URL ?>/receptionist/walk-in" class="btn btn-primary">
    <i class="bi bi-person-plus me-1"></i>Walk-In Booking
  </a>
</div>

<!-- Filters -->
<div class="panel-card mb-4">
  <div class="panel-card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-5">
        <input type="text" name="search" class="form-control" placeholder="Search booking ref, guest name, room…" value="<?= Helper::e($search) ?>">
      </div>
      <div class="col-md-4">
        <select name="status" class="form-select">
          <option value="">All Statuses</option>
          <?php foreach(['pending','confirmed','checked_in','checked_out','cancelled','no_show'] as $s): ?>
            <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Search</button>
      </div>
    </form>
  </div>
</div>

<!-- Bookings Table -->
<div class="panel-card">
  <div class="panel-card-header">
    <h6 class="panel-card-title"><i class="bi bi-calendar-check me-2"></i>Bookings <span class="badge bg-primary ms-1"><?= $pager['total'] ?></span></h6>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Ref #</th><th>Guest</th><th>Room</th><th>Check-In</th>
          <th>Check-Out</th><th>Nights</th><th>Amount</th>
          <th>Status</th><th>Payment</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($pager['data'])): ?>
          <tr><td colspan="10" class="text-center py-5 text-muted">No bookings found.</td></tr>
        <?php else: foreach ($pager['data'] as $b): ?>
          <tr>
            <td><a href="<?= APP_URL ?>/admin/bookings/view/<?= $b['id'] ?>" class="fw-semibold text-primary"><?= Helper::e($b['booking_ref']) ?></a></td>
            <td>
              <div class="fw-semibold small"><?= Helper::e($b['guest_name']) ?></div>
              <div class="text-muted" style="font-size:.75rem;"><?= Helper::e($b['guest_email']) ?></div>
            </td>
            <td>
              <div class="fw-semibold small"><?= Helper::e($b['room_number']) ?></div>
              <div class="text-muted" style="font-size:.75rem;"><?= Helper::e($b['category_name']) ?></div>
            </td>
            <td class="small"><?= Helper::formatDate($b['check_in']) ?></td>
            <td class="small"><?= Helper::formatDate($b['check_out']) ?></td>
            <td class="text-center"><span class="badge bg-secondary"><?= $b['nights'] ?>N</span></td>
            <td class="fw-semibold small"><?= Helper::money($b['total_amount']) ?></td>
            <td><?= Helper::bookingStatusBadge($b['status']) ?></td>
            <td><?= Helper::paymentStatusBadge($b['payment_status']) ?></td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= APP_URL ?>/admin/bookings/view/<?= $b['id'] ?>" class="btn btn-sm btn-icon btn-outline-secondary" data-bs-toggle="tooltip" title="View"><i class="bi bi-eye"></i></a>
                <?php if ($b['status'] === 'pending'): ?>
                  <form method="POST" action="<?= APP_URL ?>/admin/bookings/confirm/<?= $b['id'] ?>">
                    <?= Auth::csrfField() ?>
                    <button class="btn btn-sm btn-icon btn-success" data-bs-toggle="tooltip" title="Confirm"><i class="bi bi-check2"></i></button>
                  </form>
                <?php endif; ?>
                <?php if ($b['status'] === 'confirmed'): ?>
                  <form method="POST" action="<?= APP_URL ?>/admin/bookings/checkin/<?= $b['id'] ?>">
                    <?= Auth::csrfField() ?>
                    <button class="btn btn-sm btn-icon btn-primary" data-bs-toggle="tooltip" title="Check In"><i class="bi bi-box-arrow-in-right"></i></button>
                  </form>
                <?php endif; ?>
                <?php if ($b['status'] === 'checked_in'): ?>
                  <form method="POST" action="<?= APP_URL ?>/admin/bookings/checkout/<?= $b['id'] ?>">
                    <?= Auth::csrfField() ?>
                    <button class="btn btn-sm btn-icon btn-warning" data-bs-toggle="tooltip" title="Check Out"><i class="bi bi-box-arrow-right"></i></button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pager['pages'] > 1): ?>
    <div class="p-3 d-flex justify-content-end">
      <?= Helper::pagination($pager, APP_URL . '/admin/bookings?search='.urlencode($search).'&status='.$status) ?>
    </div>
  <?php endif; ?>
</div>

<?php include VIEWS_PATH . '/layouts/admin_footer.php'; ?>
