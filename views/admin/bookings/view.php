<?php include VIEWS_PATH . '/layouts/admin_layout.php'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/admin/bookings">Bookings</a></li>
      <li class="breadcrumb-item active"><?= Helper::e($booking['booking_ref']) ?></li>
    </ol>
  </nav>
  <div class="d-flex gap-2">
    <?php $inv = $booking['invoice']; ?>
    <?php if ($inv): ?>
      <a href="<?= APP_URL ?>/invoices/view/<?= $inv['id'] ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-earmark-text me-1"></i>Invoice</a>
      <a href="<?= APP_URL ?>/invoices/print/<?= $inv['id'] ?>" class="btn btn-outline-dark btn-sm" target="_blank"><i class="bi bi-printer me-1"></i>Print</a>
    <?php endif; ?>
  </div>
</div>

<div class="row g-4">
  <!-- Booking Details -->
  <div class="col-lg-8">
    <div class="panel-card mb-4">
      <div class="panel-card-header">
        <div>
          <h6 class="panel-card-title mb-0">Booking: <strong><?= Helper::e($booking['booking_ref']) ?></strong></h6>
          <small class="text-muted">Created <?= Helper::formatDate($booking['created_at'], 'M d, Y g:i A') ?></small>
        </div>
        <div class="d-flex gap-2">
          <?= Helper::bookingStatusBadge($booking['status']) ?>
          <?= Helper::paymentStatusBadge($booking['payment_status']) ?>
        </div>
      </div>
      <div class="panel-card-body">
        <div class="row g-3">
          <div class="col-md-3 text-center">
            <div class="text-muted small">Check-In</div>
            <div class="fw-bold fs-5" style="color:var(--primary)"><?= Helper::formatDate($booking['check_in']) ?></div>
            <div class="small text-muted">After 14:00</div>
          </div>
          <div class="col-md-3 text-center">
            <div class="bg-primary text-white rounded-pill px-3 py-2 d-inline-block">
              <div class="small">Duration</div>
              <div class="fw-bold"><?= $booking['nights'] ?> Night<?= $booking['nights']>1?'s':'' ?></div>
            </div>
          </div>
          <div class="col-md-3 text-center">
            <div class="text-muted small">Check-Out</div>
            <div class="fw-bold fs-5" style="color:var(--danger)"><?= Helper::formatDate($booking['check_out']) ?></div>
            <div class="small text-muted">Before 11:00</div>
          </div>
          <div class="col-md-3 text-center">
            <div class="text-muted small">Guests</div>
            <div class="fw-bold fs-5"><?= $booking['guests'] ?></div>
            <div class="small text-muted">Person<?= $booking['guests']>1?'s':'' ?></div>
          </div>
        </div>

        <?php if ($booking['special_requests']): ?>
          <div class="mt-3 p-3 rounded-3" style="background:#fff8e1;border:1px solid #ffe082;">
            <strong class="small"><i class="bi bi-chat-left-text me-1"></i>Special Requests:</strong>
            <p class="small mb-0 mt-1"><?= Helper::e($booking['special_requests']) ?></p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Extra Services -->
    <?php if (!empty($booking['services'])): ?>
    <div class="panel-card mb-4">
      <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-bag-plus me-2"></i>Extra Services</h6></div>
      <div class="table-responsive">
        <table class="table mb-0 small">
          <thead><tr><th>Service</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
          <tbody>
            <?php foreach ($booking['services'] as $s): ?>
              <tr>
                <td><?= Helper::e($s['service']) ?></td>
                <td><?= $s['quantity'] ?></td>
                <td><?= Helper::money($s['unit_price']) ?></td>
                <td class="fw-semibold"><?= Helper::money($s['total']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Payment History -->
    <div class="panel-card mb-4">
      <div class="panel-card-header">
        <h6 class="panel-card-title"><i class="bi bi-credit-card me-2"></i>Payments</h6>
        <?php if ($booking['payment_status'] !== 'paid'): ?>
          <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
            <i class="bi bi-plus me-1"></i>Record Payment
          </button>
        <?php endif; ?>
      </div>
      <div class="table-responsive">
        <table class="table mb-0 small">
          <thead><tr><th>Date</th><th>Method</th><th>Amount</th><th>Status</th><th>Txn ID</th></tr></thead>
          <tbody>
            <?php if (empty($booking['payments'])): ?>
              <tr><td colspan="5" class="text-center text-muted py-3">No payments recorded yet.</td></tr>
            <?php else: foreach ($booking['payments'] as $p): ?>
              <tr>
                <td><?= Helper::formatDate($p['paid_at'] ?? $p['created_at'], 'M d, Y') ?></td>
                <td><?= ucwords(str_replace('_',' ',$p['method'])) ?></td>
                <td class="fw-semibold"><?= Helper::money($p['amount']) ?></td>
                <td><?= Helper::paymentStatusBadge($p['status']) ?></td>
                <td class="text-muted"><?= Helper::e($p['transaction_id']) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Actions -->
    <div class="panel-card">
      <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-gear me-2"></i>Actions</h6></div>
      <div class="panel-card-body d-flex flex-wrap gap-2">
        <?php if ($booking['status'] === 'pending'): ?>
          <form method="POST" action="<?= APP_URL ?>/admin/bookings/confirm/<?= $booking['id'] ?>">
            <?= Auth::csrfField() ?>
            <button class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Confirm Booking</button>
          </form>
        <?php endif; ?>
        <?php if ($booking['status'] === 'confirmed'): ?>
          <form method="POST" action="<?= APP_URL ?>/admin/bookings/checkin/<?= $booking['id'] ?>">
            <?= Auth::csrfField() ?>
            <button class="btn btn-primary"><i class="bi bi-box-arrow-in-right me-1"></i>Check In Guest</button>
          </form>
        <?php endif; ?>
        <?php if ($booking['status'] === 'checked_in'): ?>
          <form method="POST" action="<?= APP_URL ?>/admin/bookings/checkout/<?= $booking['id'] ?>">
            <?= Auth::csrfField() ?>
            <button class="btn btn-warning"><i class="bi bi-box-arrow-right me-1"></i>Check Out Guest</button>
          </form>
        <?php endif; ?>
        <?php if (!in_array($booking['status'],['cancelled','checked_out','no_show'])): ?>
          <button class="btn btn-outline-danger" id="cancel-booking-btn"
                  data-cancel-url="<?= APP_URL ?>/admin/bookings/cancel/<?= $booking['id'] ?>">
            <i class="bi bi-x-circle me-1"></i>Cancel Booking
          </button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="col-lg-4">
    <!-- Guest Info -->
    <div class="panel-card mb-4">
      <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-person me-2"></i>Guest</h6></div>
      <div class="panel-card-body">
        <p class="mb-1 fw-bold"><?= Helper::e($booking['guest_name']) ?></p>
        <p class="small mb-1"><i class="bi bi-envelope me-1 text-muted"></i><?= Helper::e($booking['guest_email']) ?></p>
        <p class="small mb-0"><i class="bi bi-telephone me-1 text-muted"></i><?= Helper::e($booking['guest_phone'] ?? '—') ?></p>
      </div>
    </div>

    <!-- Room Info -->
    <div class="panel-card mb-4">
      <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-door-closed me-2"></i>Room</h6></div>
      <div class="panel-card-body">
        <p class="mb-1 fw-bold"><?= Helper::e($booking['room_name']) ?></p>
        <p class="small mb-1 text-muted"><?= Helper::e($booking['category_name']) ?> — Room #<?= Helper::e($booking['room_number']) ?></p>
        <p class="small mb-0"><span class="badge bg-secondary">Rate: <?= Helper::money($booking['room_rate']) ?>/night</span></p>
      </div>
    </div>

    <!-- Price Breakdown -->
    <div class="panel-card">
      <div class="panel-card-header"><h6 class="panel-card-title"><i class="bi bi-receipt me-2"></i>Price Summary</h6></div>
      <div class="panel-card-body">
        <div class="d-flex justify-content-between small mb-2">
          <span><?= Helper::money($booking['room_rate']) ?> × <?= $booking['nights'] ?> nights</span>
          <span><?= Helper::money($booking['subtotal']) ?></span>
        </div>
        <?php if ($booking['discount'] > 0): ?>
          <div class="d-flex justify-content-between small mb-2 text-success">
            <span>Discount</span>
            <span>-<?= Helper::money($booking['discount']) ?></span>
          </div>
        <?php endif; ?>
        <div class="d-flex justify-content-between small mb-2">
          <span>Tax (<?= $booking['tax_rate'] ?>%)</span>
          <span><?= Helper::money($booking['tax_amount']) ?></span>
        </div>
        <hr class="my-2">
        <div class="d-flex justify-content-between fw-bold">
          <span>Total</span>
          <span style="color:var(--accent);font-size:1.1rem;"><?= Helper::money($booking['total_amount']) ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--radius);">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Record Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="record-payment-form" data-url="<?= APP_URL ?>/admin/payments/record/<?= $booking['id'] ?>">
          <?= Auth::csrfField() ?>
          <div class="mb-3">
            <label class="form-label">Amount *</label>
            <div class="input-group">
              <span class="input-group-text">$</span>
              <input type="number" name="amount" class="form-control" step="0.01" value="<?= $booking['total_amount'] ?>" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Payment Method *</label>
            <select name="method" class="form-select" required>
              <option value="cash">Cash</option>
              <option value="credit_card">Credit Card</option>
              <option value="stripe">Stripe</option>
              <option value="paypal">PayPal</option>
              <option value="bank_transfer">Bank Transfer</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Transaction ID</label>
            <input type="text" name="transaction_id" class="form-control" placeholder="Optional">
          </div>
          <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes…"></textarea>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-success fw-bold"><i class="bi bi-check-circle me-1"></i>Record Payment</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include VIEWS_PATH . '/layouts/admin_footer.php'; ?>
