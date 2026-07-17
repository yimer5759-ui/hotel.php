<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= Helper::e($pageTitle ?? 'Grand Azure Hotel') ?> — Grand Azure Hotel</title>
<meta name="description" content="Grand Azure Hotel — Experience luxury at its finest with world-class amenities and unforgettable stays.">

<!-- Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<!-- Custom CSS -->
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<script>window.APP_URL = '<?= APP_URL ?>';</script>
</head>
<body>

<!-- Spinner overlay -->
<div class="spinner-overlay" id="spinner-overlay">
  <div class="text-center text-white">
    <div class="spinner-border text-warning mb-2" role="status" style="width:3rem;height:3rem;"></div>
    <div>Please wait…</div>
  </div>
</div>

<!-- ── Navbar ─────────────────────────────────────────────── -->
<nav class="navbar navbar-expand-lg navbar-public fixed-top">
  <div class="container">
    <a class="navbar-brand-logo" href="<?= APP_URL ?>/">
      Grand <span>Azure</span>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <i class="bi bi-list text-white fs-4"></i>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/rooms">Rooms</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/gallery">Gallery</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/about">About</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/faq">FAQ</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/contact">Contact</a></li>
      </ul>

      <div class="d-flex align-items-center gap-2">
        <?php if ($authUser): ?>
          <a href="<?= APP_URL ?>/<?= $authUser['role'] ?>/dashboard" class="btn btn-sm btn-outline-light rounded-pill">
            <i class="bi bi-grid me-1"></i>Dashboard
          </a>
          <a href="<?= APP_URL ?>/auth/logout" class="btn btn-sm btn-outline-warning rounded-pill">Logout</a>
        <?php else: ?>
          <a href="<?= APP_URL ?>/auth/login"    class="btn btn-sm btn-outline-light rounded-pill">Login</a>
          <a href="<?= APP_URL ?>/auth/register" class="btn-book-now btn btn-sm">Book Now</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
