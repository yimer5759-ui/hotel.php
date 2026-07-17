<?php include VIEWS_PATH . '/layouts/admin_layout.php'; ?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb"><li class="breadcrumb-item active">Reviews & Ratings</li></ol>
</nav>

<!-- Filter -->
<div class="panel-card mb-4">
  <div class="panel-card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-4">
        <select name="status" class="form-select">
          <option value="">All Statuses</option>
          <option value="pending"  <?= $status==='pending' ?'selected':'' ?>>Pending</option>
          <option value="approved" <?= $status==='approved'?'selected':'' ?>>Approved</option>
          <option value="rejected" <?= $status==='rejected'?'selected':'' ?>>Rejected</option>
        </select>
      </div>
      <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
    </form>
  </div>
</div>

<div class="panel-card">
  <div class="panel-card-header">
    <h6 class="panel-card-title"><i class="bi bi-star me-2 text-warning"></i>Reviews <span class="badge bg-primary ms-1"><?= $pager['total'] ?></span></h6>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>Guest</th><th>Room</th><th>Rating</th><th>Review</th><th>Booking</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($pager['data'])): ?>
          <tr><td colspan="8" class="text-center py-5 text-muted">No reviews found.</td></tr>
        <?php else: foreach ($pager['data'] as $r): ?>
          <tr>
            <td class="small fw-semibold"><?= Helper::e($r['guest_name']) ?></td>
            <td class="small"><?= Helper::e($r['room_number']) ?> – <?= Helper::e($r['room_name']) ?></td>
            <td>
              <span class="text-warning">
                <?= str_repeat('★', $r['rating']) ?><?= str_repeat('☆', 5 - $r['rating']) ?>
              </span>
            </td>
            <td style="max-width:220px;">
              <div class="fw-semibold small"><?= Helper::e($r['title']) ?></div>
              <div class="text-muted small"><?= Helper::truncate(Helper::e($r['body']), 80) ?></div>
            </td>
            <td><a href="<?= APP_URL ?>/admin/bookings/view/<?= $r['booking_id'] ?>" class="small text-primary"><?= Helper::e($r['booking_ref']) ?></a></td>
            <td class="small"><?= Helper::formatDate($r['created_at']) ?></td>
            <td>
              <span class="badge bg-<?= ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$r['status']] ?>">
                <?= ucfirst($r['status']) ?>
              </span>
            </td>
            <td>
              <div class="d-flex gap-1">
                <?php if ($r['status'] !== 'approved'): ?>
                  <button class="btn btn-sm btn-icon btn-success" data-action-url="<?= APP_URL ?>/admin/reviews/approve/<?= $r['id'] ?>" data-bs-toggle="tooltip" title="Approve"><i class="bi bi-check2"></i></button>
                <?php endif; ?>
                <?php if ($r['status'] !== 'rejected'): ?>
                  <button class="btn btn-sm btn-icon btn-warning" data-action-url="<?= APP_URL ?>/admin/reviews/reject/<?= $r['id'] ?>" data-bs-toggle="tooltip" title="Reject"><i class="bi bi-x"></i></button>
                <?php endif; ?>
                <button class="btn btn-sm btn-icon btn-outline-danger"
                        data-delete-url="<?= APP_URL ?>/admin/reviews/delete/<?= $r['id'] ?>"
                        data-delete-title="Delete Review?"
                        data-bs-toggle="tooltip" title="Delete"><i class="bi bi-trash"></i></button>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pager['pages'] > 1): ?>
    <div class="p-3 d-flex justify-content-end">
      <?= Helper::pagination($pager, APP_URL.'/admin/reviews?status='.$status) ?>
    </div>
  <?php endif; ?>
</div>

<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Auth::generateCsrf() ?>">
<?php include VIEWS_PATH . '/layouts/admin_footer.php'; ?>
