<?php class Review extends Model {
    protected string $table = 'reviews';

    public function getAllWithDetails(int $page = 1, string $status = ''): array
    {
        $params = []; $where = ['1=1'];
        if ($status) { $where[] = "rv.status = ?"; $params[] = $status; }
        $sql = "SELECT rv.*, CONCAT(u.first_name,' ',u.last_name) AS guest_name,
                       u.avatar, r.room_number, r.name AS room_name, b.booking_ref
                  FROM reviews rv
                  JOIN users u ON u.id = rv.user_id
                  JOIN rooms r ON r.id = rv.room_id
                  JOIN bookings b ON b.id = rv.booking_id
                 WHERE " . implode(' AND ', $where) . "
              ORDER BY rv.created_at DESC";
        return $this->paginate($sql, $params, $page);
    }

    public function canReview(int $userId, int $bookingId): bool
    {
        $booking = $this->queryOne("SELECT * FROM bookings WHERE id = ? AND user_id = ? AND status = 'checked_out'", [$bookingId, $userId]);
        if (!$booking) return false;
        $exists = $this->queryScalar("SELECT COUNT(*) FROM reviews WHERE booking_id = ? AND user_id = ?", [$bookingId, $userId]);
        return $exists == 0;
    }

    public function approve(int $id): bool { return $this->update($id, ['status' => 'approved']); }
    public function reject(int $id):  bool { return $this->update($id, ['status' => 'rejected']); }

    public function avgRating(): float
    {
        return (float)$this->queryScalar("SELECT AVG(rating) FROM reviews WHERE status = 'approved'");
    }
}

class Settings extends Model {
    protected string $table = 'settings';
    private array $cache = [];

    public function get(string $key, mixed $default = ''): mixed
    {
        if (empty($this->cache)) $this->loadAll();
        return $this->cache[$key] ?? $default;
    }

    public function loadAll(): array
    {
        $rows = $this->query("SELECT `key`, `value` FROM settings");
        foreach ($rows as $r) $this->cache[$r['key']] = $r['value'];
        return $this->cache;
    }

    public function set(string $key, string $value): void
    {
        $exists = $this->queryScalar("SELECT COUNT(*) FROM settings WHERE `key` = ?", [$key]);
        if ($exists) {
            $this->db->prepare("UPDATE settings SET `value` = ? WHERE `key` = ?")->execute([$value, $key]);
        } else {
            $this->insert(['key' => $key, 'value' => $value]);
        }
        $this->cache[$key] = $value;
    }

    public function saveMany(array $data): void
    {
        foreach ($data as $k => $v) $this->set($k, $v);
    }

    public function getGroup(string $group): array
    {
        return $this->query("SELECT `key`, `value` FROM settings WHERE `group` = ?", [$group]);
    }
}

class Notification extends Model {
    protected string $table = 'notifications';

    public function create(int $userId, string $type, string $title, string $message, string $link = ''): void
    {
        $this->insert(['user_id'=>$userId,'type'=>$type,'title'=>$title,'message'=>$message,'link'=>$link]);
    }

    public function getForUser(int $userId, int $limit = 20): array
    {
        return $this->query(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT {$limit}",
            [$userId]
        );
    }

    public function countUnread(int $userId): int
    {
        return (int)$this->queryScalar("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0", [$userId]);
    }

    public function markAllRead(int $userId): void
    {
        $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$userId]);
    }
}
