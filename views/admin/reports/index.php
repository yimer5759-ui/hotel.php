<?php include VIEWS_PATH . '/layouts/admin_layout.php'; ?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb"><li class="breadcrumb-item active">Reports</li></ol>
</nav>

<!-- Stats -->
<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="stat-card accent p-3 text-center"><div class="stat-card-value" style="font-size:1.4rem;"><?= Helper::money($totalRevenue) ?></div><div class="stat-card-label">Total Revenue</div></div></div>
  <div class="col-md-3"><div class="stat-card primary p-3 text-center"><div class="stat-card-value"><?= $totalBookings ?></div><div class="stat-card-label">Total Bookings</div></div></div>
  <div class="col-md-3"><div class="stat-card success p-3 text-center"><div class="stat-card-value"><?= $roomStats['available'] ?? 0 ?></div><div class="stat-card-label">Available Rooms</div></div></div>
  <div class="col-md-3"><div class="stat-card danger p-3 text-center"><div class="stat-card-value"><?= $roomStats['booked'] ?? 0 ?></div><div class="stat-card-label">Occupied Rooms</div></div></div>
</div>

<!-- Filters -->
<div class="panel-card mb-4">
  <div class="panel-card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-2">
        <label class="form-label small">Year</label>
        <select name="year" class="form-select">
          <?php for ($y = date('Y'); $y >= date('Y')-3; $y--): ?>
            <option value="<?= $y ?>" <?= $year==$y?'selected':'' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Start Date</label>
        <input type="date" name="start" class="form-control" value="<?= $start ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small">End Date</label>
        <input type="date" name="end" class="form-control" value="<?= $end ?>">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Generate</button>
      </div>
    </form>
  </div>
</div>

<div class="row g-4">
  <!-- Monthly Revenue Chart -->
  <div class="col-lg-8">
    <div class="panel-card">
      <div class="panel-card-header">
        <h6 class="panel-card-title"><i class="bi bi-bar-chart me-2 text-primary"></i>Monthly Revenue (<?= $year ?>)</h6>
      </div>
      <div class="panel-card-body"><canvas id="monthlyChart" height="200"></canvas></div>
    </div>
  </div>

  <!-- Period Chart -->
  <div class="col-lg-4">
    <div class="panel-card">
      <div class="panel-card-header">
        <h6 class="panel-card-title"><i class="bi bi-graph-up me-2 text-success"></i>Period Revenue</h6>
      </div>
      <div class="panel-card-body"><canvas id="periodChart" height="200"></canvas></div>
    </div>
  </div>
</div>

<!-- Monthly table -->
<div class="panel-card mt-4">
  <div class="panel-card-header">
    <h6 class="panel-card-title"><i class="bi bi-table me-2"></i>Monthly Breakdown (<?= $year ?>)</h6>
  </div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead><tr><th>Month</th><th>Bookings</th><th>Revenue</th><th>Avg / Booking</th></tr></thead>
      <tbody>
        <?php
        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $byMonth = [];
        foreach ($revenueByMonth as $r) $byMonth[(int)$r['month']] = $r;
        for ($m = 1; $m <= 12; $m++):
          $row = $byMonth[$m] ?? ['bookings'=>0,'revenue'=>0];
          $avg = $row['bookings'] > 0 ? $row['revenue']/$row['bookings'] : 0;
        ?>
        <tr>
          <td><?= $months[$m-1] ?></td>
          <td><?= $row['bookings'] ?></td>
          <td class="fw-semibold"><?= Helper::money($row['revenue']) ?></td>
          <td class="text-muted"><?= Helper::money($avg) ?></td>
        </tr>
        <?php endfor; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include VIEWS_PATH . '/layouts/admin_footer.php'; ?>
<script>
const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const mRevenue  = <?= json_encode(array_map(fn($m)=>$byMonth[$m]['revenue']??0, range(1,12))) ?>;
const mBookings = <?= json_encode(array_map(fn($m)=>$byMonth[$m]['bookings']??0, range(1,12))) ?>;

new Chart(document.getElementById('monthlyChart'),{
  type:'bar',
  data:{
    labels:months,
    datasets:[
      {label:'Revenue ($)',data:mRevenue,backgroundColor:'rgba(26,60,94,.75)',borderRadius:6,yAxisID:'y'},
      {label:'Bookings',data:mBookings,type:'line',borderColor:'#c9a84c',tension:.4,pointBackgroundColor:'#c9a84c',yAxisID:'y1'},
    ]
  },
  options:{responsive:true,interaction:{mode:'index',intersect:false},scales:{y:{position:'left'},y1:{position:'right',grid:{drawOnChartArea:false}}}}
});

const pDates   = <?= json_encode(array_column($revenueByPeriod,'day')) ?>;
const pRevenue = <?= json_encode(array_column($revenueByPeriod,'revenue')) ?>;
new Chart(document.getElementById('periodChart'),{
  type:'line',
  data:{labels:pDates,datasets:[{label:'Revenue',data:pRevenue,borderColor:'#10b981',backgroundColor:'rgba(16,185,129,.1)',tension:.4,fill:true}]},
  options:{responsive:true,plugins:{legend:{display:false}}}
});
</script>
