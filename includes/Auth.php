<?php
/**
 * Auth — Session & CSRF helpers
 */

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('HBSID');
            session_start();
        }
    }

    /* ── Login / Logout ─────────────────────────────────────── */

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role_slug'];
        $_SESSION['user_avatar']= $user['avatar'] ?? '';
        $_SESSION['logged_in']  = true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']
            );
        }
    }

    /* ── Checks ─────────────────────────────────────────────── */

    public static function check(): bool
    {
        return !empty($_SESSION['logged_in']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): string
    {
        return $_SESSION['user_role'] ?? '';
    }

    public static function user(): array
    {
        return [
            'id'     => $_SESSION['user_id']    ?? 0,
            'name'   => $_SESSION['user_name']   ?? '',
            'email'  => $_SESSION['user_email']  ?? '',
            'role'   => $_SESSION['user_role']   ?? '',
            'avatar' => $_SESSION['user_avatar'] ?? '',
        ];
    }

    /* ── Role guards ────────────────────────────────────────── */

    public static function requireLogin(string $redirect = '/auth/login'): void
    {
        if (!self::check()) {
            $_SESSION['intended'] = $_SERVER['REQUEST_URI'] ?? '';
            Helper::redirect($redirect);
        }
    }

    public static function requireRole(string|array $roles, string $redirect = '/'): void
    {
        self::requireLogin();
        $roles = (array) $roles;
        if (!in_array(self::role(), $roles, true)) {
            Helper::redirect($redirect);
        }
    }

    public static function isAdmin():        bool { return self::role() === 'admin'; }
    public static function isReceptionist(): bool { return self::role() === 'receptionist'; }
    public static function isCustomer():     bool { return self::role() === 'customer'; }

    /* ── CSRF ───────────────────────────────────────────────── */

    public static function generateCsrf(): string
    {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    public static function verifyCsrf(): bool
    {
        $token = $_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $token);
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . self::generateCsrf() . '">';
    }

    /** Flash messages */
    public static function flash(string $key, string $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }

    public static function getFlash(string $key): string
    {
        $msg = $_SESSION['flash'][$key] ?? '';
        unset($_SESSION['flash'][$key]);
        return $msg;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['flash'][$key]);
    }
}
