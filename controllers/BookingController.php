<?php
/**
 * BookingController — Search, create, manage bookings
 */

class BookingController
{
    private Booking  $bookingModel;
    private Room     $roomModel;
    private Invoice  $invoiceModel;
    private Notification $notifModel;
    private Settings $settings;

    public function __construct()
    {
        $this->bookingModel = new Booking();
        $this->roomModel    = new Room();
        $this->invoiceModel = new Invoice();
        $this->notifModel   = new Notification();
        $this->settings     = new Settings();
    }

    /* ── Admin / Receptionist: All bookings ──────────────────── */

    public function index(): void
    {
        Auth::requireRole(['admin', 'receptionist']);
        $page   = (int)($_GET['page']   ?? 1);
        $search = trim($_GET['search']  ?? '');
        $status = trim($_GET['status']  ?? '');

        $pager = $this->bookingModel->getAll($page, $search, $status);
        $this->view('admin/bookings/index', [
            'pageTitle' => 'All Bookings',
            'pager'     => $pager,
            'search'    => $search,
            'status'    => $status,
        ]);
    }

    public function view(string $id): void
    {
        Auth::requireRole(['admin', 'receptionist']);
        $booking = $this->bookingModel->getDetails((int)$id);
        if (!$booking) { Auth::flash('error','Booking not found.'); Helper::redirect('/admin/bookings'); }

        $this->view('admin/bookings/view', ['pageTitle' => 'Booking #'.$booking['booking_ref'], 'booking' => $booking]);
    }

    /* ── Search available rooms (public + customer) ──────────── */

    public function search(): void
    {
        $checkIn   = $_GET['check_in']  ?? '';
        $checkOut  = $_GET['check_out'] ?? '';
        $guests    = max(1, (int)($_GET['guests'] ?? 1));
        $category  = $_GET['category']  ?? '';
        $maxPrice  = (float)($_GET['max_price'] ?? 0);

        $rooms      = [];
        $categories = $this->roomModel->getCategories();

        if ($checkIn && $checkOut && strtotime($checkIn) < strtotime($checkOut)) {
            $rooms = $this->roomModel->getAvailable($checkIn, $checkOut, $guests, $category, $maxPrice);
        }

        $this->view('public/search_results', compact('rooms','checkIn','checkOut','guests','categories','category','maxPrice') + ['pageTitle'=>'Available Rooms']);
    }

    /* ── Booking form ─────────────────────────────────────────── */

    public function book(string $roomId): void
    {
        Auth::requireLogin('/auth/login');

        $room      = $this->roomModel->getWithDetails((int)$roomId);
        if (!$room || $room['status'] !== 'available') {
            Auth::flash('error','Room is not available.');
            Helper::redirect('/rooms');
        }

        $checkIn  = $_GET['check_in']  ?? '';
        $checkOut = $_GET['check_out'] ?? '';
        $guests   = max(1, (int)($_GET['guests'] ?? 1));
        $taxRate  = (float)$this->settings->get('tax_rate', '10');

        $this->view('customer/bookings/create', compact('room','checkIn','checkOut','guests','taxRate') + ['pageTitle'=>'Book Room']);
    }

    /* ── Create booking (POST) ───────────────────────────────── */

    public function store(): void
    {
        Auth::requireLogin('/auth/login');
        if (!Auth::verifyCsrf()) { Auth::flash('error','Invalid token.'); Helper::redirect('/rooms'); }

        $data     = Validator::sanitizeInput($_POST);
        $v = (new Validator($data))
            ->required('room_id',   'Room')
            ->required('check_in',  'Check-In Date')
            ->required('check_out', 'Check-Out Date')
            ->date('check_in')
            ->date('check_out')
            ->dateAfter('check_out', 'check_in', 'Check-Out');

        if ($v->fails()) {
            Auth::flash('error', $v->firstError());
            Helper::redirect('/rooms');
        }

        $roomId   = (int)$data['room_id'];
        $checkIn  = $data['check_in'];
        $checkOut = $data['check_out'];
        $guests   = max(1, (int)($data['guests'] ?? 1));

        // Double-booking check
        if (!$this->bookingModel->isRoomAvailable($roomId, $checkIn, $checkOut)) {
            Auth::flash('error','Room is no longer available for selected dates.');
            Helper::redirect('/rooms');
        }

        $room     = $this->roomModel->findById($roomId);
        $nights   = Helper::nightsBetween($checkIn, $checkOut);
        $subtotal = $room['price_per_night'] * $nights;
        $taxRate  = (float)$this->settings->get('tax_rate', '10');
        $taxAmt   = $subtotal * ($taxRate / 100);

        // Coupon
        $discount = 0;
        $couponId = null;
        if (!empty($data['coupon_code'])) {
            $coupon = $this->applyCoupon($data['coupon_code'], $nights, $subtotal);
            if ($coupon) {
                $discount = $coupon['discount'];
                $couponId = $coupon['id'];
            }
        }

        $total = $subtotal - $discount + $taxAmt;

        $bookingId = $this->bookingModel->createBooking([
            'user_id'       => Auth::id(),
            'room_id'       => $roomId,
            'coupon_id'     => $couponId,
            'check_in'      => $checkIn,
            'check_out'     => $checkOut,
            'nights'        => $nights,
            'guests'        => $guests,
            'room_rate'     => $room['price_per_night'],
            'subtotal'      => $subtotal,
            'discount'      => $discount,
            'tax_rate'      => $taxRate,
            'tax_amount'    => $taxAmt,
            'total_amount'  => $total,
            'special_requests' => $data['special_requests'] ?? '',
            'status'        => 'pending',
            'payment_status'=> 'pending',
        ]);

        // Auto-confirm if enabled
        $autoConfirm = $this->settings->get('booking_auto_confirm', '0');
        if ($autoConfirm === '1') {
            $this->bookingModel->confirm($bookingId);
        }

        // Notification
        $this->notifModel->create(
            Auth::id(), 'booking_created',
            'Booking Confirmed!',
            "Your booking has been created. Reference: " . $this->bookingModel->findById($bookingId)['booking_ref'],
            "/customer/bookings/view/{$bookingId}"
        );

        Helper::logActivity(Database::getInstance()->getConnection(), Auth::id(), 'booking.create', "Booking #{$bookingId} created");
        Auth::flash('success', 'Booking created successfully!');
        Helper::redirect('/customer/bookings/view/' . $bookingId);
    }

    /* ── Cancel booking ──────────────────────────────────────── */

    public function cancel(string $id): void
    {
        Auth::requireLogin();
        if (!Auth::verifyCsrf()) Helper::jsonResponse(['success'=>false,'message'=>'Invalid token.']);

        $booking = $this->bookingModel->findById((int)$id);
        if (!$booking) Helper::jsonResponse(['success'=>false,'message'=>'Booking not found.']);

        // Customers can only cancel their own bookings
        if (Auth::isCustomer() && $booking['user_id'] !== Auth::id()) {
            Helper::jsonResponse(['success'=>false,'message'=>'Unauthorized.']);
        }

        $reason = $_POST['reason'] ?? '';
        $this->bookingModel->cancel((int)$id, $reason);
        Helper::jsonResponse(['success' => true, 'message' => 'Booking cancelled.']);
    }

    /* ── Admin: confirm / check-in / check-out ───────────────── */

    public function confirm(string $id): void
    {
        Auth::requireRole(['admin','receptionist']);
        $this->bookingModel->confirm((int)$id);
        $booking = $this->bookingModel->findById((int)$id);
        $this->notifModel->create($booking['user_id'], 'booking_confirmed', 'Booking Confirmed', 'Your booking has been confirmed!', '/customer/bookings/view/'.$id);
        Auth::flash('success','Booking confirmed.');
        Helper::redirect('/admin/bookings/view/' . $id);
    }

    public function checkIn(string $id): void
    {
        Auth::requireRole(['admin','receptionist']);
        $this->bookingModel->checkIn((int)$id);
        Auth::flash('success','Guest checked in successfully.');
        Helper::redirect('/receptionist/checkin');
    }

    public function checkOut(string $id): void
    {
        Auth::requireRole(['admin','receptionist']);
        $this->bookingModel->checkOut((int)$id);
        $this->invoiceModel->createForBooking((int)$id);
        Auth::flash('success','Guest checked out. Invoice generated.');
        Helper::redirect('/receptionist/checkin');
    }

    /* ── Walk-in booking (receptionist) ─────────────────────── */

    public function walkIn(): void
    {
        Auth::requireRole(['admin','receptionist']);

        $rooms      = [];
        $categories = $this->roomModel->getCategories();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCsrf()) { Auth::flash('error','Invalid token.'); Helper::redirect('/receptionist/walk-in'); }

            $data    = Validator::sanitizeInput($_POST);
            $roomId  = (int)$data['room_id'];
            $checkIn = $data['check_in'];  $checkOut = $data['check_out'];
            $nights  = Helper::nightsBetween($checkIn, $checkOut);

            if (!$this->bookingModel->isRoomAvailable($roomId, $checkIn, $checkOut)) {
                Auth::flash('error','Room is not available.');
            } else {
                $room    = $this->roomModel->findById($roomId);
                $taxRate = (float)$this->settings->get('tax_rate','10');
                $sub     = $room['price_per_night'] * $nights;
                $taxAmt  = $sub * ($taxRate / 100);
                $total   = $sub + $taxAmt;

                // Create temp user if walk-in
                $userModel = new User();
                $guestId   = $userModel->findByEmail($data['guest_email']);
                if (!$guestId) {
                    $guestId = $userModel->createUser([
                        'role_id'    => 3,
                        'first_name' => $data['guest_first_name'],
                        'last_name'  => $data['guest_last_name'],
                        'email'      => $data['guest_email'],
                        'phone'      => $data['guest_phone'] ?? '',
                        'password'   => bin2hex(random_bytes(8)),
                        'status'     => 'active',
                        'email_verified' => 1,
                    ]);
                } else {
                    $guestId = $guestId['id'];
                }

                $bookingId = $this->bookingModel->createBooking([
                    'user_id'       => $guestId,
                    'room_id'       => $roomId,
                    'created_by'    => Auth::id(),
                    'check_in'      => $checkIn,
                    'check_out'     => $checkOut,
                    'nights'        => $nights,
                    'guests'        => (int)($data['guests'] ?? 1),
                    'room_rate'     => $room['price_per_night'],
                    'subtotal'      => $sub,
                    'discount'      => 0,
                    'tax_rate'      => $taxRate,
                    'tax_amount'    => $taxAmt,
                    'total_amount'  => $total,
                    'special_requests' => $data['special_requests'] ?? '',
                    'status'        => 'confirmed',
                    'payment_status'=> 'pending',
                ]);

                Auth::flash('success', 'Walk-in booking created!');
                Helper::redirect('/admin/bookings/view/' . $bookingId);
            }
        }

        $this->view('receptionist/bookings/walk_in', compact('categories') + ['pageTitle' => 'Walk-In Booking', 'rooms' => $rooms]);
    }

    /* ── Calendar API ────────────────────────────────────────── */

    public function calendar(): void
    {
        Auth::requireRole(['admin','receptionist']);
        $start = $_GET['start'] ?? date('Y-m-01');
        $end   = $_GET['end']   ?? date('Y-m-t');

        $bookings = $this->bookingModel->getForCalendar($start, $end);
        $events   = array_map(fn($b) => [
            'id'    => $b['id'],
            'title' => "#{$b['room_number']} — {$b['guest_name']}",
            'start' => $b['check_in'],
            'end'   => $b['check_out'],
            'color' => match($b['status']) {
                'confirmed'  => '#0d6efd',
                'checked_in' => '#198754',
                default      => '#6c757d',
            },
            'url'   => APP_URL . '/admin/bookings/view/' . $b['id'],
        ], $bookings);

        Helper::jsonResponse($events);
    }

    /* ── Coupon validation API ───────────────────────────────── */

    public function validateCoupon(): void
    {
        Auth::requireLogin();
        $code    = trim($_POST['code']    ?? '');
        $nights  = (int)($_POST['nights'] ?? 1);
        $amount  = (float)($_POST['amount'] ?? 0);

        $result = $this->applyCoupon($code, $nights, $amount);
        if ($result) {
            Helper::jsonResponse(['valid' => true, 'discount' => $result['discount'], 'message' => "Coupon applied! You save " . Helper::money($result['discount'])]);
        } else {
            Helper::jsonResponse(['valid' => false, 'message' => 'Invalid or expired coupon.']);
        }
    }

    /* ── Private helpers ─────────────────────────────────────── */

    private function applyCoupon(string $code, int $nights, float $amount): ?array
    {
        $db      = Database::getInstance()->getConnection();
        $stmt    = $db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at >= CURDATE()) AND (max_uses IS NULL OR used_count < max_uses) LIMIT 1");
        $stmt->execute([$code]);
        $coupon  = $stmt->fetch();

        if (!$coupon || $nights < $coupon['min_nights']) return null;

        $discount = $coupon['type'] === 'percent'
            ? $amount * ($coupon['value'] / 100)
            : min($coupon['value'], $amount);

        $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?")->execute([$coupon['id']]);

        return ['id' => $coupon['id'], 'discount' => round($discount, 2)];
    }

    private function view(string $view, array $data = []): void
    {
        extract($data);
        $authUser = Auth::user();
        include VIEWS_PATH . '/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
    }
}
