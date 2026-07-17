<?php
// Customer layout - same admin layout but customer-specific nav
include VIEWS_PATH . '/layouts/admin_layout.php';
?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb"><li class="breadcrumb-item active">My Dashboard</li></ol>
</nav>

<?php if (Auth::hasFlash('success')): ?>
  <div class="alert alert-success alert-modern alert-auto-dismiss mb-4">
    <i class="bi bi-check-circle-fill me-2"></i><?= Helper::e(Auth::getFlash('success')) ?>
  </div>
<?php endif; ?>

<!-- Stat Cards -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stat-card primary h-100">
      <div class="stat-card-icon"><i class="bi bi-calendar-check"></i></div>
      <div class="stat-card-value"><?= $stats['total'] ?></div>
      <div class="stat-card-label">Total Bookings</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card success h-100">
      <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
      <div class="stat-card-value"><?= $stats['confirmed'] ?></div>
      <div class="stat-card-label">Confirmed</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card warning h-100">
      <div class="stat-card-icon"><i class="bi bi-door-open"></i></div>
      <div class="stat-card-value"><?= $stats['checked_in'] ?></div>
      <div class="stat-card-label">Currently Staying</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card info h-100">
      <div class="stat-card-icon"><i class="bi bi-trophy"></i></div>
      <div class="stat-card-value"><?= $stats['completed'] ?></div>
      <div class="stat-card-label">Completed Stays</div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Recent Bookings -->
  <div class="col-lg-8">
    <div class="panel-card">
      <div class="panel-card-header">
        <h6 class="panel-card-title"><i class="bi bi-clock-history me-2"></i>Recent Bookings</h6>
        <a href="<?= APP_URL ?>/customer/bookings" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Booking</th><th>Room</th><th>Dates</th><th>Amount</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (empty($recent)): ?>
              <tr><td colspan="5" class="text-center py-4 text-muted">
                No bookings yet. <a href="<?= APP_URL ?>/rooms">Browse our rooms</a>
              </td></tr>
            <?php else: foreach ($recent as $b): ?>
              <tr>
                <td>
                  <a href="<?= APP_URL ?>/customer/bookings/view/<?= $b['id'] ?>" class="fw-semibold small text-primary"><?= Helper::e($b['booking_ref']) ?></a>
                </td>
                <td>
                  <div class="small fw-semibold"><?= Helper::e($b['room_number']) ?></div>
                  <div class="text-muted" style="font-size:.75rem;"><?= Helper::e($b['room_name']) ?></div>
                </td>
                <td class="small">
                  <?= Helper::formatDate($b['check_in'],'M d') ?> → <?= Helper::formatDate($b['check_out'],'M d, Y') ?>
                </td>
                <td class="fw-semibold small"><?= Helper::money($b['total_amount']) ?></td>
                <td><?= Helper::bookingStatusBadge($b['status']) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="col-lg-4">
    <div class="panel-card mb-4">
      <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-lightning me-2 text-warning"></i>Quick Actions</h6></div>
      <div class="panel-card-body d-grid gap-2">
        <a href="<?= APP_URL ?>/rooms" class="btn btn-primary py-2">
          <i class="bi bi-search me-2"></i>Browse & Book Rooms
        </a>
        <a href="<?= APP_URL ?>/customer/bookings" class="btn btn-outline-primary py-2">
          <i class="bi bi-calendar-check me-2"></i>My Bookings
        </a>
        <a href="<?= APP_URL ?>/customer/invoices" class="btn btn-outline-secondary py-2">
          <i class="bi bi-file-earmark-text me-2"></i>My Invoices
        </a>
        <a href="<?= APP_URL ?>/customer/profile" class="btn btn-outline-secondary py-2">
          <i class="bi bi-person-circle me-2"></i>Edit Profile
        </a>
      </div>
    </div>

    <!-- Profile Card -->
    <div class="panel-card">
      <div class="panel-card-body text-center py-4">
        <div class="mb-3">
          <?php $u = Auth::user(); ?>
          <div style="width:70px;height:70px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;margin:0 auto;border:3px solid var(--accent);">
            <?= strtoupper(substr($u['name'],0,1)) ?>
          </div>
        </div>
        <h6 class="fw-bold mb-0"><?= Helper::e($u['name']) ?></h6>
        <p class="text-muted small mb-3"><?= Helper::e($u['email']) ?></p>
        <div class="badge bg-warning text-dark px-3 py-2">
          <i class="bi bi-star-fill me-1"></i>Valued Guest
        </div>
      </div>
    </div>
  </div>
</div>

<?php include VIEWS_PATH . '/layouts/admin_footer.php'; ?>
