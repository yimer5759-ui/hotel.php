<?php class Invoice extends Model {
    protected string $table = 'invoices';

    public function createForBooking(int $bookingId): int|false
    {
        $existing = $this->queryOne("SELECT id FROM invoices WHERE booking_id = ? LIMIT 1", [$bookingId]);
        if ($existing) return $existing['id'];

        $no = 'INV-' . date('Y') . '-' . str_pad((int)$this->queryScalar("SELECT COUNT(*)+1 FROM invoices"), 4, '0', STR_PAD_LEFT);
        return $this->insert(['invoice_no' => $no, 'booking_id' => $bookingId, 'issued_at' => date('Y-m-d H:i:s')]);
    }

    public function getWithBooking(int $invoiceId): array|false
    {
        return $this->queryOne(
            "SELECT i.*, b.booking_ref, b.check_in, b.check_out, b.nights, b.guests,
                    b.room_rate, b.subtotal, b.discount, b.tax_rate, b.tax_amount, b.total_amount,
                    b.special_requests, b.status AS booking_status, b.payment_status,
                    CONCAT(u.first_name,' ',u.last_name) AS guest_name,
                    u.email AS guest_email, u.phone AS guest_phone,
                    r.room_number, r.name AS room_name, r.floor,
                    rc.name AS category_name
               FROM invoices i
               JOIN bookings b ON b.id = i.booking_id
               JOIN users u    ON u.id = b.user_id
               JOIN rooms r    ON r.id = b.room_id
               JOIN room_categories rc ON rc.id = r.category_id
              WHERE i.id = ? LIMIT 1",
            [$invoiceId]
        );
    }

    public function getByBooking(int $bookingId): array|false
    {
        return $this->queryOne("SELECT * FROM invoices WHERE booking_id = ? LIMIT 1", [$bookingId]);
    }

    public function getByUser(int $userId): array
    {
        return $this->query(
            "SELECT i.*, b.booking_ref, b.total_amount, b.check_in, b.check_out,
                    r.room_number, r.name AS room_name
               FROM invoices i
               JOIN bookings b ON b.id = i.booking_id
               JOIN rooms r    ON r.id = b.room_id
              WHERE b.user_id = ?
           ORDER BY i.issued_at DESC",
            [$userId]
        );
    }
}
