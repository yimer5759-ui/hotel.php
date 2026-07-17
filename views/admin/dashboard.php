<?php include VIEWS_PATH . '/layouts/admin_layout.php'; ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item active">Dashboard</li>
  </ol>
</nav>

<!-- ── Stat Cards ────────────────────────────────────────── -->
<div class="row g-4 mb-4">
  <div class="col-xl-2 col-md-4 col-6">
    <div class="stat-card primary h-100">
      <div class="stat-card-icon"><i class="bi bi-door-closed"></i></div>
      <div class="stat-card-value"><?= $totalRooms ?></div>
      <div class="stat-card-label">Total Rooms</div>
    </div>
  </div>
  <div class="col-xl-2 col-md-4 col-6">
    <div class="stat-card success h-100">
      <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
      <div class="stat-card-value"><?= $availableRooms ?></div>
      <div class="stat-card-label">Available</div>
    </div>
  </div>
  <div class="col-xl-2 col-md-4 col-6">
    <div class="stat-card danger h-100">
      <div class="stat-card-icon"><i class="bi bi-person-check"></i></div>
      <div class="stat-card-value"><?= $occupiedRooms ?></div>
      <div class="stat-card-label">Occupied</div>
    </div>
  </div>
  <div class="col-xl-2 col-md-4 col-6">
    <div class="stat-card warning h-100">
      <div class="stat-card-icon"><i class="bi bi-tools"></i></div>
      <div class="stat-card-value"><?= $maintenanceRooms ?></div>
      <div class="stat-card-label">Maintenance</div>
    </div>
  </div>
  <div class="col-xl-2 col-md-4 col-6">
    <div class="stat-card info h-100">
      <div class="stat-card-icon"><i class="bi bi-calendar-check"></i></div>
      <div class="stat-card-value"><?= $totalBookings ?></div>
      <div class="stat-card-label">Total Bookings</div>
    </div>
  </div>
  <div class="col-xl-2 col-md-4 col-6">
    <div class="stat-card accent h-100">
      <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
      <div class="stat-card-value" style="font-size:1.4rem;"><?= Helper::money($totalRevenue) ?></div>
      <div class="stat-card-label">Total Revenue</div>
    </div>
  </div>
</div>

<!-- ── Charts ─────────────────────────────────────────────── -->
<div class="row g-4 mb-4">
  <!-- Revenue Chart -->
  <div class="col-xl-8">
    <div class="panel-card h-100">
      <div class="panel-card-header">
        <h6 class="panel-card-title"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Revenue & Bookings (<?= date('Y') ?>)</h6>
        <span class="badge bg-primary-subtle text-primary">Monthly</span>
      </div>
      <div class="panel-card-body">
        <canvas id="revenueChart" height="100"></canvas>
      </div>
    </div>
  </div>

  <!-- Room Status Doughnut -->
  <div class="col-xl-4">
    <div class="panel-card h-100">
      <div class="panel-card-header">
        <h6 class="panel-card-title"><i class="bi bi-pie-chart me-2 text-success"></i>Room Status</h6>
      </div>
      <div class="panel-card-body d-flex flex-column align-items-center justify-content-center">
        <canvas id="roomStatusChart" height="220"></canvas>
        <div class="d-flex gap-3 mt-3 flex-wrap justify-content-center">
          <div class="text-center">
            <div class="fw-bold text-success"><?= $availableRooms ?></div>
            <div class="small text-muted">Available</div>
          </div>
          <div class="text-center">
            <div class="fw-bold text-danger"><?= $occupiedRooms ?></div>
            <div class="small text-muted">Occupied</div>
          </div>
          <div class="text-center">
            <div class="fw-bold text-warning"><?= $maintenanceRooms ?></div>
            <div class="small text-muted">Maintenance</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Today's Arrivals & Departures ──────────────────────── -->
<div class="row g-4 mb-4">
  <div class="col-lg-6">
    <div class="panel-card">
      <div class="panel-card-header">
        <h6 class="panel-card-title"><i class="bi bi-box-arrow-in-right me-2 text-success"></i>Today's Arrivals
          <span class="badge bg-success ms-2"><?= count($todayArrivals) ?></span>
        </h6>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Guest</th><th>Room</th><th>Action</th></tr></thead>
          <tbody>
            <?php if (empty($todayArrivals)): ?>
              <tr><td colspan="3" class="text-center text-muted py-4">No arrivals today</td></tr>
            <?php else: foreach ($todayArrivals as $b): ?>
              <tr>
                <td><div class="fw-semibold small"><?= Helper::e($b['guest_name']) ?></div></td>
                <td><span class="badge bg-primary-subtle text-primary"><?= Helper::e($b['room_number']) ?></span></td>
                <td>
                  <form method="POST" action="<?= APP_URL ?>/admin/bookings/checkin/<?= $b['id'] ?>" class="d-inline">
                    <?= Auth::csrfField() ?>
                    <button class="btn btn-sm btn-success py-0">Check In</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="panel-card">
      <div class="panel-card-header">
        <h6 class="panel-card-title"><i class="bi bi-box-arrow-right me-2 text-warning"></i>Today's Departures
          <span class="badge bg-warning text-dark ms-2"><?= count($todayDepartures) ?></span>
        </h6>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Guest</th><th>Room</th><th>Action</th></tr></thead>
          <tbody>
            <?php if (empty($todayDepartures)): ?>
              <tr><td colspan="3" class="text-center text-muted py-4">No departures today</td></tr>
            <?php else: foreach ($todayDepartures as $b): ?>
              <tr>
                <td><div class="fw-semibold small"><?= Helper::e($b['guest_name']) ?></div></td>
                <td><span class="badge bg-warning text-dark"><?= Helper::e($b['room_number']) ?></span></td>
                <td>
                  <form method="POST" action="<?= APP_URL ?>/admin/bookings/checkout/<?= $b['id'] ?>" class="d-inline">
                    <?= Auth::csrfField() ?>
                    <button class="btn btn-sm btn-warning py-0">Check Out</button>
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

<!-- ── Recent Bookings ─────────────────────────────────────── -->
<div class="panel-card">
  <div class="panel-card-header">
    <h6 class="panel-card-title"><i class="bi bi-clock-history me-2 text-info"></i>Recent Bookings</h6>
    <a href="<?= APP_URL ?>/admin/bookings" class="btn btn-sm btn-outline-primary">View All</a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Ref #</th><th>Guest</th><th>Room</th><th>Check-In</th>
          <th>Check-Out</th><th>Amount</th><th>Status</th><th>Payment</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentBookings as $b): ?>
          <tr>
            <td><a href="<?= APP_URL ?>/admin/bookings/view/<?= $b['id'] ?>" class="fw-semibold small text-primary"><?= Helper::e($b['booking_ref']) ?></a></td>
            <td class="small"><?= Helper::e($b['guest_name']) ?></td>
            <td><span class="badge bg-secondary"><?= Helper::e($b['room_number']) ?></span></td>
            <td class="small"><?= Helper::formatDate($b['check_in']) ?></td>
            <td class="small"><?= Helper::formatDate($b['check_out']) ?></td>
            <td class="fw-semibold small"><?= Helper::money($b['total_amount']) ?></td>
            <td><?= Helper::bookingStatusBadge($b['status']) ?></td>
            <td><?= Helper::paymentStatusBadge($b['payment_status']) ?></td>
            <td>
              <a href="<?= APP_URL ?>/admin/bookings/view/<?= $b['id'] ?>" class="btn btn-sm btn-icon btn-outline-secondary" data-bs-toggle="tooltip" title="View">
                <i class="bi bi-eye"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include VIEWS_PATH . '/layouts/admin_footer.php'; ?>

<script>
// Monthly Revenue + Bookings Chart
const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const revenue  = <?= json_encode(array_values($monthlyRevenue))  ?>;
const bookings = <?= json_encode(array_values($monthlyBookings)) ?>;

new Chart(document.getElementById('revenueChart'), {
  type: 'bar',
  data: {
    labels: months,
    datasets: [
      {
        label: 'Revenue ($)',
        data: revenue,
        backgroundColor: 'rgba(26,60,94,.75)',
        borderRadius: 6,
        yAxisID: 'y',
      },
      {
        label: 'Bookings',
        data: bookings,
        type: 'line',
        borderColor: '#c9a84c',
        backgroundColor: 'rgba(201,168,76,.15)',
        pointBackgroundColor: '#c9a84c',
        tension: 0.4,
        fill: true,
        yAxisID: 'y1',
      },
    ],
  },
  options: {
    responsive: true,
    interaction: { mode: 'index', intersect: false },
    plugins: { legend: { position: 'top' } },
    scales: {
      y:  { type: 'linear', display: true, position: 'left',  grid: { color: '#e2e8f0' } },
      y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false } },
    },
  },
});

// Room Status Doughnut
new Chart(document.getElementById('roomStatusChart'), {
  type: 'doughnut',
  data: {
    labels: ['Available','Occupied','Maintenance'],
    datasets: [{
      data: [<?= $availableRooms ?>, <?= $occupiedRooms ?>, <?= $maintenanceRooms ?>],
      backgroundColor: ['#10b981','#ef4444','#f59e0b'],
      borderWidth: 0,
    }],
  },
  options: {
    responsive: true,
    cutout: '70%',
    plugins: { legend: { display: false } },
  },
});
</script>
