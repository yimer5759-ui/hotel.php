<?php
/**
 * Front Controller — index.php
 * Hotel Booking Management System
 */

// ── Bootstrap ─────────────────────────────────────────────────
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// ── Includes ──────────────────────────────────────────────────
foreach (['Router','Auth','Helper','Validator'] as $cls) {
    require_once __DIR__ . '/includes/' . $cls . '.php';
}

// ── Models ────────────────────────────────────────────────────
foreach (['Model','User','Room','Booking','Payment','Invoice','Review'] as $cls) {
    require_once __DIR__ . '/models/' . $cls . '.php';
}
// Settings, Notification, Review are in Review.php
// (they are included above)

// ── Controllers ───────────────────────────────────────────────
foreach (['Auth','Admin','Room','Booking','Customer','Public'] as $cls) {
    require_once __DIR__ . '/controllers/' . $cls . 'Controller.php';
}
// Receptionist, Payment, Invoice, Review, Report, Settings are in CustomerController.php
require_once __DIR__ . '/controllers/PublicController.php';

// ── Session ───────────────────────────────────────────────────
Auth::start();

// ── Router ────────────────────────────────────────────────────
$router = new Router();

/* ── Public routes ─────────────────────────────────────────── */
$router->any('/',                    'PublicController', 'index');
$router->any('/rooms',               'PublicController', 'rooms');
$router->get('/rooms/{id}',          'PublicController', 'roomDetail');
$router->any('/about',               'PublicController', 'about');
$router->any('/contact',             'PublicController', 'contact');
$router->get('/gallery',             'PublicController', 'gallery');
$router->get('/faq',                 'PublicController', 'faq');
$router->post('/newsletter',         'PublicController', 'newsletter');

/* ── Search ────────────────────────────────────────────────── */
$router->get('/search',              'BookingController', 'search');

/* ── Auth routes ────────────────────────────────────────────── */
$router->any('/auth/login',          'AuthController', 'login');
$router->any('/auth/register',       'AuthController', 'register');
$router->any('/auth/logout',         'AuthController', 'logout');
$router->any('/auth/forgot-password','AuthController', 'forgotPassword');
$router->any('/auth/reset-password', 'AuthController', 'resetPassword');

/* ── Admin routes ───────────────────────────────────────────── */
$router->get('/admin/dashboard',     'AdminController', 'dashboard');
$router->get('/admin/customers',     'AdminController', 'customers');
$router->get('/admin/customers/{id}','AdminController', 'viewCustomer');
$router->post('/admin/customers/{id}/toggle','AdminController','toggleUserStatus');

// Rooms
$router->get( '/admin/rooms',           'RoomController', 'index');
$router->any( '/admin/rooms/add',       'RoomController', 'add');
$router->any( '/admin/rooms/edit/{id}', 'RoomController', 'edit');
$router->post('/admin/rooms/delete/{id}','RoomController','delete');
$router->post('/admin/rooms/image/{id}/delete','RoomController','deleteImage');

// Bookings
$router->get( '/admin/bookings',            'BookingController', 'index');
$router->get( '/admin/bookings/view/{id}',  'BookingController', 'view');
$router->post('/admin/bookings/confirm/{id}','BookingController','confirm');
$router->post('/admin/bookings/cancel/{id}','BookingController','cancel');
$router->post('/admin/bookings/checkin/{id}','BookingController','checkIn');
$router->post('/admin/bookings/checkout/{id}','BookingController','checkOut');
$router->get( '/admin/bookings/calendar',   'BookingController', 'calendar');

// Payments
$router->get( '/admin/payments',                    'PaymentController','index');
$router->post('/admin/payments/record/{bookingId}', 'PaymentController','recordPayment');

// Reports
$router->get('/admin/reports',  'ReportController', 'index');

// Reviews
$router->get( '/admin/reviews',               'ReviewController', 'index');
$router->post('/admin/reviews/approve/{id}',  'ReviewController', 'approve');
$router->post('/admin/reviews/reject/{id}',   'ReviewController', 'reject');
$router->post('/admin/reviews/delete/{id}',   'ReviewController', 'delete');

// Settings
$router->get( '/admin/settings',  'SettingsController', 'index');
$router->post('/admin/settings',  'SettingsController', 'save');

/* ── Receptionist routes ─────────────────────────────────────── */
$router->get('/receptionist/dashboard',  'ReceptionistController', 'dashboard');
$router->get('/receptionist/checkin',    'ReceptionistController', 'checkInPanel');
$router->any('/receptionist/walk-in',    'BookingController',       'walkIn');
$router->get('/receptionist/bookings',   'BookingController',       'index');

/* ── Customer routes ────────────────────────────────────────── */
$router->get('/customer/dashboard',             'CustomerController', 'dashboard');
$router->get('/customer/bookings',              'CustomerController', 'bookings');
$router->get('/customer/bookings/view/{id}',    'CustomerController', 'viewBooking');
$router->any('/customer/book/{roomId}',         'BookingController',  'book');
$router->post('/customer/bookings/store',       'BookingController',  'store');
$router->post('/customer/bookings/cancel/{id}', 'BookingController',  'cancel');
$router->any( '/customer/profile',              'CustomerController', 'profile');
$router->post('/customer/change-password',      'CustomerController', 'changePassword');
$router->get( '/customer/invoices',             'CustomerController', 'invoices');
$router->get( '/customer/notifications',        'CustomerController', 'notifications');
$router->post('/customer/reviews/{bookingId}',  'CustomerController', 'submitReview');

/* ── Invoice routes ─────────────────────────────────────────── */
$router->get('/invoices/view/{id}',  'InvoiceController', 'view');
$router->get('/invoices/print/{id}', 'InvoiceController', 'print');

/* ── Coupon validation ──────────────────────────────────────── */
$router->post('/api/validate-coupon', 'BookingController', 'validateCoupon');

// ── Dispatch ──────────────────────────────────────────────────
$router->dispatch();
