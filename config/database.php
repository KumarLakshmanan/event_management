<?php
/**
 * Database Connection Class
 * Provides a single connection point to the database
 */
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $host = "localhost";
        $port = "3306";
        $dbname = "eventmanagement";
        $user = "root";
        $password = "";

        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

        try {
            $this->connection = new PDO($dsn, $user, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Database Connection Error: " . $e->getMessage();
            exit;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    /**
     * Execute a query and return the results as an associative array
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Query Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Execute a single row query and return the result as an associative array
     */
    public function queryOne($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Query Error: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Alternative name for queryOne for compatibility
     */
    public function querySingle($sql, $params = []) {
        return $this->queryOne($sql, $params);
    }

    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);


            return true;
        } catch (PDOException $e) {
            echo "Execute Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Get the last inserted ID
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
}
?>