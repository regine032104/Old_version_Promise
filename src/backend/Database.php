<?php
/**
 * Minimal PDO Database wrapper (singleton)
 * Follows the structure described in the project's `apply this concept.md` attachment.
 */
class Database {
    private $host = 'localhost';
    private $db   = 'wedding_shop';
    private $user = 'root';
    private $pass = 'agatha0507';
    private $charset = 'utf8mb4';

    /** @var PDO|null */
    private $pdo = null;
    /** @var Database|null */
    private static $instance = null;

    // Prevent external construction
    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // In many environments you'd log this instead of die()
            die('DB Connection failed: ' . $e->getMessage());
        }
    }

    // Prevent cloning
    private function __clone() {}
    // __wakeup must be public for PHP's unserialize() to call it without visibility errors
    public function __wakeup() {}

    // Get singleton instance
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Expose the raw PDO connection for compatibility
    public function getConnection(): PDO {
        return $this->pdo;
    }

    // Convenience helpers
    public function query(string $sql, array $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function fetchOne(string $sql, array $params = []): ?array {
        $stmt = $this->query($sql, $params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function execute(string $sql, array $params = []): bool {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function lastInsertId(): string {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool {
        return $this->pdo->commit();
    }

    public function rollBack(): bool {
        return $this->pdo->rollBack();
    }
}

// Backwards-compatible global helper: create/get the singleton and return PDO
if (!function_exists('db_get_pdo')) {
    function db_get_pdo() {
        return Database::getInstance()->getConnection();
    }
}
