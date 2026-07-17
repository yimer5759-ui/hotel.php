<?php
/**
 * Admin Panel Layout — wraps all admin/receptionist views
 * Usage: include this at top, then include the content, then call admin_footer()
 */
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function isActive(string $path): string {
    global $currentPath;
    return str_contains($currentPath, $path) ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= Helper::e($pageTitle ?? 'Dashboard') ?> — Grand Azure Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<script>window.APP_URL='<?= APP_URL ?>';</script>
</head>
<body>

<!-- Spinner -->
<div class="spinner-overlay" id="spinner-overlay">
  <div class="text-center text-white">
    <div class="spinner-border text-warning mb-2" style="width:3rem;height:3rem;" role="status"></div>
    <div class="small">Please wait…</div>
  </div>
</div>

<!-- Sidebar overlay (mobile) -->
<div id="sidebar-overlay" class="d-none" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;"></div>

<div class="admin-wrapper">

  <!-- ── Sidebar ───────────────────────────────────────────── -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <a href="<?= APP_URL ?>/" class="text-decoration-none">
        <div class="sidebar-brand-name">Grand <span>Azure</span></div>
        <div class="sidebar-brand-sub">
          <?= Auth::isAdmin() ? 'Admin Panel' : (Auth::isReceptionist() ? 'Receptionist Panel' : 'Customer Panel') ?>
        </div>
      </a>
    </div>

    <nav class="sidebar-nav">
      <?php if (Auth::isAdmin()): ?>

        <div class="sidebar-heading">Overview</div>
        <a href="<?= APP_URL ?>/admin/dashboard" class="sidebar-link<?= isActive('/admin/dashboard') ?>">
          <i class="bi bi-grid-1x2"></i> Dashboard
        </a>

        <div class="sidebar-heading">Rooms</div>
        <a href="<?= APP_URL ?>/admin/rooms" class="sidebar-link<?= isActive('/admin/rooms') ?>">
          <i class="bi bi-door-closed"></i> All Rooms
        </a>
        <a href="<?= APP_URL ?>/admin/rooms/add" class="sidebar-link<?= isActive('/admin/rooms/add') ?>">
          <i class="bi bi-plus-circle"></i> Add Room
        </a>

        <div class="sidebar-heading">Bookings</div>
        <a href="<?= APP_URL ?>/admin/bookings" class="sidebar-link<?= isActive('/admin/bookings') ?>">
          <i class="bi bi-calendar-check"></i> All Bookings
        </a>
        <a href="<?= APP_URL ?>/admin/bookings/calendar" class="sidebar-link<?= isActive('/admin/bookings/calendar') ?>">
          <i class="bi bi-calendar3"></i> Calendar
        </a>
        <a href="<?= APP_URL ?>/receptionist/walk-in" class="sidebar-link">
          <i class="bi bi-person-plus"></i> Walk-In Booking
        </a>

        <div class="sidebar-heading">Finance</div>
        <a href="<?= APP_URL ?>/admin/payments" class="sidebar-link<?= isActive('/admin/payments') ?>">
          <i class="bi bi-credit-card"></i> Payments
        </a>
        <a href="<?= APP_URL ?>/admin/reports" class="sidebar-link<?= isActive('/admin/reports') ?>">
          <i class="bi bi-bar-chart"></i> Reports
        </a>

        <div class="sidebar-heading">People</div>
        <a href="<?= APP_URL ?>/admin/customers" class="sidebar-link<?= isActive('/admin/customers') ?>">
          <i class="bi bi-people"></i> Customers
        </a>

        <div class="sidebar-heading">Content</div>
        <a href="<?= APP_URL ?>/admin/reviews" class="sidebar-link<?= isActive('/admin/reviews') ?>">
          <i class="bi bi-star"></i> Reviews
        </a>

        <div class="sidebar-heading">System</div>
        <a href="<?= APP_URL ?>/admin/settings" class="sidebar-link<?= isActive('/admin/settings') ?>">
          <i class="bi bi-gear"></i> Settings
        </a>

      <?php elseif (Auth::isReceptionist()): ?>

        <div class="sidebar-heading">Overview</div>
        <a href="<?= APP_URL ?>/receptionist/dashboard" class="sidebar-link<?= isActive('/receptionist/dashboard') ?>">
          <i class="bi bi-grid-1x2"></i> Dashboard
        </a>

        <div class="sidebar-heading">Operations</div>
        <a href="<?= APP_URL ?>/receptionist/checkin" class="sidebar-link<?= isActive('/receptionist/checkin') ?>">
          <i class="bi bi-door-open"></i> Check-In / Out
        </a>
        <a href="<?= APP_URL ?>/receptionist/bookings" class="sidebar-link<?= isActive('/receptionist/bookings') ?>">
          <i class="bi bi-calendar-check"></i> All Bookings
        </a>
        <a href="<?= APP_URL ?>/receptionist/walk-in" class="sidebar-link<?= isActive('/receptionist/walk-in') ?>">
          <i class="bi bi-person-plus"></i> Walk-In Booking
        </a>

      <?php endif; ?>

      <!-- Common bottom links -->
      <div class="sidebar-heading mt-3">Account</div>
      <a href="<?= APP_URL ?>/" class="sidebar-link" target="_blank">
        <i class="bi bi-globe"></i> View Website
      </a>
      <a href="<?= APP_URL ?>/auth/logout" class="sidebar-link text-danger-emphasis"
         onclick="return confirm('Sign out?')">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </nav>
  </aside>

  <!-- ── Main Content ──────────────────────────────────────── -->
  <div class="main-content">

    <!-- Topbar -->
    <header class="topbar">
      <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
        <h1 class="topbar-title"><?= Helper::e($pageTitle ?? 'Dashboard') ?></h1>
      </div>

      <div class="d-flex align-items-center gap-3">
        <!-- Notifications -->
        <div class="dropdown">
          <button class="btn btn-sm btn-light position-relative rounded-circle" style="width:38px;height:38px;" data-bs-toggle="dropdown">
            <i class="bi bi-bell fs-5"></i>
            <?php if (($unreadNotifs ?? 0) > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">
                <?= min(99, $unreadNotifs) ?>
              </span>
            <?php endif; ?>
          </button>
          <div class="dropdown-menu dropdown-menu-end shadow" style="width:280px;border-radius:12px;">
            <div class="px-3 py-2 border-bottom">
              <strong class="small">Notifications</strong>
            </div>
            <div class="px-3 py-2 text-muted small">No new notifications</div>
          </div>
        </div>

        <!-- User dropdown -->
        <div class="dropdown">
          <button class="btn btn-sm btn-light d-flex align-items-center gap-2 px-3 py-2 rounded-pill" data-bs-toggle="dropdown">
            <?php if ($authUser['avatar'] ?? ''): ?>
              <img src="<?= UPLOADS_URL ?>/avatars/<?= Helper::e($authUser['avatar']) ?>" class="topbar-avatar" alt="">
            <?php else: ?>
              <div class="topbar-avatar d-flex align-items-center justify-content-center" style="background:var(--primary);color:#fff;font-weight:700;font-size:.8rem;">
                <?= strtoupper(substr($authUser['name'] ?? 'U', 0, 1)) ?>
              </div>
            <?php endif; ?>
            <div class="text-start d-none d-md-block">
              <div class="topbar-user-name"><?= Helper::e($authUser['name'] ?? '') ?></div>
              <div class="topbar-user-role"><?= ucfirst($authUser['role'] ?? '') ?></div>
            </div>
            <i class="bi bi-chevron-down small"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow" style="border-radius:12px;min-width:180px;">
            <li><a class="dropdown-item" href="<?= APP_URL ?>/<?= $authUser['role'] ?>/dashboard"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
            <?php if (Auth::isCustomer()): ?>
              <li><a class="dropdown-item" href="<?= APP_URL ?>/customer/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </div>
      </div>
    </header>

    <!-- Flash messages -->
    <div class="px-4 pt-3">
      <?php if (Auth::hasFlash('success')): ?>
        <div class="alert alert-success alert-modern alert-auto-dismiss">
          <i class="bi bi-check-circle-fill"></i> <?= Helper::e(Auth::getFlash('success')) ?>
        </div>
      <?php endif; ?>
      <?php if (Auth::hasFlash('error')): ?>
        <div class="alert alert-danger alert-modern alert-auto-dismiss">
          <i class="bi bi-exclamation-circle-fill"></i> <?= Helper::e(Auth::getFlash('error')) ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Page content starts here (included by controller) -->
    <div class="content-area">
