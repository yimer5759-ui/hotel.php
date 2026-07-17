<?php include VIEWS_PATH . '/layouts/admin_layout.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/customer/bookings">My Bookings</a></li>
      <li class="breadcrumb-item active"><?= Helper::e($booking['booking_ref']) ?></li>
    </ol>
  </nav>
  <?php if ($booking['invoice']): ?>
    <div class="d-flex gap-2">
      <a href="<?= APP_URL ?>/invoices/view/<?= $booking['invoice']['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark-text me-1"></i>Invoice</a>
      <a href="<?= APP_URL ?>/invoices/print/<?= $booking['invoice']['id'] ?>" class="btn btn-sm btn-outline-dark" target="_blank"><i class="bi bi-printer me-1"></i>Print</a>
    </div>
  <?php endif; ?>
</div>

<!-- Status banner -->
<div class="alert alert-<?= ['pending'=>'warning','confirmed'=>'primary','checked_in'=>'success','checked_out'=>'info','cancelled'=>'danger'][$booking['status']]??'secondary' ?> alert-modern mb-4">
  <i class="bi bi-info-circle-fill me-2"></i>
  Booking <strong><?= Helper::e($booking['booking_ref']) ?></strong> is currently
  <strong><?= ucwords(str_replace('_',' ',$booking['status'])) ?></strong>.
  <?= Helper::paymentStatusBadge($booking['payment_status']) ?>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <!-- Stay Details -->
    <div class="panel-card mb-4">
      <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-calendar3 me-2"></i>Stay Details</h6></div>
      <div class="panel-card-body">
        <div class="row text-center g-3">
          <div class="col-4">
            <div class="text-muted small">Check-In</div>
            <div class="fw-bold" style="color:var(--primary)"><?= Helper::formatDate($booking['check_in'],'D, M d Y') ?></div>
          </div>
          <div class="col-4">
            <div class="badge bg-primary px-3 py-2"><?= $booking['nights'] ?> Night<?= $booking['nights']>1?'s':'' ?></div>
            <div class="text-muted small mt-1"><?= $booking['guests'] ?> Guest<?= $booking['guests']>1?'s':'' ?></div>
          </div>
          <div class="col-4">
            <div class="text-muted small">Check-Out</div>
            <div class="fw-bold" style="color:var(--danger)"><?= Helper::formatDate($booking['check_out'],'D, M d Y') ?></div>
          </div>
        </div>
        <?php if ($booking['special_requests']): ?>
          <div class="mt-3 p-3 rounded-3 bg-light">
            <strong class="small">Special Requests:</strong>
            <p class="small mb-0 mt-1"><?= Helper::e($booking['special_requests']) ?></p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Room -->
    <div class="panel-card mb-4">
      <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-door-closed me-2"></i>Your Room</h6></div>
      <div class="panel-card-body">
        <div class="d-flex gap-3 align-items-center">
          <?php if ($booking['thumbnail']): ?>
            <img src="<?= UPLOADS_URL ?>/rooms/<?= Helper::e($booking['thumbnail']) ?>" style="width:100px;height:75px;object-fit:cover;border-radius:10px;">
          <?php endif; ?>
          <div>
            <h6 class="mb-1"><?= Helper::e($booking['room_name']) ?></h6>
            <p class="text-muted small mb-1"><?= Helper::e($booking['category_name']) ?> — Room #<?= Helper::e($booking['room_number']) ?></p>
            <span class="badge bg-light text-dark border"><?= Helper::money($booking['room_rate']) ?>/night</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="d-flex flex-wrap gap-2">
      <?php if (in_array($booking['status'],['pending','confirmed'])): ?>
        <button class="btn btn-outline-danger" id="cancel-booking-btn"
                data-cancel-url="<?= APP_URL ?>/customer/bookings/cancel/<?= $booking['id'] ?>">
          <i class="bi bi-x-circle me-1"></i>Cancel Booking
        </button>
      <?php endif; ?>
      <?php if ($booking['status'] === 'checked_out' && empty($booking['reviews'])): ?>
        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#reviewModal">
          <i class="bi bi-star me-1"></i>Leave a Review
        </button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Price Summary -->
  <div class="col-lg-4">
    <div class="panel-card">
      <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-receipt me-2"></i>Price Summary</h6></div>
      <div class="panel-card-body">
        <div class="d-flex justify-content-between small mb-2">
          <span>Room Rate</span><span><?= Helper::money($booking['room_rate']) ?>/night</span>
        </div>
        <div class="d-flex justify-content-between small mb-2">
          <span><?= $booking['nights'] ?> Nights</span><span><?= Helper::money($booking['subtotal']) ?></span>
        </div>
        <?php if ($booking['discount'] > 0): ?>
          <div class="d-flex justify-content-between small mb-2 text-success">
            <span>Discount</span><span>-<?= Helper::money($booking['discount']) ?></span>
          </div>
        <?php endif; ?>
        <div class="d-flex justify-content-between small mb-2">
          <span>Tax (<?= $booking['tax_rate'] ?>%)</span><span><?= Helper::money($booking['tax_amount']) ?></span>
        </div>
        <?php foreach ($booking['services'] as $s): ?>
          <div class="d-flex justify-content-between small mb-2 text-muted">
            <span><?= Helper::e($s['service']) ?></span><span><?= Helper::money($s['total']) ?></span>
          </div>
        <?php endforeach; ?>
        <hr>
        <div class="d-flex justify-content-between fw-bold">
          <span>Total</span>
          <span style="color:var(--accent);font-size:1.15rem;"><?= Helper::money($booking['total_amount']) ?></span>
        </div>
        <div class="mt-3"><?= Helper::paymentStatusBadge($booking['payment_status']) ?></div>
      </div>
    </div>
  </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--radius);">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-star me-2 text-warning"></i>Leave a Review</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="<?= APP_URL ?>/customer/reviews/<?= $booking['id'] ?>">
          <?= Auth::csrfField() ?>
          <div class="mb-3 text-center">
            <label class="form-label">Your Rating</label>
            <div class="star-rating justify-content-center">
              <?php for ($i=5;$i>=1;$i--): ?>
                <input type="radio" name="rating" id="star<?=$i?>" value="<?=$i?>">
                <label for="star<?=$i?>">★</label>
              <?php endfor; ?>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Review Title</label>
            <input type="text" name="title" class="form-control" placeholder="Summarize your experience" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Your Review</label>
            <textarea name="body" class="form-control" rows="4" placeholder="Tell us about your stay…" required></textarea>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-warning fw-bold">Submit Review</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Auth::generateCsrf() ?>">
<?php include VIEWS_PATH . '/layouts/admin_footer.php'; ?>
