<?php include VIEWS_PATH . '/layouts/admin_layout.php'; ?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/admin/rooms">Rooms</a></li>
    <li class="breadcrumb-item active">Add Room</li>
  </ol>
</nav>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger alert-modern mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <div><?= implode('<br>', array_map([Helper::class,'e'], $errors)) ?></div>
  </div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>/admin/rooms/add" enctype="multipart/form-data">
  <?= $csrf ?>
  <div class="row g-4">

    <!-- Left: Basic Info -->
    <div class="col-lg-8">
      <div class="panel-card mb-4">
        <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-info-circle me-2"></i>Room Information</h6></div>
        <div class="panel-card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Room Number *</label>
              <input type="text" name="room_number" class="form-control" placeholder="e.g. 101" required value="<?= Helper::e($old['room_number'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Category *</label>
              <select name="category_id" class="form-select" required>
                <option value="">Select category…</option>
                <?php foreach ($categories as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= ($old['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                    <?= Helper::e($c['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Floor</label>
              <input type="number" name="floor" class="form-control" placeholder="1" min="1" value="<?= Helper::e($old['floor'] ?? '1') ?>">
            </div>
            <div class="col-md-8">
              <label class="form-label">Room Name *</label>
              <input type="text" name="name" class="form-control" placeholder="e.g. Deluxe Ocean Suite" required value="<?= Helper::e($old['name'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="available">Available</option>
                <option value="maintenance">Maintenance</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="4" placeholder="Describe the room…"><?= Helper::e($old['description'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Pricing & Capacity -->
      <div class="panel-card mb-4">
        <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-currency-dollar me-2"></i>Pricing & Capacity</h6></div>
        <div class="panel-card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Price Per Night ($) *</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" name="price_per_night" class="form-control" step="0.01" min="0" placeholder="0.00" required value="<?= Helper::e($old['price_per_night'] ?? '') ?>">
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Capacity (guests) *</label>
              <input type="number" name="capacity" class="form-control" min="1" max="20" placeholder="2" required value="<?= Helper::e($old['capacity'] ?? '2') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Size (sqft)</label>
              <input type="number" name="size_sqft" class="form-control" min="0" placeholder="300" value="<?= Helper::e($old['size_sqft'] ?? '') ?>">
            </div>
            <div class="col-12">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1">
                <label class="form-check-label" for="is_featured">Feature this room on homepage</label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Amenities -->
      <div class="panel-card">
        <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-stars me-2"></i>Amenities</h6></div>
        <div class="panel-card-body">
          <div class="row g-2">
            <?php foreach ($amenities as $a): ?>
              <div class="col-md-4 col-6">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="amenities[]" value="<?= $a['id'] ?>" id="am<?= $a['id'] ?>">
                  <label class="form-check-label small" for="am<?= $a['id'] ?>">
                    <i class="bi <?= Helper::e($a['icon']) ?> me-1"></i><?= Helper::e($a['name']) ?>
                  </label>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Images -->
    <div class="col-lg-4">
      <div class="panel-card mb-4">
        <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-image me-2"></i>Thumbnail</h6></div>
        <div class="panel-card-body">
          <div class="mb-3 text-center" id="thumb-preview" style="height:160px;border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
            <span class="text-muted small"><i class="bi bi-image fs-3 d-block mb-1"></i>Click to preview</span>
          </div>
          <input type="file" name="thumbnail" class="form-control" accept="image/*" onchange="previewThumb(this)">
          <small class="text-muted">Max 5MB. JPG, PNG, WebP.</small>
        </div>
      </div>

      <div class="panel-card">
        <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-images me-2"></i>Gallery Images</h6></div>
        <div class="panel-card-body">
          <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
          <small class="text-muted d-block mt-1">Select multiple images. Max 5MB each.</small>
        </div>
      </div>

      <div class="mt-4 d-grid gap-2">
        <button type="submit" class="btn btn-primary py-2 fw-bold">
          <i class="bi bi-plus-circle me-2"></i>Add Room
        </button>
        <a href="<?= APP_URL ?>/admin/rooms" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </div>
  </div>
</form>

<?php include VIEWS_PATH . '/layouts/admin_footer.php'; ?>
<script>
function previewThumb(input){
  const p = document.getElementById('thumb-preview');
  if(input.files && input.files[0]){
    const r = new FileReader();
    r.onload = e => { p.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`; };
    r.readAsDataURL(input.files[0]);
  }
}
</script>
