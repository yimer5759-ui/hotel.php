<?php include VIEWS_PATH . '/layouts/admin_layout.php'; ?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb"><li class="breadcrumb-item active">Receptionist Dashboard</li></ol>
</nav>

<!-- Stats -->
<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="stat-card success h-100"><div class="stat-card-icon"><i class="bi bi-check-circle"></i></div><div class="stat-card-value"><?= $roomStats['available'] ?? 0 ?></div><div class="stat-card-label">Available Rooms</div></div></div>
  <div class="col-md-3"><div class="stat-card danger h-100"><div class="stat-card-icon"><i class="bi bi-person-check"></i></div><div class="stat-card-value"><?= $roomStats['booked'] ?? 0 ?></div><div class="stat-card-label">Occupied Rooms</div></div></div>
  <div class="col-md-3"><div class="stat-card primary h-100"><div class="stat-card-icon"><i class="bi bi-box-arrow-in-right"></i></div><div class="stat-card-value"><?= count($arrivals) ?></div><div class="stat-card-label">Today's Arrivals</div></div></div>
  <div class="col-md-3"><div class="stat-card warning h-100"><div class="stat-card-icon"><i class="bi bi-box-arrow-right"></i></div><div class="stat-card-value"><?= count($departures) ?></div><div class="stat-card-label">Today's Departures</div></div></div>
</div>

<div class="row g-4">
  <!-- Quick Actions -->
  <div class="col-lg-4">
    <div class="panel-card mb-4">
      <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-lightning me-2 text-warning"></i>Quick Actions</h6></div>
      <div class="panel-card-body d-grid gap-2">
        <a href="<?= APP_URL ?>/receptionist/checkin" class="btn btn-primary py-2"><i class="bi bi-door-open me-2"></i>Check-In / Check-Out Panel</a>
        <a href="<?= APP_URL ?>/receptionist/walk-in" class="btn btn-outline-primary py-2"><i class="bi bi-person-plus me-2"></i>New Walk-In Booking</a>
        <a href="<?= APP_URL ?>/receptionist/bookings" class="btn btn-outline-secondary py-2"><i class="bi bi-calendar-check me-2"></i>All Bookings</a>
        <a href="<?= APP_URL ?>/rooms" class="btn btn-outline-secondary py-2" target="_blank"><i class="bi bi-globe me-2"></i>View Rooms (Website)</a>
      </div>
    </div>
  </div>

  <!-- Today's Arrivals -->
  <div class="col-lg-8">
    <div class="panel-card">
      <div class="panel-card-header">
        <h6 class="panel-card-title"><i class="bi bi-box-arrow-in-right me-2 text-success"></i>Today's Arrivals (<?= date('M d, Y') ?>)</h6>
        <a href="<?= APP_URL ?>/receptionist/checkin" class="btn btn-sm btn-outline-success">View All</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Guest</th><th>Room</th><th>Nights</th><th>Amount</th><th>Action</th></tr></thead>
          <tbody>
            <?php if (empty($arrivals)): ?>
              <tr><td colspan="5" class="text-center py-4 text-muted">No arrivals today</td></tr>
            <?php else: foreach ($arrivals as $b): ?>
              <tr>
                <td class="fw-semibold small"><?= Helper::e($b['guest_name']) ?></td>
                <td><span class="badge bg-primary"><?= Helper::e($b['room_number']) ?></span></td>
                <td><?= $b['nights'] ?>N</td>
                <td class="fw-semibold"><?= Helper::money($b['total_amount']) ?></td>
                <td>
                  <form method="POST" action="<?= APP_URL ?>/admin/bookings/checkin/<?= $b['id'] ?>">
                    <?= Auth::csrfField() ?>
                    <button class="btn btn-sm btn-success"><i class="bi bi-check me-1"></i>Check In</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include VIEWS_PATH . '/layouts/admin_footer.php'; ?>
