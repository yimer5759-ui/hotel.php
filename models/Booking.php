<?php
/**
 * Booking Model
 */

class Booking extends Model
{
    protected string $table = 'bookings';

    /* ── Create ─────────────────────────────────────────────── */

    public function createBooking(array $data): int|false
    {
        // Generate unique booking reference
        $data['booking_ref'] = $this->generateRef();
        return $this->insert($data);
    }

    private function generateRef(): string
    {
        do {
            $ref = 'BK-' . date('Y') . strtoupper(substr(uniqid(), -6));
        } while ($this->findBy('booking_ref', $ref));
        return $ref;
    }

    /* ── Double-booking guard ───────────────────────────────── */

    public function isRoomAvailable(int $roomId, string $checkIn, string $checkOut, int $excludeBookingId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM bookings
                 WHERE room_id = ?
                   AND status NOT IN ('cancelled','checked_out','no_show')
                   AND id != ?
                   AND (
                         (check_in  < ? AND check_out > ?)
                      OR (check_in >= ? AND check_in  < ?)
                   )";
        return ((int) $this->queryScalar($sql, [$roomId, $excludeBookingId, $checkOut, $checkIn, $checkIn, $checkOut])) === 0;
    }

    /* ── Listings ───────────────────────────────────────────── */

    public function getAll(int $page = 1, string $search = '', string $status = ''): array
    {
        $params = [];
        $where  = ['1=1'];

        if ($search) {
            $where[]  = "(b.booking_ref LIKE ? OR CONCAT(u.first_name,' ',u.last_name) LIKE ? OR r.room_number LIKE ?)";
            $like     = "%{$search}%";
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        if ($status) {
            $where[]  = "b.status = ?";
            $params[] = $status;
        }

        $sql = "SELECT b.*, CONCAT(u.first_name,' ',u.last_name) AS guest_name, u.email AS guest_email,
                       r.room_number, r.name AS room_name, rc.name AS category_name
                  FROM bookings b
                  JOIN users u  ON u.id  = b.user_id
                  JOIN rooms r  ON r.id  = b.room_id
                  JOIN room_categories rc ON rc.id = r.category_id
                 WHERE " . implode(' AND ', $where) . "
              ORDER BY b.created_at DESC";

        return $this->paginate($sql, $params, $page);
    }

    public function getByUser(int $userId, int $page = 1): array
    {
        $sql = "SELECT b.*, r.room_number, r.name AS room_name, rc.name AS category_name, r.thumbnail
                  FROM bookings b
                  JOIN rooms r  ON r.id = b.room_id
                  JOIN room_categories rc ON rc.id = r.category_id
                 WHERE b.user_id = ?
              ORDER BY b.created_at DESC";

        return $this->paginate($sql, [$userId], $page);
    }

    public function getDetails(int $id): array|false
    {
        $booking = $this->queryOne(
            "SELECT b.*, CONCAT(u.first_name,' ',u.last_name) AS guest_name,
                    u.email AS guest_email, u.phone AS guest_phone,
                    r.room_number, r.name AS room_name, r.thumbnail,
                    rc.name AS category_name, rc.slug AS category_slug
               FROM bookings b
               JOIN users u  ON u.id  = b.user_id
               JOIN rooms r  ON r.id  = b.room_id
               JOIN room_categories rc ON rc.id = r.category_id
              WHERE b.id = ? LIMIT 1",
            [$id]
        );

        if (!$booking) return false;

        $booking['services'] = $this->query(
            "SELECT * FROM booking_services WHERE booking_id = ?",
            [$id]
        );
        $booking['payments'] = $this->query(
            "SELECT * FROM payments WHERE booking_id = ? ORDER BY created_at DESC",
            [$id]
        );
        $booking['invoice']  = $this->queryOne(
            "SELECT * FROM invoices WHERE booking_id = ? LIMIT 1",
            [$id]
        );

        return $booking;
    }

    /* ── Status updates ─────────────────────────────────────── */

    public function confirm(int $id): bool
    {
        return $this->update($id, ['status' => 'confirmed']);
    }

    public function checkIn(int $id): bool
    {
        $this->update($id, ['status' => 'checked_in', 'checked_in_at' => date('Y-m-d H:i:s')]);
        // Mark room as booked
        $booking = $this->findById($id);
        if ($booking) {
            $this->db->prepare("UPDATE rooms SET status = 'booked' WHERE id = ?")->execute([$booking['room_id']]);
        }
        return true;
    }

    public function checkOut(int $id): bool
    {
        $booking = $this->findById($id);
        $this->update($id, ['status' => 'checked_out', 'checked_out_at' => date('Y-m-d H:i:s')]);
        if ($booking) {
            $this->db->prepare("UPDATE rooms SET status = 'available' WHERE id = ?")->execute([$booking['room_id']]);
        }
        return true;
    }

    public function cancel(int $id, string $reason = ''): bool
    {
        $booking = $this->findById($id);
        $this->update($id, [
            'status'       => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'cancel_reason' => $reason,
        ]);
        if ($booking) {
            $this->db->prepare("UPDATE rooms SET status = 'available' WHERE id = ? AND status = 'booked'")
                     ->execute([$booking['room_id']]);
        }
        return true;
    }

    /* ── Calendar / availability ────────────────────────────── */

    public function getForCalendar(string $start, string $end): array
    {
        return $this->query(
            "SELECT b.id, b.booking_ref, b.check_in, b.check_out, b.status,
                    CONCAT(u.first_name,' ',u.last_name) AS guest_name,
                    r.room_number, r.name AS room_name
               FROM bookings b
               JOIN users u ON u.id = b.user_id
               JOIN rooms r ON r.id = b.room_id
              WHERE b.status NOT IN ('cancelled','no_show')
                AND b.check_in <= ? AND b.check_out >= ?
           ORDER BY b.check_in ASC",
            [$end, $start]
        );
    }

    public function getTodayArrivals(): array
    {
        return $this->query(
            "SELECT b.*, CONCAT(u.first_name,' ',u.last_name) AS guest_name,
                    r.room_number, r.name AS room_name
               FROM bookings b
               JOIN users u ON u.id = b.user_id
               JOIN rooms r ON r.id = b.room_id
              WHERE b.check_in = CURDATE() AND b.status = 'confirmed'
           ORDER BY b.check_in ASC"
        );
    }

    public function getTodayDepartures(): array
    {
        return $this->query(
            "SELECT b.*, CONCAT(u.first_name,' ',u.last_name) AS guest_name,
                    r.room_number, r.name AS room_name
               FROM bookings b
               JOIN users u ON u.id = b.user_id
               JOIN rooms r ON r.id = b.room_id
              WHERE b.check_out = CURDATE() AND b.status = 'checked_in'
           ORDER BY b.check_out ASC"
        );
    }

    /* ── Reports ────────────────────────────────────────────── */

    public function getRevenueByMonth(int $year): array
    {
        return $this->query(
            "SELECT MONTH(created_at) AS month, SUM(total_amount) AS revenue, COUNT(*) AS bookings
               FROM bookings
              WHERE YEAR(created_at) = ? AND status != 'cancelled' AND payment_status = 'paid'
           GROUP BY MONTH(created_at)
           ORDER BY month ASC",
            [$year]
        );
    }

    public function getRevenueByPeriod(string $start, string $end): array
    {
        return $this->query(
            "SELECT DATE(created_at) AS day, SUM(total_amount) AS revenue, COUNT(*) AS bookings
               FROM bookings
              WHERE DATE(created_at) BETWEEN ? AND ?
                AND status != 'cancelled' AND payment_status = 'paid'
           GROUP BY DATE(created_at)
           ORDER BY day ASC",
            [$start, $end]
        );
    }

    public function totalRevenue(): float
    {
        return (float) $this->queryScalar(
            "SELECT SUM(total_amount) FROM bookings WHERE status != 'cancelled' AND payment_status = 'paid'"
        );
    }

    public function getRecentActivity(int $limit = 10): array
    {
        return $this->query(
            "SELECT b.*, CONCAT(u.first_name,' ',u.last_name) AS guest_name,
                    r.room_number, r.name AS room_name
               FROM bookings b
               JOIN users u ON u.id = b.user_id
               JOIN rooms r ON r.id = b.room_id
           ORDER BY b.created_at DESC
              LIMIT {$limit}"
        );
    }
}
