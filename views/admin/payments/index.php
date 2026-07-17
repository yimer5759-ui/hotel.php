<?php include VIEWS_PATH . '/layouts/admin_layout.php'; ?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= APP_URL ?>/admin/payments">Payments</a></li><li class="breadcrumb-item active">All Payments</li></ol>
</nav>

<!-- Summary cards -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="stat-card accent text-center p-3">
      <div class="stat-card-value" style="font-size:1.5rem;"><?= Helper::money($pager['data'] ? array_sum(array_column(array_filter($pager['data'],fn($r)=>$r['status']==='completed'),'amount')) : 0) ?></div>
      <div class="stat-card-label">Page Revenue</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card success text-center p-3">
      <div class="stat-card-value" style="font-size:1.5rem;"><?= count(array_filter($pager['data']??[],fn($r)=>$r['status']==='completed')) ?></div>
      <div class="stat-card-label">Completed</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card warning text-center p-3">
      <div class="stat-card-value" style="font-size:1.5rem;"><?= count(array_filter($pager['data']??[],fn($r)=>$r['status']==='pending')) ?></div>
      <div class="stat-card-label">Pending</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card danger text-center p-3">
      <div class="stat-card-value" style="font-size:1.5rem;"><?= count(array_filter($pager['data']??[],fn($r)=>$r['status']==='refunded')) ?></div>
      <div class="stat-card-label">Refunded</div>
    </div>
  </div>
</div>

<!-- Filters -->
<div class="panel-card mb-4">
  <div class="panel-card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-6"><input type="text" name="search" class="form-control" placeholder="Search booking ref or guest name…" value="<?= Helper::e($search) ?>"></div>
      <div class="col-md-3">
        <select name="status" class="form-select">
          <option value="">All Statuses</option>
          <option value="completed" <?= $status==='completed'?'selected':'' ?>>Completed</option>
          <option value="pending"   <?= $status==='pending'  ?'selected':'' ?>>Pending</option>
          <option value="refunded"  <?= $status==='refunded' ?'selected':'' ?>>Refunded</option>
        </select>
      </div>
      <div class="col-md-3"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
    </form>
  </div>
</div>

<div class="panel-card">
  <div class="panel-card-header">
    <h6 class="panel-card-title"><i class="bi bi-credit-card me-2"></i>Payment Transactions</h6>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>Booking Ref</th><th>Guest</th><th>Amount</th><th>Method</th><th>Status</th><th>Transaction ID</th><th>Date</th></tr></thead>
      <tbody>
        <?php if (empty($pager['data'])): ?>
          <tr><td colspan="7" class="text-center py-5 text-muted">No payments found.</td></tr>
        <?php else: foreach ($pager['data'] as $p): ?>
          <tr>
            <td><a href="<?= APP_URL ?>/admin/bookings/view/<?= $p['booking_id'] ?>" class="fw-semibold text-primary"><?= Helper::e($p['booking_ref']) ?></a></td>
            <td class="small"><?= Helper::e($p['guest_name']) ?></td>
            <td class="fw-bold"><?= Helper::money($p['amount']) ?></td>
            <td>
              <span class="badge bg-light text-dark border">
                <i class="bi bi-<?= $p['method']==='cash'?'cash':'credit-card' ?> me-1"></i>
                <?= ucwords(str_replace('_',' ',$p['method'])) ?>
              </span>
            </td>
            <td>
              <span class="badge bg-<?= ['completed'=>'success','pending'=>'warning','failed'=>'danger','refunded'=>'secondary'][$p['status']] ?? 'secondary' ?>">
                <?= ucfirst($p['status']) ?>
              </span>
            </td>
            <td class="small text-muted"><?= Helper::e($p['transaction_id'] ?? '—') ?></td>
            <td class="small"><?= Helper::formatDate($p['paid_at'] ?? $p['created_at'],'M d, Y') ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pager['pages'] > 1): ?>
    <div class="p-3 d-flex justify-content-end">
      <?= Helper::pagination($pager, APP_URL.'/admin/payments?search='.urlencode($search).'&status='.$status) ?>
    </div>
  <?php endif; ?>
</div>

<?php include VIEWS_PATH . '/layouts/admin_footer.php'; ?>
