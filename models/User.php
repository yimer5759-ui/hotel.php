<?php
/**
 * User Model
 */

class User extends Model
{
    protected string $table = 'users';

    /* ── Auth helpers ──────────────────────────────────────── */

    public function findByEmail(string $email): array|false
    {
        return $this->queryOne(
            "SELECT u.*, r.slug AS role_slug, r.name AS role_name
               FROM users u
               JOIN roles r ON r.id = u.role_id
              WHERE u.email = ? LIMIT 1",
            [$email]
        );
    }

    public function findByResetToken(string $token): array|false
    {
        return $this->queryOne(
            "SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1",
            [$token]
        );
    }

    public function createUser(array $data): int|false
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        return $this->insert($data);
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        return $this->update($id, [
            'password'      => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]),
            'reset_token'   => null,
            'reset_expires' => null,
        ]);
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    /* ── Listing ────────────────────────────────────────────── */

    public function getAllWithRoles(int $page = 1, string $search = ''): array
    {
        $params = [];
        $where  = '';

        if ($search) {
            $where   = "WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
            $like    = "%{$search}%";
            $params  = [$like, $like, $like];
        }

        $sql = "SELECT u.*, r.name AS role_name, r.slug AS role_slug
                  FROM users u
                  JOIN roles r ON r.id = u.role_id
                {$where}
              ORDER BY u.id DESC";

        return $this->paginate($sql, $params, $page);
    }

    public function getCustomers(int $page = 1, string $search = ''): array
    {
        $params = [3];  // role_id = 3 (customer)
        $where  = 'u.role_id = ?';

        if ($search) {
            $where  .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
            $like    = "%{$search}%";
            $params  = array_merge($params, [$like, $like, $like]);
        }

        $sql = "SELECT u.*, COUNT(b.id) AS total_bookings
                  FROM users u
             LEFT JOIN bookings b ON b.user_id = u.id
                 WHERE {$where}
              GROUP BY u.id
              ORDER BY u.created_at DESC";

        return $this->paginate($sql, $params, $page);
    }

    public function getFullProfile(int $id): array|false
    {
        return $this->queryOne(
            "SELECT u.*, r.name AS role_name, r.slug AS role_slug
               FROM users u
               JOIN roles r ON r.id = u.role_id
              WHERE u.id = ? LIMIT 1",
            [$id]
        );
    }

    public function countByRole(string $slug): int
    {
        return (int) $this->queryScalar(
            "SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug = ?",
            [$slug]
        );
    }

    public function updateLastLogin(int $id): void
    {
        $this->update($id, ['last_login' => date('Y-m-d H:i:s')]);
    }
}
