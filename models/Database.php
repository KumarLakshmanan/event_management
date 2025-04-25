<?php
class Database {
    private static $instance = null;
    private $db;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        try {
            $this->db = new PDO("sqlite:" . DB_PATH);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->exec('PRAGMA foreign_keys = ON;');
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get a singleton instance of Database
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->db;
    }
    
    /**
     * Execute a query with parameters
     * 
     * @param string $query SQL query
     * @param array $params Parameters for the query
     * @return PDOStatement|false
     */
    public function executeQuery($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query execution error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a query and fetch a single row
     * 
     * @param string $query SQL query
     * @param array $params Parameters for the query
     * @param int $fetchMode PDO fetch mode
     * @return mixed The result
     */
    public function fetchOne($query, $params = [], $fetchMode = PDO::FETCH_ASSOC) {
        $stmt = $this->executeQuery($query, $params);
        if ($stmt === false) {
            return false;
        }
        return $stmt->fetch($fetchMode);
    }
    
    /**
     * Execute a query and fetch all rows
     * 
     * @param string $query SQL query
     * @param array $params Parameters for the query
     * @param int $fetchMode PDO fetch mode
     * @return array The results
     */
    public function fetchAll($query, $params = [], $fetchMode = PDO::FETCH_ASSOC) {
        $stmt = $this->executeQuery($query, $params);
        if ($stmt === false) {
            return [];
        }
        return $stmt->fetchAll($fetchMode);
    }
    
    /**
     * Insert a row into a table
     * 
     * @param string $table Table name
     * @param array $data Data to insert (column => value)
     * @return int|false The last insert ID or false on failure
     */
    public function insert($table, $data) {
        // Build column names and placeholders
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($data);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Insert error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update a row in a table
     * 
     * @param string $table Table name
     * @param array $data Data to update (column => value)
     * @param string $where Where clause
     * @param array $params Parameters for where clause
     * @return bool Success or failure
     */
    public function update($table, $data, $where, $params = []) {
        // Build SET part of query
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "$column = :$column";
        }
        $setClause = implode(', ', $setParts);
        
        $query = "UPDATE $table SET $setClause WHERE $where";
        
        // Merge data and where params
        $execParams = array_merge($data, $params);
        
        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute($execParams);
        } catch (PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a row from a table
     * 
     * @param string $table Table name
     * @param string $where Where clause
     * @param array $params Parameters for where clause
     * @return bool Success or failure
     */
    public function delete($table, $where, $params = []) {
        $query = "DELETE FROM $table WHERE $where";
        
        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool Success or failure
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool Success or failure
     */
    public function commit() {
        return $this->db->commit();
    }
    
    /**
     * Rollback a transaction
     * 
     * @return bool Success or failure
     */
    public function rollback() {
        return $this->db->rollBack();
    }
    
    /**
     * Count rows in a table
     * 
     * @param string $table Table name
     * @param string $where Where clause (optional)
     * @param array $params Parameters for where clause (optional)
     * @return int Number of rows
     */
    public function count($table, $where = '', $params = []) {
        $query = "SELECT COUNT(*) FROM $table";
        
        if (!empty($where)) {
            $query .= " WHERE $where";
        }
        
        $stmt = $this->executeQuery($query, $params);
        if ($stmt === false) {
            return 0;
        }
        
        return (int) $stmt->fetchColumn();
    }
}
?>
