<?php
/**
 * Base Model
 * Provides PDO helpers: query, find, insert, update, delete, paginate
 */

abstract class Model
{
    protected PDO   $db;
    protected string $table  = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /* ── Query helpers ─────────────────────────────────────── */

    /**
     * Execute a raw query and return all rows.
     */
    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a raw query and return a single row.
     */
    protected function queryOne(string $sql, array $params = []): array|false
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Execute a query and return the scalar value of the first column.
     */
    protected function queryScalar(string $sql, array $params = []): mixed
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /* ── CRUD ──────────────────────────────────────────────── */

    public function findAll(string $orderBy = 'id DESC'): array
    {
        return $this->query("SELECT * FROM `{$this->table}` ORDER BY {$orderBy}");
    }

    public function findById(int $id): array|false
    {
        return $this->queryOne(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?",
            [$id]
        );
    }

    public function findBy(string $column, mixed $value): array|false
    {
        return $this->queryOne(
            "SELECT * FROM `{$this->table}` WHERE `{$column}` = ? LIMIT 1",
            [$value]
        );
    }

    public function findAllBy(string $column, mixed $value, string $orderBy = 'id DESC'): array
    {
        return $this->query(
            "SELECT * FROM `{$this->table}` WHERE `{$column}` = ? ORDER BY {$orderBy}",
            [$value]
        );
    }

    public function count(string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        if ($where) $sql .= " WHERE {$where}";
        return (int) $this->queryScalar($sql, $params);
    }

    /**
     * Insert a row. Returns last insert ID.
     */
    public function insert(array $data): int|false
    {
        $columns = implode('`, `', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql  = "INSERT INTO `{$this->table}` (`{$columns}`) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update a row by primary key.
     */
    public function update(int $id, array $data): bool
    {
        $sets = implode(', ', array_map(fn($c) => "`{$c}` = ?", array_keys($data)));
        $sql  = "UPDATE `{$this->table}` SET {$sets} WHERE `{$this->primaryKey}` = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([...array_values($data), $id]);
    }

    /**
     * Delete a row by primary key.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?"
        );
        return $stmt->execute([$id]);
    }

    /* ── Pagination ─────────────────────────────────────────── */

    /**
     * Paginated query. Returns ['data'=>[], 'total'=>int, 'pages'=>int].
     */
    protected function paginate(
        string $sql,
        array  $params    = [],
        int    $page      = 1,
        int    $perPage   = PER_PAGE,
        string $countSql  = ''
    ): array {
        $page    = max(1, $page);
        $offset  = ($page - 1) * $perPage;

        // Count query
        if ($countSql) {
            $total = (int) $this->queryScalar($countSql, $params);
        } else {
            $countQuery = preg_replace('/SELECT .+? FROM/is', 'SELECT COUNT(*) FROM', $sql, 1);
            $countQuery = preg_replace('/ORDER BY.+$/i', '', $countQuery);
            $total = (int) $this->queryScalar($countQuery, $params);
        }

        $sql  .= " LIMIT {$perPage} OFFSET {$offset}";
        $data  = $this->query($sql, $params);

        return [
            'data'    => $data,
            'total'   => $total,
            'pages'   => (int) ceil($total / $perPage),
            'current' => $page,
            'perPage' => $perPage,
        ];
    }
}
