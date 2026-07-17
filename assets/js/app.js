/**
 * Grand Azure Hotel — Global JavaScript
 * Handles: CSRF, AJAX, SweetAlert confirmations, sidebar, charts, etc.
 */
'use strict';

/* ── CSRF Token helper ──────────────────────────────────────── */
const CSRF_TOKEN_NAME = '_csrf_token';
const csrfToken = () => document.querySelector(`[name="${CSRF_TOKEN_NAME}"]`)?.value || '';

/* ── Fetch helper with CSRF ─────────────────────────────────── */
async function postJSON(url, data = {}) {
  const form = new FormData();
  form.append(CSRF_TOKEN_NAME, csrfToken());
  for (const [k, v] of Object.entries(data)) form.append(k, v);

  const res = await fetch(url, { method: 'POST', body: form });
  return res.json();
}

/* ── Spinner ─────────────────────────────────────────────────── */
const spinner = {
  show() { document.getElementById('spinner-overlay')?.style.setProperty('display','flex'); },
  hide() { document.getElementById('spinner-overlay')?.style.setProperty('display','none'); },
};

/* ── Mobile Sidebar ──────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.querySelector('.sidebar');
  const toggle  = document.querySelector('.sidebar-toggle');
  const overlay = document.getElementById('sidebar-overlay');

  toggle?.addEventListener('click', () => {
    sidebar?.classList.toggle('open');
    overlay?.classList.toggle('d-none');
  });
  overlay?.addEventListener('click', () => {
    sidebar?.classList.remove('open');
    overlay?.classList.add('d-none');
  });

  /* ── Navbar scroll effect ─── */
  const navbar = document.querySelector('.navbar-public');
  if (navbar) {
    window.addEventListener('scroll', () => {
      navbar.classList.toggle('scrolled', window.scrollY > 50);
    });
  }

  /* ── Active sidebar link ─── */
  const path  = window.location.pathname;
  document.querySelectorAll('.sidebar-link').forEach(link => {
    if (link.getAttribute('href') && path.startsWith(link.getAttribute('href'))) {
      link.classList.add('active');
    }
  });

  /* ── Tooltip init ─── */
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el =>
    new bootstrap.Tooltip(el, { trigger: 'hover' })
  );

  /* ── Auto-dismiss alerts ─── */
  document.querySelectorAll('.alert-auto-dismiss').forEach(el => {
    setTimeout(() => {
      el.classList.add('fade');
      setTimeout(() => el.remove(), 500);
    }, 4000);
  });

  /* ── Simple AOS ─── */
  const aosObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('aos-animate'); } });
  }, { threshold: 0.1 });
  document.querySelectorAll('[data-aos]').forEach(el => aosObserver.observe(el));
});

/* ── Room Gallery ─────────────────────────────────────────────── */
window.switchGalleryImage = function(src, el) {
  const main = document.getElementById('gallery-main');
  if (main) main.src = src;
  document.querySelectorAll('.room-gallery-thumb').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
};

/* ── Night counter & price ────────────────────────────────────── */
window.calculatePrice = function() {
  const checkIn  = document.getElementById('check_in')?.value;
  const checkOut = document.getElementById('check_out')?.value;
  const rate     = parseFloat(document.getElementById('room_rate')?.value || 0);
  const taxRate  = parseFloat(document.getElementById('tax_rate_input')?.value || 0);

  if (!checkIn || !checkOut) return;

  const nights = Math.max(0, Math.ceil((new Date(checkOut) - new Date(checkIn)) / 86400000));
  const sub    = nights * rate;
  const tax    = sub * (taxRate / 100);
  const total  = sub + tax;

  const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
  set('nights-count', nights);
  set('subtotal-amt', '$' + sub.toFixed(2));
  set('tax-amt', '$' + tax.toFixed(2));
  set('total-amt', '$' + total.toFixed(2));
};

/* ── Coupon validation ─────────────────────────────────────────── */
document.getElementById('apply-coupon-btn')?.addEventListener('click', async () => {
  const code   = document.getElementById('coupon_code')?.value.trim();
  const nights = document.getElementById('nights-count')?.textContent || 1;
  const sub    = parseFloat(document.getElementById('subtotal-hidden')?.value || 0);

  if (!code) return;

  const data = await postJSON(APP_URL + '/api/validate-coupon', { code, nights, amount: sub });

  const msg = document.getElementById('coupon-message');
  if (msg) {
    msg.textContent  = data.message;
    msg.className    = 'mt-1 small ' + (data.valid ? 'text-success' : 'text-danger');
  }
  if (data.valid) {
    const discountEl = document.getElementById('discount-amt');
    if (discountEl) discountEl.textContent = '-$' + parseFloat(data.discount).toFixed(2);
    document.getElementById('coupon_id')?.setAttribute('value', code);
  }
});

/* ── Delete confirmation ───────────────────────────────────────── */
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-delete-url]');
  if (!btn) return;

  e.preventDefault();
  const result = await Swal.fire({
    title: btn.dataset.deleteTitle || 'Are you sure?',
    text:  btn.dataset.deleteText  || 'This action cannot be undone.',
    icon:  'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, delete it!',
  });

  if (result.isConfirmed) {
    spinner.show();
    const res = await postJSON(btn.dataset.deleteUrl);
    spinner.hide();
    if (res.success) {
      Swal.fire('Deleted!', 'The record has been removed.', 'success').then(() => location.reload());
    } else {
      Swal.fire('Error', res.message || 'Could not delete.', 'error');
    }
  }
});

/* ── Status toggle (approve/reject reviews) ────────────────────── */
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-action-url]');
  if (!btn) return;

  e.preventDefault();
  const res = await postJSON(btn.dataset.actionUrl);
  if (res.success) {
    Swal.fire({ icon: 'success', title: 'Done!', timer: 1500, showConfirmButton: false })
        .then(() => location.reload());
  }
});

/* ── Cancel booking ────────────────────────────────────────────── */
document.getElementById('cancel-booking-btn')?.addEventListener('click', async () => {
  const result = await Swal.fire({
    title: 'Cancel Booking?',
    input:  'textarea',
    inputLabel: 'Reason for cancellation (optional)',
    inputPlaceholder: 'Enter reason...',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    confirmButtonText: 'Yes, cancel it',
  });

  if (result.isConfirmed) {
    const url    = document.getElementById('cancel-booking-btn').dataset.cancelUrl;
    const reason = result.value || '';
    spinner.show();
    const res = await postJSON(url, { reason });
    spinner.hide();
    if (res.success) {
      Swal.fire('Cancelled!', res.message, 'success').then(() => location.reload());
    } else {
      Swal.fire('Error', res.message, 'error');
    }
  }
});

/* ── Payment recording ─────────────────────────────────────────── */
document.getElementById('record-payment-form')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const form   = e.target;
  const url    = form.dataset.url;
  const data   = Object.fromEntries(new FormData(form));
  spinner.show();
  const res    = await postJSON(url, data);
  spinner.hide();
  if (res.success) {
    Swal.fire('Payment Recorded!', 'Invoice generated.', 'success').then(() => location.reload());
  } else {
    Swal.fire('Error', res.message, 'error');
  }
});

/* ── Change password modal ─────────────────────────────────────── */
document.getElementById('change-password-form')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const data = Object.fromEntries(new FormData(e.target));
  spinner.show();
  const res  = await postJSON(APP_URL + '/customer/change-password', data);
  spinner.hide();
  if (res.success) {
    Swal.fire('Success!', res.message, 'success');
    bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'))?.hide();
    e.target.reset();
  } else {
    Swal.fire('Error', res.message, 'error');
  }
});

/* ── Newsletter ─────────────────────────────────────────────────── */
document.getElementById('newsletter-form')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const email = e.target.querySelector('[name="email"]').value;
  const res   = await postJSON(APP_URL + '/newsletter', { email });
  const msg   = document.getElementById('newsletter-msg');
  if (msg) {
    msg.textContent = res.message;
    msg.className = res.success ? 'text-success mt-2' : 'text-danger mt-2';
  }
  if (res.success) e.target.reset();
});

/* ── Contact form ───────────────────────────────────────────────── */
document.getElementById('contact-form')?.addEventListener('submit', () => spinner.show());

/* ── Expose APP_URL for inline scripts ──────────────────────────── */
// Injected by PHP layout: window.APP_URL = '<?= APP_URL ?>';
