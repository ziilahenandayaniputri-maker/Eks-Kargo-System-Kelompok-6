<?php
namespace Core;

use PDO;
use PDOException;

/**
 * Database Connection Handler (Singleton Pattern)
 * Menggunakan PDO Strict Mode: ERRMODE_EXCEPTION & EMULATE_PREPARES = false
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    // Encapsulation: Database credentials
    private string $host = '127.0.0.1';
    private string $dbName = 'kargotrack_db';
    private string $user = 'root';
    private string $pass = '';
    private string $charset = 'utf8mb4';

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset={$this->charset}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // Wajib Strict Mode
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
        ];

        try {
            $this->connection = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // Attempt to create database if not exists
            try {
                $rootDsn = "mysql:host={$this->host};charset={$this->charset}";
                $rootPdo = new PDO($rootDsn, $this->user, $this->pass, $options);
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->dbName}` CHARACTER SET {$this->charset} COLLATE utf8mb4_unicode_ci");
                $this->connection = new PDO($dsn, $this->user, $this->pass, $options);
            } catch (PDOException $ex) {
                die("Koneksi Database Gagal: " . htmlspecialchars($ex->getMessage(), ENT_QUOTES, 'UTF-8'));
            }
        }
    }

    /**
     * Get Singleton Instance
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get PDO Connection
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Prevent clone
     */
    private function __clone() {}

    /**
     * Prevent unserialize
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}

