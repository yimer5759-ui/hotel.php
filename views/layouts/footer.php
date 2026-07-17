<!-- ── Footer ─────────────────────────────────────────────── -->
<footer class="footer-main mt-auto">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="footer-logo mb-3">Grand <span>Azure</span></div>
        <p class="small" style="color:rgba(255,255,255,.55);line-height:1.8;">
          Experience luxury at its finest. Grand Azure Hotel offers world-class amenities, exquisite dining, and unforgettable stays in the heart of the city.
        </p>
        <div class="footer-social mt-3">
          <a href="<?= $settings['facebook_url']  ?? '#' ?>"><i class="bi bi-facebook"></i></a>
          <a href="<?= $settings['twitter_url']   ?? '#' ?>"><i class="bi bi-twitter-x"></i></a>
          <a href="<?= $settings['instagram_url'] ?? '#' ?>"><i class="bi bi-instagram"></i></a>
          <a href="<?= $settings['tripadvisor_url']??'#' ?>"><i class="bi bi-star"></i></a>
        </div>
      </div>

      <div class="col-lg-2 col-6">
        <div class="footer-heading">Quick Links</div>
        <a class="footer-link" href="<?= APP_URL ?>/">Home</a>
        <a class="footer-link" href="<?= APP_URL ?>/rooms">Rooms</a>
        <a class="footer-link" href="<?= APP_URL ?>/about">About Us</a>
        <a class="footer-link" href="<?= APP_URL ?>/gallery">Gallery</a>
        <a class="footer-link" href="<?= APP_URL ?>/faq">FAQ</a>
        <a class="footer-link" href="<?= APP_URL ?>/contact">Contact</a>
      </div>

      <div class="col-lg-3 col-6">
        <div class="footer-heading">Contact Info</div>
        <p class="small mb-2" style="color:rgba(255,255,255,.55);">
          <i class="bi bi-geo-alt me-2" style="color:var(--accent)"></i>
          <?= Helper::e($settings['hotel_address'] ?? '1 Ocean Drive, Miami, FL') ?>
        </p>
        <p class="small mb-2" style="color:rgba(255,255,255,.55);">
          <i class="bi bi-telephone me-2" style="color:var(--accent)"></i>
          <?= Helper::e($settings['hotel_phone'] ?? '+1-800-555-HOTEL') ?>
        </p>
        <p class="small mb-2" style="color:rgba(255,255,255,.55);">
          <i class="bi bi-envelope me-2" style="color:var(--accent)"></i>
          <?= Helper::e($settings['hotel_email'] ?? 'info@grandazure.com') ?>
        </p>
        <p class="small" style="color:rgba(255,255,255,.55);">
          <i class="bi bi-clock me-2" style="color:var(--accent)"></i>
          Check-in: <?= $settings['check_in_time'] ?? '14:00' ?> | Check-out: <?= $settings['check_out_time'] ?? '11:00' ?>
        </p>
      </div>

      <div class="col-lg-3">
        <div class="footer-heading">Newsletter</div>
        <p class="small mb-3" style="color:rgba(255,255,255,.55);">Subscribe for exclusive deals and updates.</p>
        <form id="newsletter-form">
          <?= Auth::csrfField() ?>
          <div class="input-group">
            <input type="email" name="email" class="form-control form-control-sm" placeholder="Your email address" required>
            <button class="btn btn-sm" style="background:var(--accent);color:var(--dark);font-weight:600;">Subscribe</button>
          </div>
          <div id="newsletter-msg" class="mt-1 small"></div>
        </form>
      </div>
    </div>

    <div class="divider-gold mt-4"></div>
    <div class="row align-items-center py-3">
      <div class="col-md-6 text-center text-md-start">
        <small style="color:rgba(255,255,255,.4);">&copy; <?= date('Y') ?> Grand Azure Hotel. All rights reserved.</small>
      </div>
      <div class="col-md-6 text-center text-md-end">
        <small style="color:rgba(255,255,255,.4);">
          <a href="<?= APP_URL ?>/auth/login" style="color:rgba(255,255,255,.4);">Staff Login</a>
          &nbsp;|&nbsp;
          <a href="<?= APP_URL ?>/auth/register" style="color:rgba(255,255,255,.4);">Register</a>
        </small>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<!-- App JS -->
<script src="<?= APP_URL ?>/assets/js/app.js"></script>
</body>
</html>
