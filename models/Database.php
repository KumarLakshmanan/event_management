<?php
class Database {
    private static $instance = null;
    private $db;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->db = new PDO($dsn, DB_USER, DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Add these settings for better MySQL compatibility
            $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->db->setAttribute(PDO::MYSQL_ATTR_FOUND_ROWS, true);
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
            
            // Bind parameters properly based on their types
            foreach ($params as $key => $value) {
                // If using named parameters
                if (is_string($key)) {
                    $paramKey = (strpos($key, ':') === 0) ? $key : ':' . $key;
                    
                    if (is_int($value)) {
                        $stmt->bindValue($paramKey, $value, PDO::PARAM_INT);
                    } elseif (is_bool($value)) {
                        $stmt->bindValue($paramKey, $value, PDO::PARAM_BOOL);
                    } elseif (is_null($value)) {
                        $stmt->bindValue($paramKey, $value, PDO::PARAM_NULL);
                    } else {
                        $stmt->bindValue($paramKey, $value, PDO::PARAM_STR);
                    }
                }
            }
            
            // Execute with or without parameters
            if (empty($params) || !is_string(key($params))) {
                $stmt->execute($params); // For numeric keys or empty params
            } else {
                $stmt->execute(); // Named parameters were bound above
            }
            
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query execution error: " . $e->getMessage() . " in query: " . $query);
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
        try {
            $stmt = $this->db->prepare($query);
            
            // If using named parameters, need to bind them properly
            if (!empty($params) && is_string(key($params))) {
                foreach ($params as $key => $value) {
                    $paramKey = (strpos($key, ':') === 0) ? $key : ':' . $key;
                    
                    if (is_int($value)) {
                        $stmt->bindValue($paramKey, $value, PDO::PARAM_INT);
                    } elseif (is_bool($value)) {
                        $stmt->bindValue($paramKey, $value, PDO::PARAM_BOOL);
                    } elseif (is_null($value)) {
                        $stmt->bindValue($paramKey, $value, PDO::PARAM_NULL);
                    } else {
                        $stmt->bindValue($paramKey, $value, PDO::PARAM_STR);
                    }
                }
                $stmt->execute();
            } else {
                $stmt->execute($params);
            }
            
            return $stmt->fetch($fetchMode);
        } catch (PDOException $e) {
            error_log("fetchOne error: " . $e->getMessage() . " in query: " . $query);
            return false;
        }
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
        try {
            $stmt = $this->db->prepare($query);
            
            // If using named parameters, need to bind them properly
            if (!empty($params) && is_string(key($params))) {
                foreach ($params as $key => $value) {
                    $paramKey = (strpos($key, ':') === 0) ? $key : ':' . $key;
                    
                    if (is_int($value)) {
                        $stmt->bindValue($paramKey, $value, PDO::PARAM_INT);
                    } elseif (is_bool($value)) {
                        $stmt->bindValue($paramKey, $value, PDO::PARAM_BOOL);
                    } elseif (is_null($value)) {
                        $stmt->bindValue($paramKey, $value, PDO::PARAM_NULL);
                    } else {
                        $stmt->bindValue($paramKey, $value, PDO::PARAM_STR);
                    }
                }
                $stmt->execute();
            } else {
                $stmt->execute($params);
            }
            
            return $stmt->fetchAll($fetchMode);
        } catch (PDOException $e) {
            error_log("fetchAll error: " . $e->getMessage() . " in query: " . $query);
            return [];
        }
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
        
        try {
            $stmt = $this->db->prepare($query);
            
            // If using named parameters, need to bind them properly
            if (!empty($params) && is_string(key($params))) {
                foreach ($params as $key => $value) {
                    $paramKey = (strpos($key, ':') === 0) ? $key : ':' . $key;
                    
                    if (is_int($value)) {
                        $stmt->bindValue($paramKey, $value, PDO::PARAM_INT);
                    } elseif (is_bool($value)) {
                        $stmt->bindValue($paramKey, $value, PDO::PARAM_BOOL);
                    } elseif (is_null($value)) {
                        $stmt->bindValue($paramKey, $value, PDO::PARAM_NULL);
                    } else {
                        $stmt->bindValue($paramKey, $value, PDO::PARAM_STR);
                    }
                }
                $stmt->execute();
            } else {
                $stmt->execute($params);
            }
            
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Count error: " . $e->getMessage() . " in query: " . $query);
            return 0;
        }
    }
}
?>
