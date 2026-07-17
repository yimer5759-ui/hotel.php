<?php
/**
 * RoomController — Full CRUD with image upload and amenity sync
 */

class RoomController
{
    private Room $roomModel;

    public function __construct()
    {
        Auth::requireRole(['admin', 'receptionist']);
        $this->roomModel = new Room();
    }

    public function index(): void
    {
        $page     = (int)($_GET['page']     ?? 1);
        $search   = trim($_GET['search']    ?? '');
        $category = trim($_GET['category']  ?? '');
        $status   = trim($_GET['status']    ?? '');

        $pager      = $this->roomModel->getAllWithCategory($page, $search, $category, $status);
        $categories = $this->roomModel->getCategories();

        $this->view('admin/rooms/index', [
            'pageTitle'  => 'Rooms',
            'pager'      => $pager,
            'categories' => $categories,
            'search'     => $search,
            'category'   => $category,
            'status'     => $status,
        ]);
    }

    public function add(): void
    {
        Auth::requireRole('admin');
        $errors     = [];
        $old        = [];
        $categories = $this->roomModel->getCategories();
        $amenities  = $this->roomModel->getAllAmenities();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCsrf()) { $errors[] = 'Invalid security token.'; }
            else {
                $data = Validator::sanitizeInput($_POST);
                $v = (new Validator($data))
                    ->required('room_number', 'Room Number')
                    ->required('category_id', 'Category')
                    ->required('name',        'Room Name')
                    ->required('price_per_night', 'Price Per Night')
                    ->numeric('price_per_night', 'Price Per Night')
                    ->required('capacity',   'Capacity')
                    ->numeric('capacity',    'Capacity');

                if ($v->fails()) {
                    $errors = array_values($v->errors());
                    $old    = $data;
                } else {
                    // Handle thumbnail upload
                    $thumbnail = null;
                    if (!empty($_FILES['thumbnail']['name'])) {
                        $thumb = Helper::uploadImage($_FILES['thumbnail'], 'rooms');
                        if ($thumb) $thumbnail = $thumb;
                        else $errors[] = 'Thumbnail upload failed (check type/size).';
                    }

                    if (empty($errors)) {
                        $roomId = $this->roomModel->insert([
                            'category_id'    => (int)$data['category_id'],
                            'room_number'    => $data['room_number'],
                            'floor'          => (int)($data['floor'] ?? 1),
                            'name'           => $data['name'],
                            'description'    => $data['description'] ?? '',
                            'price_per_night'=> (float)$data['price_per_night'],
                            'capacity'       => (int)$data['capacity'],
                            'size_sqft'      => (int)($data['size_sqft'] ?? 0),
                            'thumbnail'      => $thumbnail,
                            'status'         => $data['status'] ?? 'available',
                            'is_featured'    => isset($data['is_featured']) ? 1 : 0,
                        ]);

                        // Sync amenities
                        if (!empty($_POST['amenities'])) {
                            $this->roomModel->syncAmenities($roomId, array_map('intval', $_POST['amenities']));
                        }

                        // Additional images
                        if (!empty($_FILES['images']['name'][0])) {
                            foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
                                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                                    $img = Helper::uploadImage([
                                        'name'     => $_FILES['images']['name'][$i],
                                        'tmp_name' => $tmp,
                                        'error'    => $_FILES['images']['error'][$i],
                                        'size'     => $_FILES['images']['size'][$i],
                                        'type'     => $_FILES['images']['type'][$i],
                                    ], 'rooms');
                                    if ($img) $this->roomModel->addImage($roomId, $img, '', $i);
                                }
                            }
                        }

                        Helper::logActivity(Database::getInstance()->getConnection(), Auth::id(), 'room.create', "Room #{$data['room_number']} created");
                        Auth::flash('success', 'Room added successfully.');
                        Helper::redirect('/admin/rooms');
                    }
                }
            }
        }

        $this->view('admin/rooms/add', compact('pageTitle', 'errors', 'old', 'categories', 'amenities') + ['pageTitle' => 'Add Room']);
    }

    public function edit(string $id): void
    {
        Auth::requireRole('admin');
        $room = $this->roomModel->getWithDetails((int)$id);
        if (!$room) { Auth::flash('error','Room not found.'); Helper::redirect('/admin/rooms'); }

        $errors     = [];
        $categories = $this->roomModel->getCategories();
        $amenities  = $this->roomModel->getAllAmenities();
        $selectedAmenities = array_column($room['amenities'], 'id');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCsrf()) { $errors[] = 'Invalid security token.'; }
            else {
                $data = Validator::sanitizeInput($_POST);
                $v = (new Validator($data))
                    ->required('room_number','Room Number')
                    ->required('name',       'Room Name')
                    ->required('price_per_night','Price Per Night')
                    ->numeric('price_per_night','Price Per Night');

                if ($v->fails()) {
                    $errors = array_values($v->errors());
                } else {
                    $update = [
                        'category_id'    => (int)$data['category_id'],
                        'room_number'    => $data['room_number'],
                        'floor'          => (int)($data['floor'] ?? 1),
                        'name'           => $data['name'],
                        'description'    => $data['description'] ?? '',
                        'price_per_night'=> (float)$data['price_per_night'],
                        'capacity'       => (int)$data['capacity'],
                        'size_sqft'      => (int)($data['size_sqft'] ?? 0),
                        'status'         => $data['status'] ?? 'available',
                        'is_featured'    => isset($data['is_featured']) ? 1 : 0,
                    ];

                    if (!empty($_FILES['thumbnail']['name'])) {
                        $thumb = Helper::uploadImage($_FILES['thumbnail'], 'rooms');
                        if ($thumb) $update['thumbnail'] = $thumb;
                    }

                    $this->roomModel->update((int)$id, $update);
                    $this->roomModel->syncAmenities((int)$id, array_map('intval', $_POST['amenities'] ?? []));

                    if (!empty($_FILES['images']['name'][0])) {
                        foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
                            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                                $img = Helper::uploadImage([
                                    'name'     => $_FILES['images']['name'][$i],
                                    'tmp_name' => $tmp,
                                    'error'    => $_FILES['images']['error'][$i],
                                    'size'     => $_FILES['images']['size'][$i],
                                    'type'     => $_FILES['images']['type'][$i],
                                ], 'rooms');
                                if ($img) $this->roomModel->addImage((int)$id, $img, '', $i);
                            }
                        }
                    }

                    Auth::flash('success', 'Room updated successfully.');
                    Helper::redirect('/admin/rooms');
                }
            }
        }

        $this->view('admin/rooms/edit', compact('room','categories','amenities','selectedAmenities','errors') + ['pageTitle' => 'Edit Room']);
    }

    public function delete(string $id): void
    {
        Auth::requireRole('admin');
        if (!Auth::verifyCsrf()) Helper::jsonResponse(['success'=>false,'message'=>'Invalid token.']);

        $room = $this->roomModel->findById((int)$id);
        if (!$room) Helper::jsonResponse(['success'=>false,'message'=>'Room not found.']);

        // Remove thumbnail
        if ($room['thumbnail']) {
            $p = UPLOADS_PATH . '/rooms/' . $room['thumbnail'];
            if (file_exists($p)) unlink($p);
        }

        $this->roomModel->delete((int)$id);
        Helper::logActivity(Database::getInstance()->getConnection(), Auth::id(), 'room.delete', "Room #{$room['room_number']} deleted");
        Helper::jsonResponse(['success' => true]);
    }

    public function deleteImage(string $id): void
    {
        Auth::requireRole('admin');
        $this->roomModel->deleteImage((int)$id);
        Helper::jsonResponse(['success' => true]);
    }

    private function view(string $view, array $data = []): void
    {
        extract($data);
        $authUser = Auth::user();
        include VIEWS_PATH . '/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
    }
}
