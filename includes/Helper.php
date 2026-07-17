<?php
/**
 * Helper — General utilities
 */

class Helper
{
    /* ── Routing ─────────────────────────────────────────────── */

    public static function redirect(string $path, int $code = 302): never
    {
        $url = str_starts_with($path, 'http') ? $path : APP_URL . $path;
        header("Location: {$url}", true, $code);
        exit;
    }

    public static function url(string $path = ''): string
    {
        return APP_URL . '/' . ltrim($path, '/');
    }

    /* ── XSS ─────────────────────────────────────────────────── */

    public static function e(mixed $v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /* ── Currency ────────────────────────────────────────────── */

    public static function money(float $amount, string $symbol = '$'): string
    {
        return $symbol . number_format($amount, 2);
    }

    /* ── Dates ───────────────────────────────────────────────── */

    public static function formatDate(string $date, string $format = 'M d, Y'): string
    {
        return $date ? date($format, strtotime($date)) : '—';
    }

    public static function nightsBetween(string $checkIn, string $checkOut): int
    {
        $d1 = new DateTime($checkIn);
        $d2 = new DateTime($checkOut);
        return max(0, (int) $d2->diff($d1)->days);
    }

    public static function daysFromNow(string $date): int
    {
        return (int) (new DateTime())->diff(new DateTime($date))->days;
    }

    /* ── File Uploads ─────────────────────────────────────────── */

    public static function uploadImage(array $file, string $subDir = 'rooms'): string|false
    {
        if ($file['error'] !== UPLOAD_ERR_OK) return false;
        if ($file['size']  > MAX_UPLOAD_SIZE)  return false;

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) return false;

        $ext      = match($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => 'jpg',
        };
        $filename = uniqid('img_', true) . '.' . $ext;
        $dir      = UPLOADS_PATH . '/' . $subDir;
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        return move_uploaded_file($file['tmp_name'], $dir . '/' . $filename) ? $filename : false;
    }

    /* ── Text ────────────────────────────────────────────────── */

    public static function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        return mb_strlen($text) > $length
            ? mb_substr($text, 0, $length) . $suffix
            : $text;
    }

    public static function slug(string $text): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
    }

    /* ── Status badges ────────────────────────────────────────── */

    public static function bookingStatusBadge(string $status): string
    {
        $map = [
            'pending'     => ['secondary', 'clock'],
            'confirmed'   => ['primary',   'check-circle'],
            'checked_in'  => ['success',   'door-open'],
            'checked_out' => ['info',      'door-closed'],
            'cancelled'   => ['danger',    'x-circle'],
            'no_show'     => ['warning',   'exclamation-circle'],
        ];
        [$color, $icon] = $map[$status] ?? ['secondary', 'question'];
        $label = ucwords(str_replace('_', ' ', $status));
        return "<span class=\"badge bg-{$color}\"><i class=\"bi bi-{$icon} me-1\"></i>{$label}</span>";
    }

    public static function paymentStatusBadge(string $status): string
    {
        $map = [
            'paid'     => 'success',
            'pending'  => 'warning',
            'partial'  => 'info',
            'refunded' => 'secondary',
        ];
        $color = $map[$status] ?? 'secondary';
        return "<span class=\"badge bg-{$color}\">" . ucfirst($status) . "</span>";
    }

    public static function roomStatusBadge(string $status): string
    {
        $map = [
            'available'   => 'success',
            'booked'      => 'danger',
            'maintenance' => 'warning',
            'inactive'    => 'secondary',
        ];
        $color = $map[$status] ?? 'secondary';
        return "<span class=\"badge bg-{$color}\">" . ucfirst($status) . "</span>";
    }

    /* ── Pagination HTML ──────────────────────────────────────── */

    public static function pagination(array $pager, string $url): string
    {
        if ($pager['pages'] <= 1) return '';
        $html = '<nav><ul class="pagination pagination-sm mb-0">';

        $prev = $pager['current'] - 1;
        $next = $pager['current'] + 1;

        $html .= $pager['current'] > 1
            ? "<li class=\"page-item\"><a class=\"page-link\" href=\"{$url}&page={$prev}\">«</a></li>"
            : "<li class=\"page-item disabled\"><span class=\"page-link\">«</span></li>";

        for ($i = max(1, $pager['current'] - 2); $i <= min($pager['pages'], $pager['current'] + 2); $i++) {
            $active = $i === $pager['current'] ? ' active' : '';
            $html  .= "<li class=\"page-item{$active}\"><a class=\"page-link\" href=\"{$url}&page={$i}\">{$i}</a></li>";
        }

        $html .= $pager['current'] < $pager['pages']
            ? "<li class=\"page-item\"><a class=\"page-link\" href=\"{$url}&page={$next}\">»</a></li>"
            : "<li class=\"page-item disabled\"><span class=\"page-link\">»</span></li>";

        return $html . '</ul></nav>';
    }

    /* ── Log Activity ─────────────────────────────────────────── */

    public static function logActivity(PDO $db, ?int $userId, string $action, string $desc = ''): void
    {
        try {
            $stmt = $db->prepare(
                "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $userId,
                $action,
                $desc,
                $_SERVER['REMOTE_ADDR'] ?? '',
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ]);
        } catch (Exception) { /* non-fatal */ }
    }

    /* ── JSON response ───────────────────────────────────────── */

    public static function jsonResponse(array $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
