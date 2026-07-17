<?php
/**
 * PublicController — Hotel website public pages
 */
class PublicController
{
    private Room    $roomModel;
    private Review  $reviewModel;
    private Settings $settings;

    public function __construct()
    {
        $this->roomModel   = new Room();
        $this->reviewModel = new Review();
        $this->settings    = new Settings();
    }

    public function index(): void
    {
        $featuredRooms = $this->roomModel->getFeatured(6);
        $categories    = $this->roomModel->getCategories();
        $avgRating     = $this->reviewModel->avgRating();

        $reviews = Database::getInstance()->getConnection()
            ->query("SELECT rv.*, CONCAT(u.first_name,' ',u.last_name) AS guest_name, u.avatar, r.name AS room_name
                       FROM reviews rv JOIN users u ON u.id=rv.user_id JOIN rooms r ON r.id=rv.room_id
                      WHERE rv.status='approved' ORDER BY rv.created_at DESC LIMIT 6")
            ->fetchAll();

        $this->view('public/home', compact('featuredRooms','categories','avgRating','reviews') + ['pageTitle' => $this->settings->get('hotel_name','Grand Azure Hotel')]);
    }

    public function rooms(): void
    {
        $page      = (int)($_GET['page']      ?? 1);
        $search    = trim($_GET['search']     ?? '');
        $category  = trim($_GET['category']   ?? '');
        $status    = 'available';
        $pager     = $this->roomModel->getAllWithCategory($page, $search, $category, $status);
        $categories= $this->roomModel->getCategories();

        $this->view('public/rooms', compact('pager','categories','search','category') + ['pageTitle' => 'Our Rooms']);
    }

    public function roomDetail(string $id): void
    {
        $room = $this->roomModel->getWithDetails((int)$id);
        if (!$room) { http_response_code(404); $this->view('errors/404', ['pageTitle'=>'Not Found']); return; }

        $related = $this->roomModel->getFeatured(3);
        $this->view('public/room_detail', compact('room','related') + ['pageTitle' => $room['name']]);
    }

    public function about():   void { $this->view('public/about',   ['pageTitle' => 'About Us']); }
    public function contact(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Store or email contact form — for now just flash success
            Auth::flash('success','Thank you for your message! We will get back to you shortly.');
        }
        $this->view('public/contact', ['pageTitle' => 'Contact Us']);
    }
    public function gallery(): void { $this->view('public/gallery', ['pageTitle' => 'Gallery']); }
    public function faq():     void { $this->view('public/faq',     ['pageTitle' => 'FAQ']); }

    public function newsletter(): void
    {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        if (!$email) Helper::jsonResponse(['success'=>false,'message'=>'Invalid email.']);

        try {
            Database::getInstance()->getConnection()
                ->prepare("INSERT IGNORE INTO newsletter (email) VALUES (?)")
                ->execute([$email]);
            Helper::jsonResponse(['success'=>true,'message'=>'Thank you for subscribing!']);
        } catch (Exception) {
            Helper::jsonResponse(['success'=>false,'message'=>'Subscription failed. Please try again.']);
        }
    }

    private function view(string $view, array $data = []): void
    {
        extract($data);
        $authUser = Auth::check() ? Auth::user() : null;
        $settings = $this->settings->loadAll();
        include VIEWS_PATH . '/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
    }
}
