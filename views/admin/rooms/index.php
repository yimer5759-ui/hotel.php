<?php include VIEWS_PATH . '/layouts/admin_layout.php'; ?>

<div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/admin/dashboard">Dashboard</a></li>
      <li class="breadcrumb-item active">Rooms</li>
    </ol>
  </nav>
  <a href="<?= APP_URL ?>/admin/rooms/add" class="btn btn-primary">
    <i class="bi bi-plus-circle me-1"></i>Add Room
  </a>
</div>

<!-- Filters -->
<div class="panel-card mb-4">
  <div class="panel-card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-4">
        <input type="text" name="search" class="form-control" placeholder="Search room number or name…" value="<?= Helper::e($search) ?>">
      </div>
      <div class="col-md-3">
        <select name="category" class="form-select">
          <option value="">All Categories</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= $c['slug'] ?>" <?= $category === $c['slug'] ? 'selected' : '' ?>><?= Helper::e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <select name="status" class="form-select">
          <option value="">All Statuses</option>
          <option value="available"   <?= $status==='available'   ?'selected':'' ?>>Available</option>
          <option value="booked"      <?= $status==='booked'      ?'selected':'' ?>>Booked</option>
          <option value="maintenance" <?= $status==='maintenance' ?'selected':'' ?>>Maintenance</option>
          <option value="inactive"    <?= $status==='inactive'    ?'selected':'' ?>>Inactive</option>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
      </div>
    </form>
  </div>
</div>

<!-- Rooms Grid -->
<?php if (empty($pager['data'])): ?>
  <div class="text-center py-5 text-muted">
    <i class="bi bi-door-closed fs-1 d-block mb-2"></i>
    <p>No rooms found. <a href="<?= APP_URL ?>/admin/rooms/add">Add the first room</a>.</p>
  </div>
<?php else: ?>

<div class="row g-4">
  <?php foreach ($pager['data'] as $room): ?>
  <div class="col-xl-3 col-lg-4 col-md-6">
    <div class="card border-0 shadow-sm h-100" style="border-radius:var(--radius);overflow:hidden;">
      <!-- Thumbnail -->
      <div style="height:180px;overflow:hidden;position:relative;">
        <img src="<?= $room['thumbnail'] ? UPLOADS_URL.'/rooms/'.Helper::e($room['thumbnail']) : APP_URL.'/assets/images/room-placeholder.jpg' ?>"
             alt="<?= Helper::e($room['name']) ?>" style="width:100%;height:100%;object-fit:cover;transition:transform .4s;"
             onmouseover="this.style.transform='scale(1.06)'" onmouseout="this.style.transform='scale(1)'">
        <div style="position:absolute;top:.75rem;left:.75rem;">
          <?= Helper::roomStatusBadge($room['status']) ?>
        </div>
        <div style="position:absolute;top:.75rem;right:.75rem;background:rgba(13,31,51,.8);color:#fff;font-size:.75rem;padding:.2rem .6rem;border-radius:20px;">
          <?= Helper::e($room['category_name']) ?>
        </div>
      </div>

      <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <h6 class="fw-bold mb-0"><?= Helper::e($room['name']) ?></h6>
          <span class="badge bg-light text-dark border">
            <i class="bi bi-hash"></i><?= Helper::e($room['room_number']) ?>
          </span>
        </div>
        <div class="text-muted small mb-2">
          <i class="bi bi-building me-1"></i>Floor <?= $room['floor'] ?>
          &nbsp;·&nbsp;
          <i class="bi bi-people me-1"></i><?= $room['capacity'] ?> guests
          <?php if ($room['size_sqft']): ?>
          &nbsp;·&nbsp;
          <i class="bi bi-crop me-1"></i><?= $room['size_sqft'] ?> sqft
          <?php endif; ?>
        </div>
        <div class="fw-bold mb-3" style="color:var(--accent);font-size:1.1rem;">
          <?= Helper::money($room['price_per_night']) ?> <span class="text-muted fw-normal small">/ night</span>
        </div>
      </div>

      <div class="card-footer bg-transparent border-top-0 pt-0 p-3 d-flex gap-2">
        <a href="<?= APP_URL ?>/admin/rooms/edit/<?= $room['id'] ?>" class="btn btn-sm btn-outline-primary flex-fill">
          <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <?php if (Auth::isAdmin()): ?>
        <button class="btn btn-sm btn-outline-danger"
                data-delete-url="<?= APP_URL ?>/admin/rooms/delete/<?= $room['id'] ?>"
                data-delete-title="Delete Room?"
                data-delete-text="Room <?= Helper::e($room['room_number']) ?> will be permanently deleted."
                data-bs-toggle="tooltip" title="Delete">
          <i class="bi bi-trash"></i>
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if ($pager['pages'] > 1): ?>
  <div class="d-flex justify-content-center mt-4">
    <?= Helper::pagination($pager, APP_URL . '/admin/rooms?search=' . urlencode($search) . '&category=' . $category . '&status=' . $status) ?>
  </div>
<?php endif; ?>

<?php endif; ?>

<!-- Hidden CSRF for delete -->
<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Auth::generateCsrf() ?>">

<?php include VIEWS_PATH . '/layouts/admin_footer.php'; ?>
