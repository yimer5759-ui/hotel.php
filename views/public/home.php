<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<!-- ── HERO ──────────────────────────────────────────────────── -->
<section class="hero-section">
  <div class="container" style="padding-top:5rem;">
    <div class="row align-items-center min-vh-100 py-5">
      <div class="col-lg-6 hero-content" data-aos>
        <div class="hero-badge">
          <i class="bi bi-star-fill"></i>
          Award-Winning Luxury Hotel
        </div>
        <h1 class="hero-title">
          Experience <span>Luxury</span><br>Like Never Before
        </h1>
        <p class="hero-subtitle">
          Grand Azure Hotel — where world-class amenities meet breathtaking views. Your perfect escape awaits in Miami's most iconic address.
        </p>
        <div class="d-flex gap-3 flex-wrap">
          <a href="<?= APP_URL ?>/rooms" class="btn btn-accent btn-lg px-4 fw-bold">
            <i class="bi bi-door-closed me-2"></i>Explore Rooms
          </a>
          <a href="<?= APP_URL ?>/about" class="btn btn-outline-light btn-lg px-4">
            <i class="bi bi-play-circle me-2"></i>Learn More
          </a>
        </div>
        <!-- Rating -->
        <div class="d-flex align-items-center gap-3 mt-4">
          <div class="review-stars">★★★★★</div>
          <div class="text-white-50 small">
            <strong class="text-white"><?= number_format($avgRating,1) ?>/5</strong> based on our reviews
          </div>
        </div>
      </div>

      <!-- Search Card -->
      <div class="col-lg-5 offset-lg-1 mt-5 mt-lg-0" data-aos>
        <div class="search-card">
          <h4 class="fw-bold mb-1" style="color:var(--dark)">Book Your Stay</h4>
          <p class="text-muted small mb-4">Find the perfect room for your next adventure</p>
          <form action="<?= APP_URL ?>/search" method="GET">
            <div class="mb-3">
              <label class="form-label">Check-In Date</label>
              <input type="date" name="check_in" class="form-control" min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Check-Out Date</label>
              <input type="date" name="check_out" class="form-control" min="<?= date('Y-m-d',strtotime('+1 day')) ?>" required>
            </div>
            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label">Guests</label>
                <select name="guests" class="form-select">
                  <?php for ($g=1;$g<=10;$g++): ?><option value="<?=$g?>"><?=$g?> Guest<?=$g>1?'s':''?></option><?php endfor; ?>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">Room Type</label>
                <select name="category" class="form-select">
                  <option value="">Any Type</option>
                  <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['slug'] ?>"><?= Helper::e($c['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <button type="submit" class="btn-search btn">
              <i class="bi bi-search me-2"></i>Search Available Rooms
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── STATS BAR ─────────────────────────────────────────────── -->
<div class="stats-bar">
  <div class="container">
    <div class="row">
      <div class="col-3 stat-item"><div class="stat-number">150+</div><div class="stat-label">Luxury Rooms</div></div>
      <div class="col-3 stat-item"><div class="stat-number">10K+</div><div class="stat-label">Happy Guests</div></div>
      <div class="col-3 stat-item"><div class="stat-number">15+</div><div class="stat-label">Years of Excellence</div></div>
      <div class="col-3 stat-item"><div class="stat-number">4.9★</div><div class="stat-label">Average Rating</div></div>
    </div>
  </div>
</div>

<!-- ── FEATURED ROOMS ─────────────────────────────────────────── -->
<section class="py-6" style="padding:5rem 0;background:#fff;">
  <div class="container">
    <div class="text-center mb-5" data-aos>
      <div class="section-label">Accommodation</div>
      <h2 class="section-title">Our Finest Rooms & Suites</h2>
      <div class="section-divider mx-auto"></div>
      <p class="text-muted" style="max-width:500px;margin:0 auto;">Every room is designed to deliver an unparalleled experience of comfort and luxury.</p>
    </div>

    <div class="row g-4">
      <?php foreach ($featuredRooms as $i => $room): ?>
        <div class="col-lg-4 col-md-6" data-aos style="transition-delay:<?= $i*.1 ?>s;">
          <div class="room-card h-100">
            <div class="room-card-img-wrap">
              <img src="<?= $room['thumbnail'] ? UPLOADS_URL.'/rooms/'.Helper::e($room['thumbnail']) : APP_URL.'/assets/images/room-placeholder.jpg' ?>"
                   alt="<?= Helper::e($room['name']) ?>">
              <div class="room-card-badge"><?= Helper::e($room['category_name']) ?></div>
              <div class="room-card-price"><strong><?= Helper::money($room['price_per_night']) ?></strong>/night</div>
            </div>
            <div class="room-card-body">
              <div class="room-card-title"><?= Helper::e($room['name']) ?></div>
              <p class="text-muted small mb-3"><?= Helper::truncate($room['description'] ?? '', 80) ?></p>
              <div class="d-flex flex-wrap gap-1 mb-3">
                <span class="room-amenity-tag"><i class="bi bi-people"></i> <?= $room['capacity'] ?> Guests</span>
                <?php if ($room['size_sqft']): ?>
                  <span class="room-amenity-tag"><i class="bi bi-crop"></i> <?= $room['size_sqft'] ?> sqft</span>
                <?php endif; ?>
              </div>
              <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>" class="btn-view-room btn w-100">View Room <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-5">
      <a href="<?= APP_URL ?>/rooms" class="btn btn-primary btn-lg px-5">View All Rooms</a>
    </div>
  </div>
</section>

<!-- ── AMENITIES ──────────────────────────────────────────────── -->
<section style="padding:5rem 0;background:#f0f4f8;">
  <div class="container">
    <div class="text-center mb-5" data-aos>
      <div class="section-label">What We Offer</div>
      <h2 class="section-title">World-Class Amenities</h2>
      <div class="section-divider mx-auto"></div>
    </div>
    <div class="row g-4 justify-content-center">
      <?php
      $amenityIcons = [
        ['bi-wifi','Free Wi-Fi','High-speed internet throughout'],
        ['bi-water','Swimming Pool','Olympic-sized heated pool'],
        ['bi-cup-hot','Restaurant','Fine dining & room service'],
        ['bi-person-badge','Concierge','24/7 personal concierge'],
        ['bi-dumbbell','Fitness Center','State-of-the-art gym'],
        ['bi-droplet','Spa & Wellness','Award-winning spa'],
        ['bi-car-front','Valet Parking','Complimentary valet'],
        ['bi-airplane','Airport Transfer','Private airport transfers'],
      ];
      foreach ($amenityIcons as [$icon,$title,$desc]):
      ?>
        <div class="col-lg-3 col-md-4 col-6" data-aos>
          <div class="text-center p-4 bg-white rounded-xl shadow-soft h-100" style="transition:.3s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
            <div style="width:64px;height:64px;background:rgba(26,60,94,.08);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.8rem;color:var(--primary);">
              <i class="bi <?= $icon ?>"></i>
            </div>
            <h6 class="fw-bold mb-1"><?= $title ?></h6>
            <p class="text-muted small mb-0"><?= $desc ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── REVIEWS ────────────────────────────────────────────────── -->
<?php if (!empty($reviews)): ?>
<section style="padding:5rem 0;background:#fff;">
  <div class="container">
    <div class="text-center mb-5" data-aos>
      <div class="section-label">Guest Experiences</div>
      <h2 class="section-title">What Our Guests Say</h2>
      <div class="section-divider mx-auto"></div>
    </div>
    <div class="row g-4">
      <?php foreach ($reviews as $r): ?>
        <div class="col-lg-4" data-aos>
          <div class="review-card h-100">
            <div class="review-stars mb-2"><?= str_repeat('★', $r['rating']) ?></div>
            <h6 class="fw-bold mb-2"><?= Helper::e($r['title'] ?? 'Great experience!') ?></h6>
            <p class="text-muted small mb-3"><?= Helper::truncate(Helper::e($r['body']), 150) ?></p>
            <div class="d-flex align-items-center gap-2 mt-auto">
              <div style="width:40px;height:40px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem;border:2px solid var(--accent);">
                <?= strtoupper(substr($r['guest_name'],0,1)) ?>
              </div>
              <div>
                <div class="fw-semibold small"><?= Helper::e($r['guest_name']) ?></div>
                <div class="text-muted" style="font-size:.75rem;"><?= Helper::e($r['room_name']) ?></div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── NEWSLETTER ─────────────────────────────────────────────── -->
<section class="newsletter-section">
  <div class="container text-center">
    <div data-aos>
      <div class="section-label" style="color:var(--accent-light);">Stay Connected</div>
      <h2 class="section-title text-white">Get Exclusive Offers</h2>
      <p class="text-white-50 mb-4" style="max-width:450px;margin:0 auto 2rem;">Subscribe to receive special promotions, seasonal packages, and luxury travel inspiration.</p>
      <form id="newsletter-form" class="d-flex gap-2 justify-content-center flex-wrap">
        <?= Auth::csrfField() ?>
        <input type="email" name="email" class="form-control" style="max-width:320px;border-radius:30px;padding:.75rem 1.5rem;" placeholder="Enter your email address" required>
        <button type="submit" class="btn btn-accent fw-bold px-4" style="border-radius:30px;">Subscribe</button>
      </form>
      <div id="newsletter-msg" class="mt-2"></div>
    </div>
  </div>
</section>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
