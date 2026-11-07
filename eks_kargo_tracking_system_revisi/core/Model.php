<?php
namespace Core;

use PDO;
use Core\Database;

/**
 * Abstract Base Model
 * Menyediakan implementasi OOP Abstraction & Inheritance untuk seluruh entitas database
 */
abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    /**
     * Constructor menginisialisasi koneksi Database Singleton
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Ambil semua data
     */
    public function all(string $columns = '*', string $orderBy = ''): array
    {
        $sql = "SELECT {$columns} FROM `{$this->table}`";
        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Cari record berdasarkan ID
     */
    public function find($id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Query berdasarkan kondisi WHERE
     */
    public function where(array $conditions, string $orderBy = '', ?int $limit = null): array
    {
        $clauses = [];
        $params = [];
        foreach ($conditions as $column => $value) {
            $clauses[] = "`{$column}` = :{$column}";
            $params[":{$column}"] = $value;
        }

        $sql = "SELECT * FROM `{$this->table}`";
        if (!empty($clauses)) {
            $sql .= " WHERE " . implode(" AND ", $clauses);
        }
        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }
        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cari 1 record pertama berdasarkan kondisi
     */
    public function firstWhere(array $conditions): ?array
    {
        $res = $this->where($conditions, '', 1);
        return !empty($res) ? $res[0] : null;
    }

    /**
     * Insert data baru (Prepared Statements)
     */
    public function create(array $data): int
    {
        $fields = array_keys($data);
        $columns = implode(', ', array_map(fn($f) => "`{$f}`", $fields));
        $placeholders = implode(', ', array_map(fn($f) => ":{$f}", $fields));

        $sql = "INSERT INTO `{$this->table}` ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);

        $params = [];
        foreach ($data as $key => $val) {
            $params[":{$key}"] = $val;
        }

        $stmt->execute($params);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update data (Prepared Statements)
     */
    public function update($id, array $data): bool
    {
        $sets = [];
        $params = [":id" => $id];

        foreach ($data as $key => $val) {
            $sets[] = "`{$key}` = :set_{$key}";
            $params[":set_{$key}"] = $val;
        }

        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $sets) . " WHERE `{$this->primaryKey}` = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Hapus data berdasarkan ID
     */
    public function delete($id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Hitung total baris
     */
    public function count(string $whereClause = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}`";
        if (!empty($whereClause)) {
            $sql .= " WHERE {$whereClause}";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Custom Query dengan Prepared Statements
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

