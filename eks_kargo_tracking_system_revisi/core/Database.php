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
        $configFile = dirname(__DIR__) . '/config/database.php';
        $config = is_file($configFile) ? require $configFile : [];

        if (!is_array($config)) {
            throw new \RuntimeException('Database configuration must return an array.');
        }

        $this->host = (string)($config['host'] ?? getenv('KARGOTRACK_DB_HOST') ?: $this->host);
        $this->dbName = (string)($config['database'] ?? getenv('KARGOTRACK_DB_NAME') ?: $this->dbName);
        $this->user = (string)($config['username'] ?? getenv('KARGOTRACK_DB_USER') ?: $this->user);
        $this->pass = (string)($config['password'] ?? getenv('KARGOTRACK_DB_PASSWORD') ?: $this->pass);

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
            throw new \RuntimeException(
                'Database connection failed. Check the hosting database settings.',
                0,
                $e
            );
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
