<?php include VIEWS_PATH . '/layouts/admin_layout.php'; ?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb"><li class="breadcrumb-item active">Customers</li></ol>
</nav>

<div class="panel-card mb-4">
  <div class="panel-card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-8">
        <input type="text" name="search" class="form-control" placeholder="Search by name or email…" value="<?= Helper::e($search) ?>">
      </div>
      <div class="col-md-4">
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Search</button>
      </div>
    </form>
  </div>
</div>

<div class="panel-card">
  <div class="panel-card-header">
    <h6 class="panel-card-title"><i class="bi bi-people me-2"></i>Customers <span class="badge bg-primary ms-1"><?= $pager['total'] ?></span></h6>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr><th>Customer</th><th>Email</th><th>Phone</th><th>Total Bookings</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($pager['data'])): ?>
          <tr><td colspan="7" class="text-center py-5 text-muted">No customers found.</td></tr>
        <?php else: foreach ($pager['data'] as $u): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <?php if ($u['avatar']): ?>
                  <img src="<?= UPLOADS_URL ?>/avatars/<?= Helper::e($u['avatar']) ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                <?php else: ?>
                  <div style="width:36px;height:36px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;">
                    <?= strtoupper(substr($u['first_name'],0,1)) ?>
                  </div>
                <?php endif; ?>
                <div>
                  <div class="fw-semibold small"><?= Helper::e($u['first_name'].' '.$u['last_name']) ?></div>
                </div>
              </div>
            </td>
            <td class="small"><?= Helper::e($u['email']) ?></td>
            <td class="small"><?= Helper::e($u['phone'] ?? '—') ?></td>
            <td class="text-center"><span class="badge bg-info text-dark"><?= $u['total_bookings'] ?></span></td>
            <td>
              <span class="badge bg-<?= $u['status']==='active'?'success':($u['status']==='banned'?'danger':'secondary') ?>">
                <?= ucfirst($u['status']) ?>
              </span>
            </td>
            <td class="small"><?= Helper::formatDate($u['created_at']) ?></td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= APP_URL ?>/admin/customers/<?= $u['id'] ?>" class="btn btn-sm btn-icon btn-outline-secondary" data-bs-toggle="tooltip" title="View"><i class="bi bi-eye"></i></a>
                <button class="btn btn-sm btn-icon btn-<?= $u['status']==='active'?'outline-danger':'outline-success' ?>"
                        onclick="toggleStatus(<?= $u['id'] ?>, this)"
                        data-bs-toggle="tooltip" title="<?= $u['status']==='active'?'Ban':'Activate' ?>">
                  <i class="bi bi-<?= $u['status']==='active'?'slash-circle':'check-circle' ?>"></i>
                </button>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pager['pages'] > 1): ?>
    <div class="p-3 d-flex justify-content-end">
      <?= Helper::pagination($pager, APP_URL . '/admin/customers?search='.urlencode($search)) ?>
    </div>
  <?php endif; ?>
</div>

<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" id="csrf" value="<?= Auth::generateCsrf() ?>">

<?php include VIEWS_PATH . '/layouts/admin_footer.php'; ?>
<script>
async function toggleStatus(id, btn) {
  const res = await postJSON(`<?= APP_URL ?>/admin/customers/${id}/toggle`);
  if (res.success) location.reload();
}
</script>
