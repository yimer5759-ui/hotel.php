<?php class Payment extends Model {
    protected string $table = 'payments';

    public function createPayment(array $data): int|false
    {
        if (!isset($data['paid_at']) && ($data['status'] ?? '') === 'completed') {
            $data['paid_at'] = date('Y-m-d H:i:s');
        }
        $id = $this->insert($data);
        if ($id && ($data['status'] ?? '') === 'completed') {
            $this->db->prepare("UPDATE bookings SET payment_status = 'paid' WHERE id = ?")
                     ->execute([$data['booking_id']]);
        }
        return $id;
    }

    public function getByBooking(int $bookingId): array
    {
        return $this->query("SELECT * FROM payments WHERE booking_id = ? ORDER BY created_at DESC", [$bookingId]);
    }

    public function getAll(int $page = 1, string $search = '', string $status = ''): array
    {
        $params = [];
        $where  = ['1=1'];
        if ($search) {
            $where[] = "(b.booking_ref LIKE ? OR CONCAT(u.first_name,' ',u.last_name) LIKE ?)";
            $like = "%{$search}%"; $params[] = $like; $params[] = $like;
        }
        if ($status) { $where[] = "p.status = ?"; $params[] = $status; }

        $sql = "SELECT p.*, b.booking_ref, b.total_amount AS booking_total,
                       CONCAT(u.first_name,' ',u.last_name) AS guest_name
                  FROM payments p
                  JOIN bookings b ON b.id = p.booking_id
                  JOIN users u    ON u.id = b.user_id
                 WHERE " . implode(' AND ', $where) . "
              ORDER BY p.created_at DESC";
        return $this->paginate($sql, $params, $page);
    }

    public function totalRevenue(): float
    {
        return (float)$this->queryScalar("SELECT SUM(amount) FROM payments WHERE status = 'completed'");
    }
}
