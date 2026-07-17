<?php
/**
 * CustomerController — Customer portal
 */
class CustomerController
{
    private User        $userModel;
    private Booking     $bookingModel;
    private Invoice     $invoiceModel;
    private Notification $notifModel;
    private Review      $reviewModel;

    public function __construct()
    {
        Auth::requireRole('customer');
        $this->userModel    = new User();
        $this->bookingModel = new Booking();
        $this->invoiceModel = new Invoice();
        $this->notifModel   = new Notification();
        $this->reviewModel  = new Review();
    }

    public function dashboard(): void
    {
        $userId   = Auth::id();
        $pager    = $this->bookingModel->getByUser($userId);
        $recent   = array_slice($pager['data'], 0, 5);
        $unread   = $this->notifModel->countUnread($userId);

        $stats = [
            'total'       => $pager['total'],
            'confirmed'   => 0,
            'checked_in'  => 0,
            'completed'   => 0,
        ];
        foreach ($pager['data'] as $b) {
            if ($b['status'] === 'confirmed')   $stats['confirmed']++;
            if ($b['status'] === 'checked_in')  $stats['checked_in']++;
            if ($b['status'] === 'checked_out') $stats['completed']++;
        }

        $this->view('customer/dashboard', compact('recent','stats','unread') + ['pageTitle' => 'My Dashboard']);
    }

    public function bookings(): void
    {
        $page  = (int)($_GET['page'] ?? 1);
        $pager = $this->bookingModel->getByUser(Auth::id(), $page);
        $this->view('customer/bookings/index', ['pageTitle' => 'My Bookings', 'pager' => $pager]);
    }

    public function viewBooking(string $id): void
    {
        $booking = $this->bookingModel->getDetails((int)$id);
        if (!$booking || $booking['user_id'] != Auth::id()) {
            Auth::flash('error','Booking not found.'); Helper::redirect('/customer/bookings');
        }
        $this->view('customer/bookings/view', ['pageTitle' => 'Booking Details', 'booking' => $booking]);
    }

    public function profile(): void
    {
        $user   = $this->userModel->getFullProfile(Auth::id());
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCsrf()) { $errors[] = 'Invalid token.'; }
            else {
                $data = Validator::sanitizeInput($_POST);
                $v    = (new Validator($data))->required('first_name')->required('last_name')->required('email')->email('email');

                if ($v->fails()) {
                    $errors = array_values($v->errors());
                } else {
                    $update = ['first_name'=>$data['first_name'],'last_name'=>$data['last_name'],'phone'=>$data['phone']??''];

                    if (!empty($_FILES['avatar']['name'])) {
                        $av = Helper::uploadImage($_FILES['avatar'], 'avatars');
                        if ($av) {
                            $update['avatar'] = $av;
                            $_SESSION['user_avatar'] = $av;
                        }
                    }
                    $this->userModel->update(Auth::id(), $update);
                    $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
                    Auth::flash('success','Profile updated.');
                    Helper::redirect('/customer/profile');
                }
            }
        }

        $this->view('customer/profile/index', compact('user','errors') + ['pageTitle' => 'My Profile']);
    }

    public function changePassword(): void
    {
        if (!Auth::verifyCsrf()) Helper::jsonResponse(['success'=>false,'message'=>'Invalid token.']);

        $user = $this->userModel->findById(Auth::id());
        $data = Validator::sanitizeInput($_POST);

        if (!$this->userModel->verifyPassword($data['current_password'] ?? '', $user['password'])) {
            Helper::jsonResponse(['success'=>false,'message'=>'Current password is incorrect.']);
        }

        $v = (new Validator($data))->required('new_password','New Password')->min('new_password',8,'New Password')->matches('confirm_password','new_password','Confirm Password');
        if ($v->fails()) Helper::jsonResponse(['success'=>false,'message'=>$v->firstError()]);

        $this->userModel->updatePassword(Auth::id(), $data['new_password']);
        Helper::jsonResponse(['success'=>true,'message'=>'Password changed successfully.']);
    }

    public function invoices(): void
    {
        $invoices = $this->invoiceModel->getByUser(Auth::id());
        $this->view('customer/invoices/index', compact('invoices') + ['pageTitle' => 'My Invoices']);
    }

    public function submitReview(string $bookingId): void
    {
        if (!Auth::verifyCsrf()) { Auth::flash('error','Invalid token.'); Helper::redirect('/customer/bookings'); }

        if (!$this->reviewModel->canReview(Auth::id(), (int)$bookingId)) {
            Auth::flash('error','You cannot review this booking.');
            Helper::redirect('/customer/bookings');
        }

        $booking = $this->bookingModel->findById((int)$bookingId);
        $data    = Validator::sanitizeInput($_POST);
        $v       = (new Validator($data))->required('rating')->required('title','Review Title')->required('body','Review');

        if ($v->fails()) { Auth::flash('error',$v->firstError()); Helper::redirect('/customer/bookings/view/'.$bookingId); }

        $this->reviewModel->insert([
            'booking_id' => (int)$bookingId,
            'user_id'    => Auth::id(),
            'room_id'    => $booking['room_id'],
            'rating'     => min(5, max(1,(int)$data['rating'])),
            'title'      => $data['title'],
            'body'       => $data['body'],
            'status'     => 'pending',
        ]);

        Auth::flash('success','Review submitted and awaiting approval. Thank you!');
        Helper::redirect('/customer/bookings/view/' . $bookingId);
    }

    public function notifications(): void
    {
        $this->notifModel->markAllRead(Auth::id());
        $notifs = $this->notifModel->getForUser(Auth::id());
        $this->view('customer/notifications', compact('notifs') + ['pageTitle' => 'Notifications']);
    }

    private function view(string $view, array $data = []): void
    {
        extract($data);
        $authUser = Auth::user();
        include VIEWS_PATH . '/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
    }
}


/**
 * ReceptionistController
 */
class ReceptionistController
{
    private Booking $bookingModel;
    private Room    $roomModel;

    public function __construct()
    {
        Auth::requireRole(['admin','receptionist']);
        $this->bookingModel = new Booking();
        $this->roomModel    = new Room();
    }

    public function dashboard(): void
    {
        $arrivals   = $this->bookingModel->getTodayArrivals();
        $departures = $this->bookingModel->getTodayDepartures();
        $roomStats  = [];
        foreach ($this->roomModel->countByStatus() as $r) $roomStats[$r['status']] = $r['total'];

        $this->view('receptionist/dashboard', compact('arrivals','departures','roomStats') + ['pageTitle' => 'Receptionist Dashboard']);
    }

    public function checkInPanel(): void
    {
        $arrivals   = $this->bookingModel->getTodayArrivals();
        $departures = $this->bookingModel->getTodayDepartures();
        $this->view('receptionist/checkin/index', compact('arrivals','departures') + ['pageTitle' => 'Check-In / Check-Out']);
    }

    private function view(string $view, array $data = []): void
    {
        extract($data);
        $authUser = Auth::user();
        include VIEWS_PATH . '/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
    }
}


/**
 * PaymentController
 */
class PaymentController
{
    private Payment $paymentModel;
    private Booking $bookingModel;
    private Invoice $invoiceModel;

    public function __construct()
    {
        Auth::requireRole(['admin','receptionist']);
        $this->paymentModel = new Payment();
        $this->bookingModel = new Booking();
        $this->invoiceModel = new Invoice();
    }

    public function index(): void
    {
        $page   = (int)($_GET['page']   ?? 1);
        $search = trim($_GET['search']  ?? '');
        $status = trim($_GET['status']  ?? '');
        $pager  = $this->paymentModel->getAll($page, $search, $status);
        $this->view('admin/payments/index', compact('pager','search','status') + ['pageTitle' => 'Payments']);
    }

    public function recordPayment(string $bookingId): void
    {
        if (!Auth::verifyCsrf()) Helper::jsonResponse(['success'=>false,'message'=>'Invalid token.']);

        $data = Validator::sanitizeInput($_POST);
        $v    = (new Validator($data))->required('amount')->numeric('amount')->required('method');
        if ($v->fails()) Helper::jsonResponse(['success'=>false,'message'=>$v->firstError()]);

        $id = $this->paymentModel->createPayment([
            'booking_id'     => (int)$bookingId,
            'amount'         => (float)$data['amount'],
            'method'         => $data['method'],
            'status'         => 'completed',
            'transaction_id' => $data['transaction_id'] ?? '',
            'notes'          => $data['notes'] ?? '',
        ]);

        $this->invoiceModel->createForBooking((int)$bookingId);
        Helper::jsonResponse(['success'=>true,'payment_id'=>$id]);
    }

    private function view(string $view, array $data = []): void
    {
        extract($data);
        $authUser = Auth::user();
        include VIEWS_PATH . '/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
    }
}


/**
 * InvoiceController
 */
class InvoiceController
{
    private Invoice $invoiceModel;

    public function __construct()
    {
        $this->invoiceModel = new Invoice();
    }

    public function view(string $id): void
    {
        Auth::requireLogin();
        $invoice = $this->invoiceModel->getWithBooking((int)$id);
        if (!$invoice) { Auth::flash('error','Invoice not found.'); Helper::redirect('/'); }

        // Customers can only view their own
        if (Auth::isCustomer() && $invoice['user_id'] != Auth::id()) {
            Helper::redirect('/customer/invoices');
        }

        $invoice['services'] = Database::getInstance()->getConnection()
            ->prepare("SELECT * FROM booking_services WHERE booking_id = ?")
            ->execute([$invoice['booking_id']]) ? [] : [];

        $stmt = Database::getInstance()->getConnection()->prepare("SELECT * FROM booking_services WHERE booking_id = ?");
        $stmt->execute([$invoice['booking_id']]);
        $invoice['services'] = $stmt->fetchAll();

        $this->view('shared/invoice_view', compact('invoice') + ['pageTitle' => 'Invoice '.$invoice['invoice_no']]);
    }

    public function print(string $id): void
    {
        Auth::requireLogin();
        $invoice = $this->invoiceModel->getWithBooking((int)$id);
        if (!$invoice) { Auth::flash('error','Invoice not found.'); Helper::redirect('/'); }

        $stmt = Database::getInstance()->getConnection()->prepare("SELECT * FROM booking_services WHERE booking_id = ?");
        $stmt->execute([$invoice['booking_id']]);
        $invoice['services'] = $stmt->fetchAll();

        // Render print-only view
        include VIEWS_PATH . '/shared/invoice_print.php';
    }

    private function view(string $view, array $data = []): void
    {
        extract($data);
        $authUser = Auth::user();
        include VIEWS_PATH . '/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
    }
}


/**
 * ReviewController — Admin manages reviews
 */
class ReviewController
{
    private Review $reviewModel;

    public function __construct()
    {
        Auth::requireRole('admin');
        $this->reviewModel = new Review();
    }

    public function index(): void
    {
        $page   = (int)($_GET['page']   ?? 1);
        $status = trim($_GET['status']  ?? '');
        $pager  = $this->reviewModel->getAllWithDetails($page, $status);
        $this->view('admin/reviews/index', compact('pager','status') + ['pageTitle' => 'Reviews']);
    }

    public function approve(string $id): void
    {
        $this->reviewModel->approve((int)$id);
        Helper::jsonResponse(['success'=>true]);
    }

    public function reject(string $id): void
    {
        $this->reviewModel->reject((int)$id);
        Helper::jsonResponse(['success'=>true]);
    }

    public function delete(string $id): void
    {
        $this->reviewModel->delete((int)$id);
        Helper::jsonResponse(['success'=>true]);
    }

    private function view(string $view, array $data = []): void
    {
        extract($data);
        $authUser = Auth::user();
        include VIEWS_PATH . '/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
    }
}


/**
 * ReportController
 */
class ReportController
{
    private Booking $bookingModel;
    private Room    $roomModel;

    public function __construct()
    {
        Auth::requireRole('admin');
        $this->bookingModel = new Booking();
        $this->roomModel    = new Room();
    }

    public function index(): void
    {
        $period = $_GET['period'] ?? 'monthly';
        $year   = (int)($_GET['year'] ?? date('Y'));
        $start  = $_GET['start'] ?? date('Y-m-01');
        $end    = $_GET['end']   ?? date('Y-m-d');

        $revenueByMonth  = $this->bookingModel->getRevenueByMonth($year);
        $revenueByPeriod = $this->bookingModel->getRevenueByPeriod($start, $end);
        $totalRevenue    = $this->bookingModel->totalRevenue();
        $totalBookings   = $this->bookingModel->count();
        $roomStats       = [];
        foreach ($this->roomModel->countByStatus() as $r) $roomStats[$r['status']] = $r['total'];

        $this->view('admin/reports/index', compact('period','year','start','end','revenueByMonth','revenueByPeriod','totalRevenue','totalBookings','roomStats') + ['pageTitle' => 'Reports']);
    }

    private function view(string $view, array $data = []): void
    {
        extract($data);
        $authUser = Auth::user();
        include VIEWS_PATH . '/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
    }
}


/**
 * SettingsController
 */
class SettingsController
{
    private Settings $settings;

    public function __construct()
    {
        Auth::requireRole('admin');
        $this->settings = new Settings();
    }

    public function index(): void
    {
        $all = $this->settings->loadAll();
        $this->view('admin/settings/index', compact('all') + ['pageTitle' => 'Hotel Settings']);
    }

    public function save(): void
    {
        if (!Auth::verifyCsrf()) { Auth::flash('error','Invalid token.'); Helper::redirect('/admin/settings'); }

        $allowed = [
            'hotel_name','hotel_email','hotel_phone','hotel_address','hotel_description',
            'currency','currency_symbol','tax_rate','check_in_time','check_out_time',
            'timezone','smtp_host','smtp_port','smtp_user','smtp_from','smtp_from_name',
            'booking_auto_confirm','cancellation_hours',
            'facebook_url','twitter_url','instagram_url',
        ];

        $data = [];
        foreach ($allowed as $key) {
            if (isset($_POST[$key])) $data[$key] = trim($_POST[$key]);
        }
        $this->settings->saveMany($data);

        Helper::logActivity(Database::getInstance()->getConnection(), Auth::id(), 'settings.save', 'Hotel settings updated');
        Auth::flash('success','Settings saved successfully.');
        Helper::redirect('/admin/settings');
    }

    private function view(string $view, array $data = []): void
    {
        extract($data);
        $authUser = Auth::user();
        include VIEWS_PATH . '/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
    }
}
