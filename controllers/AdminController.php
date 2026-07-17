<?php
/**
 * AdminController — Dashboard statistics
 */

class AdminController
{
    private Booking     $bookingModel;
    private Room        $roomModel;
    private User        $userModel;
    private Notification $notifModel;

    public function __construct()
    {
        Auth::requireRole('admin');
        $this->bookingModel = new Booking();
        $this->roomModel    = new Room();
        $this->userModel    = new User();
        $this->notifModel   = new Notification();
    }

    public function dashboard(): void
    {
        // Room stats
        $roomStats = [];
        foreach ($this->roomModel->countByStatus() as $r) {
            $roomStats[$r['status']] = $r['total'];
        }

        // Monthly revenue (current year)
        $revenueData  = $this->bookingModel->getRevenueByMonth((int)date('Y'));
        $months       = array_fill(1, 12, ['revenue' => 0, 'bookings' => 0]);
        foreach ($revenueData as $r) {
            $months[(int)$r['month']] = ['revenue' => $r['revenue'], 'bookings' => $r['bookings']];
        }

        $data = [
            'pageTitle'       => 'Admin Dashboard',
            'totalRooms'      => array_sum($roomStats),
            'availableRooms'  => $roomStats['available']    ?? 0,
            'occupiedRooms'   => $roomStats['booked']       ?? 0,
            'maintenanceRooms'=> $roomStats['maintenance']  ?? 0,
            'totalBookings'   => $this->bookingModel->count(),
            'totalCustomers'  => $this->userModel->countByRole('customer'),
            'totalRevenue'    => $this->bookingModel->totalRevenue(),
            'recentBookings'  => $this->bookingModel->getRecentActivity(8),
            'todayArrivals'   => $this->bookingModel->getTodayArrivals(),
            'todayDepartures' => $this->bookingModel->getTodayDepartures(),
            'monthlyRevenue'  => array_column($months, 'revenue'),
            'monthlyBookings' => array_column($months, 'bookings'),
            'unreadNotifs'    => $this->notifModel->countUnread(Auth::id()),
        ];

        $this->view('admin/dashboard', $data);
    }

    /* ── Customer management ──────────────────────────────── */

    public function customers(): void
    {
        $page   = (int)($_GET['page'] ?? 1);
        $search = trim($_GET['search'] ?? '');

        $pager  = $this->userModel->getCustomers($page, $search);
        $this->view('admin/customers/index', [
            'pageTitle' => 'Customers',
            'pager'     => $pager,
            'search'    => $search,
        ]);
    }

    public function viewCustomer(string $id): void
    {
        $user = $this->userModel->getFullProfile((int)$id);
        if (!$user) { Auth::flash('error','Customer not found.'); Helper::redirect('/admin/customers'); }

        $bookings = $this->bookingModel->getByUser((int)$id);
        $this->view('admin/customers/view', [
            'pageTitle' => 'Customer: ' . $user['first_name'],
            'customer'  => $user,
            'bookings'  => $bookings['data'],
        ]);
    }

    public function toggleUserStatus(string $id): void
    {
        $user = $this->userModel->findById((int)$id);
        if (!$user) { Helper::jsonResponse(['success'=>false,'message'=>'Not found.'], 404); }

        $newStatus = $user['status'] === 'active' ? 'banned' : 'active';
        $this->userModel->update((int)$id, ['status' => $newStatus]);
        Helper::jsonResponse(['success' => true, 'status' => $newStatus]);
    }

    /* ── Helpers ─────────────────────────────────────────────── */

    private function view(string $view, array $data = []): void
    {
        extract($data);
        $authUser = Auth::user();
        include VIEWS_PATH . '/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
    }
}
