<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/Database.php';

class User {
    private $db;
    
    // User properties
    private $id;
    private $name;
    private $email;
    private $role;
    private $canApplyDiscount;
    private $createdAt;
    private $updatedAt;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new user
     * 
     * @param string $name User's name
     * @param string $email User's email
     * @param string $password User's password (plain text)
     * @param string $role User's role
     * @return int|false User ID or false on failure
     */
    public function create($name, $email, $password, $role = ROLE_CLIENT) {
        // Validate inputs
        if (empty($name) || empty($email) || empty($password)) {
            return false;
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Check if email already exists
        $user = $this->getByEmail($email);
        if ($user) {
            return false;
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare data for insert
        $data = [
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => $role,
            'can_apply_discount' => $role === ROLE_ADMIN ? 1 : 0
        ];
        
        // Insert user
        return $this->db->insert('users', $data);
    }
    
    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return array|false User data or false if not found
     */
    public function getById($id) {
        return $this->db->fetchOne(
            "SELECT id, name, email, role, can_apply_discount, created_at, updated_at FROM users WHERE id = :id",
            [':id' => $id]
        );
    }
    
    /**
     * Get user by email
     * 
     * @param string $email User email
     * @return array|false User data or false if not found
     */
    public function getByEmail($email) {
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE email = :email",
            [':email' => $email]
        );
    }
    
    /**
     * Update user
     * 
     * @param int $id User ID
     * @param array $data User data to update
     * @return bool Success or failure
     */
    public function update($id, $data) {
        // Ensure id is valid
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        // Remove protected fields
        $allowedFields = ['name', 'email', 'role', 'can_apply_discount'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        // Add updated_at timestamp
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('users', $updateData, 'id = :id', [':id' => $id]);
    }
    
    /**
     * Update user password
     * 
     * @param int $id User ID
     * @param string $password New password (plain text)
     * @return bool Success or failure
     */
    public function updatePassword($id, $password) {
        // Validate inputs
        if (!$id || !is_numeric($id) || empty($password)) {
            return false;
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare data for update
        $data = [
            'password' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->update('users', $data, 'id = :id', [':id' => $id]);
    }
    
    /**
     * Delete user
     * 
     * @param int $id User ID
     * @return bool Success or failure
     */
    public function delete($id) {
        // Validate input
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        return $this->db->delete('users', 'id = :id', [':id' => $id]);
    }
    
    /**
     * Get all users
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @param string $orderBy Order by field
     * @param string $orderDir Order direction
     * @return array Users
     */
    public function getAll($limit = 100, $offset = 0, $orderBy = 'id', $orderDir = 'ASC') {
        // Validate order by field
        $allowedOrderByFields = ['id', 'name', 'email', 'role', 'created_at', 'updated_at'];
        if (!in_array($orderBy, $allowedOrderByFields)) {
            $orderBy = 'id';
        }
        
        // Validate order direction
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        
        return $this->db->fetchAll(
            "SELECT id, name, email, role, can_apply_discount, created_at, updated_at 
             FROM users 
             ORDER BY $orderBy $orderDir 
             LIMIT :limit OFFSET :offset",
            [
                ':limit' => $limit,
                ':offset' => $offset
            ]
        );
    }
    
    /**
     * Count total users
     * 
     * @return int Number of users
     */
    public function countAll() {
        return $this->db->count('users');
    }
    
    /**
     * Get users by role
     * 
     * @param string $role Role to filter by
     * @return array Users with specified role
     */
    public function getByRole($role) {
        return $this->db->fetchAll(
            "SELECT id, name, email, role, can_apply_discount, created_at, updated_at 
             FROM users 
             WHERE role = :role",
            [':role' => $role]
        );
    }
    
    /**
     * Authenticate user
     * 
     * @param string $email User email
     * @param string $password User password (plain text)
     * @return array|false User data without password or false on failure
     */
    public function authenticate($email, $password) {
        // Get user by email
        $user = $this->getByEmail($email);
        
        // Check if user exists and password is correct
        if ($user && password_verify($password, $user['password'])) {
            // Don't return the password
            unset($user['password']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Check if a user is an admin
     * 
     * @param int $id User ID
     * @return bool True if user is an admin, false otherwise
     */
    public function isAdmin($id) {
        $user = $this->getById($id);
        return $user && $user['role'] === ROLE_ADMIN;
    }
    
    /**
     * Check if a user is a manager
     * 
     * @param int $id User ID
     * @return bool True if user is a manager, false otherwise
     */
    public function isManager($id) {
        $user = $this->getById($id);
        return $user && $user['role'] === ROLE_MANAGER;
    }
    
    /**
     * Check if a user is a client
     * 
     * @param int $id User ID
     * @return bool True if user is a client, false otherwise
     */
    public function isClient($id) {
        $user = $this->getById($id);
        return $user && $user['role'] === ROLE_CLIENT;
    }
    
    /**
     * Check if a user can apply discounts
     * 
     * @param int $id User ID
     * @return bool True if user can apply discounts, false otherwise
     */
    public function canApplyDiscount($id) {
        $user = $this->getById($id);
        return $user && (int)$user['can_apply_discount'] === 1;
    }
    
    /**
     * Set user's discount permission
     * 
     * @param int $id User ID
     * @param bool $canApplyDiscount Whether user can apply discounts
     * @return bool Success or failure
     */
    public function setDiscountPermission($id, $canApplyDiscount) {
        return $this->db->update(
            'users', 
            [
                'can_apply_discount' => $canApplyDiscount ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ], 
            'id = :id', 
            [':id' => $id]
        );
    }
}
?>
