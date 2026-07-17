<?php
/**
 * AuthController — Registration, Login, Logout, Password Reset
 */

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /* ── Login ─────────────────────────────────────────────── */

    public function login(): void
    {
        if (Auth::check()) { $this->redirectByRole(); }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCsrf()) { $error = 'Invalid security token.'; }
            else {
                $email    = trim($_POST['email']    ?? '');
                $password = trim($_POST['password'] ?? '');

                $user = $this->userModel->findByEmail($email);
                if ($user && $user['status'] === 'active' && $this->userModel->verifyPassword($password, $user['password'])) {
                    Auth::login($user);
                    $this->userModel->updateLastLogin($user['id']);
                    Helper::logActivity(
                        Database::getInstance()->getConnection(),
                        $user['id'], 'login', 'User logged in from ' . ($_SERVER['REMOTE_ADDR'] ?? '')
                    );
                    $intended = $_SESSION['intended'] ?? '';
                    unset($_SESSION['intended']);
                    if ($intended) Helper::redirect($intended);
                    $this->redirectByRole();
                }
                $error = 'Invalid email or password.';
            }
        }

        $this->view('auth/login', ['error' => $error, 'pageTitle' => 'Login']);
    }

    /* ── Register ───────────────────────────────────────────── */

    public function register(): void
    {
        if (Auth::check()) { $this->redirectByRole(); }

        $errors = [];
        $old    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCsrf()) { $errors[] = 'Invalid security token.'; }
            else {
                $data = Validator::sanitizeInput($_POST);
                $v = (new Validator($data))
                    ->required('first_name','First Name')
                    ->required('last_name', 'Last Name')
                    ->required('email',     'Email')
                    ->email('email')
                    ->required('password', 'Password')
                    ->min('password', 8, 'Password')
                    ->required('confirm_password', 'Confirm Password')
                    ->matches('confirm_password', 'password', 'Confirm Password');

                if ($v->fails()) {
                    $errors = array_values($v->errors());
                    $old    = $data;
                } elseif ($this->userModel->findByEmail($data['email'])) {
                    $errors[] = 'Email address is already registered.';
                    $old      = $data;
                } else {
                    $userId = $this->userModel->createUser([
                        'role_id'    => 3,
                        'first_name' => $data['first_name'],
                        'last_name'  => $data['last_name'],
                        'email'      => $data['email'],
                        'phone'      => $data['phone'] ?? '',
                        'password'   => $data['password'],
                        'status'     => 'active',
                        'email_verified' => 1,
                    ]);

                    $user = $this->userModel->getFullProfile($userId);
                    Auth::login($user);
                    Auth::flash('success', 'Welcome! Your account has been created.');
                    Helper::redirect('/customer/dashboard');
                }
            }
        }

        $this->view('auth/register', ['errors' => $errors, 'old' => $old, 'pageTitle' => 'Register']);
    }

    /* ── Logout ─────────────────────────────────────────────── */

    public function logout(): void
    {
        Helper::logActivity(
            Database::getInstance()->getConnection(),
            Auth::id(), 'logout', 'User logged out'
        );
        Auth::logout();
        Auth::flash('success', 'You have been logged out.');
        Helper::redirect('/auth/login');
    }

    /* ── Forgot Password ────────────────────────────────────── */

    public function forgotPassword(): void
    {
        $message = '';
        $type    = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCsrf()) { $message = 'Invalid token.'; $type = 'danger'; }
            else {
                $email = trim($_POST['email'] ?? '');
                $user  = $this->userModel->findByEmail($email);

                if ($user) {
                    $token   = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    $this->userModel->update($user['id'], [
                        'reset_token'   => $token,
                        'reset_expires' => $expires,
                    ]);
                    // In production, send email with reset link
                    $resetUrl = APP_URL . "/auth/reset-password?token={$token}";
                    // For demo: store in session to display
                    Auth::flash('reset_url', $resetUrl);
                }

                $message = 'If this email exists, a password reset link has been sent.';
                $type    = 'success';
            }
        }

        $this->view('auth/forgot_password', [
            'message'   => $message,
            'type'      => $type,
            'pageTitle' => 'Forgot Password',
        ]);
    }

    /* ── Reset Password ─────────────────────────────────────── */

    public function resetPassword(): void
    {
        $token  = $_GET['token'] ?? '';
        $user   = $token ? $this->userModel->findByResetToken($token) : null;

        if (!$user) {
            Auth::flash('error', 'Invalid or expired reset link.');
            Helper::redirect('/auth/forgot-password');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCsrf()) { $error = 'Invalid token.'; }
            else {
                $data = Validator::sanitizeInput($_POST);
                $v = (new Validator($data))
                    ->required('password', 'Password')
                    ->min('password', 8, 'Password')
                    ->matches('confirm_password', 'password', 'Confirm Password');

                if ($v->fails()) {
                    $error = $v->firstError();
                } else {
                    $this->userModel->updatePassword($user['id'], $data['password']);
                    Auth::flash('success', 'Password reset successfully. Please login.');
                    Helper::redirect('/auth/login');
                }
            }
        }

        $this->view('auth/reset_password', ['error' => $error, 'token' => $token, 'pageTitle' => 'Reset Password']);
    }

    /* ── Helpers ─────────────────────────────────────────────── */

    private function redirectByRole(): never
    {
        match (Auth::role()) {
            'admin'        => Helper::redirect('/admin/dashboard'),
            'receptionist' => Helper::redirect('/receptionist/dashboard'),
            default        => Helper::redirect('/customer/dashboard'),
        };
    }

    private function view(string $view, array $data = []): void
    {
        extract($data);
        $csrf = Auth::csrfField();
        include VIEWS_PATH . '/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
    }
}
