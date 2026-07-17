<?php
/**
 * Room Model
 */

class Room extends Model
{
    protected string $table = 'rooms';

    /* ── Listings ───────────────────────────────────────────── */

    public function getAllWithCategory(int $page = 1, string $search = '', string $category = '', string $status = ''): array
    {
        $params = [];
        $where  = ['1=1'];

        if ($search) {
            $where[]  = "(r.name LIKE ? OR r.room_number LIKE ?)";
            $like     = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
        }
        if ($category) {
            $where[]  = "rc.slug = ?";
            $params[] = $category;
        }
        if ($status) {
            $where[]  = "r.status = ?";
            $params[] = $status;
        }

        $whereStr = implode(' AND ', $where);

        $sql = "SELECT r.*, rc.name AS category_name, rc.slug AS category_slug
                  FROM rooms r
                  JOIN room_categories rc ON rc.id = r.category_id
                 WHERE {$whereStr}
              ORDER BY r.room_number ASC";

        return $this->paginate($sql, $params, $page);
    }

    public function getAvailable(string $checkIn, string $checkOut, int $guests = 1, string $category = '', float $maxPrice = 0): array
    {
        $params = [$checkIn, $checkOut, $checkIn, $checkOut, $guests];
        $extra  = '';

        if ($category) {
            $extra   .= " AND rc.slug = ?";
            $params[] = $category;
        }
        if ($maxPrice > 0) {
            $extra   .= " AND r.price_per_night <= ?";
            $params[] = $maxPrice;
        }

        return $this->query(
            "SELECT r.*, rc.name AS category_name, rc.slug AS category_slug
               FROM rooms r
               JOIN room_categories rc ON rc.id = r.category_id
              WHERE r.status = 'available'
                AND r.capacity >= ?
                AND r.id NOT IN (
                    SELECT room_id FROM bookings
                     WHERE status NOT IN ('cancelled','checked_out','no_show')
                       AND (
                             (check_in  < ? AND check_out > ?)
                          OR (check_in >= ? AND check_in  < ?)
                         )
                )
                {$extra}
              ORDER BY r.price_per_night ASC",
            array_merge([$guests, $checkOut, $checkIn, $checkIn, $checkOut], $category ? [$category] : [], $maxPrice > 0 ? [$maxPrice] : [])
        );
    }

    public function getWithDetails(int $id): array|false
    {
        $room = $this->queryOne(
            "SELECT r.*, rc.name AS category_name, rc.slug AS category_slug
               FROM rooms r
               JOIN room_categories rc ON rc.id = r.category_id
              WHERE r.id = ? LIMIT 1",
            [$id]
        );

        if (!$room) return false;

        $room['images']    = $this->getImages($id);
        $room['amenities'] = $this->getAmenities($id);
        $room['reviews']   = $this->getApprovedReviews($id);

        return $room;
    }

    public function getImages(int $roomId): array
    {
        return $this->query(
            "SELECT * FROM room_images WHERE room_id = ? ORDER BY sort_order ASC",
            [$roomId]
        );
    }

    public function getAmenities(int $roomId): array
    {
        return $this->query(
            "SELECT a.* FROM amenities a
               JOIN room_amenities ra ON ra.amenity_id = a.id
              WHERE ra.room_id = ?",
            [$roomId]
        );
    }

    public function getApprovedReviews(int $roomId): array
    {
        return $this->query(
            "SELECT rv.*, u.first_name, u.last_name, u.avatar
               FROM reviews rv
               JOIN users u ON u.id = rv.user_id
              WHERE rv.room_id = ? AND rv.status = 'approved'
           ORDER BY rv.created_at DESC",
            [$roomId]
        );
    }

    public function getFeatured(int $limit = 6): array
    {
        return $this->query(
            "SELECT r.*, rc.name AS category_name
               FROM rooms r
               JOIN room_categories rc ON rc.id = r.category_id
              WHERE r.is_featured = 1 AND r.status = 'available'
           ORDER BY r.price_per_night ASC
              LIMIT {$limit}"
        );
    }

    public function addImage(int $roomId, string $filename, string $caption = '', int $sort = 0): int|false
    {
        return $this->queryScalar(
            "INSERT INTO room_images (room_id, image, caption, sort_order) VALUES (?, ?, ?, ?)",
            [$roomId, $filename, $caption, $sort]
        ) ?: (int) $this->db->lastInsertId();
    }

    public function deleteImage(int $imageId): bool
    {
        $image = $this->queryOne("SELECT * FROM room_images WHERE id = ?", [$imageId]);
        if ($image) {
            $path = UPLOADS_PATH . '/rooms/' . $image['image'];
            if (file_exists($path)) unlink($path);
            $stmt = $this->db->prepare("DELETE FROM room_images WHERE id = ?");
            return $stmt->execute([$imageId]);
        }
        return false;
    }

    public function syncAmenities(int $roomId, array $amenityIds): void
    {
        $this->db->prepare("DELETE FROM room_amenities WHERE room_id = ?")->execute([$roomId]);
        foreach ($amenityIds as $aid) {
            $this->db->prepare("INSERT IGNORE INTO room_amenities (room_id, amenity_id) VALUES (?,?)")
                     ->execute([$roomId, (int)$aid]);
        }
    }

    public function getCategories(): array
    {
        return $this->query("SELECT * FROM room_categories ORDER BY base_price ASC");
    }

    public function getAllAmenities(): array
    {
        return $this->query("SELECT * FROM amenities ORDER BY name ASC");
    }

    /* ── Stats ──────────────────────────────────────────────── */

    public function countByStatus(): array
    {
        return $this->query(
            "SELECT status, COUNT(*) AS total FROM rooms GROUP BY status"
        );
    }

    public function avgRating(int $roomId): float
    {
        return (float) $this->queryScalar(
            "SELECT AVG(rating) FROM reviews WHERE room_id = ? AND status = 'approved'",
            [$roomId]
        );
    }
}
