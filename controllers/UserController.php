<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/User.php';

class UserController {
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Get all users with pagination
     * 
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Users and pagination info
     */
    public function getAllUsers($page = 1, $perPage = 10) {
        // Calculate offset
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        
        // Get users
        $users = $this->userModel->getAll($perPage, $offset);
        
        // Get total count
        $total = $this->userModel->countAll();
        
        // Calculate total pages
        $totalPages = ceil($total / $perPage);
        
        return [
            'users' => $users,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage,
                'totalItems' => $total
            ]
        ];
    }
    
    /**
     * Get users by role with pagination
     * 
     * @param string $role Role to filter by
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Users and pagination info
     */
    public function getUsersByRole($role, $page = 1, $perPage = 10) {
        // Get users by role
        $users = $this->userModel->getByRole($role);
        
        // Count total
        $total = count($users);
        
        // Calculate total pages
        $totalPages = ceil($total / $perPage);
        
        // Apply pagination manually
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $paginatedUsers = array_slice($users, $offset, $perPage);
        
        return [
            'users' => $paginatedUsers,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage,
                'totalItems' => $total
            ]
        ];
    }
    
    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return array|false User data or false if not found
     */
    public function getUserById($id) {
        // Validate input
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        return $this->userModel->getById($id);
    }
    
    /**
     * Create a new user
     * 
     * @param array $data User data
     * @return int|false User ID or false on failure
     */
    public function createUser($data) {
        // Validate required fields
        if (empty($data['name']) || empty($data['email']) || empty($data['password']) || empty($data['role'])) {
            return false;
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Validate role
        $validRoles = [ROLE_ADMIN, ROLE_MANAGER, ROLE_CLIENT];
        if (!in_array($data['role'], $validRoles)) {
            return false;
        }
        
        // Create the user
        return $this->userModel->create(
            $data['name'],
            $data['email'],
            $data['password'],
            $data['role']
        );
    }
    
    /**
     * Update user information
     * 
     * @param array $data User data
     * @return bool Success or failure
     */
    public function updateUser($data) {
        // Validate required fields
        if (!isset($data['id']) || empty($data['name']) || empty($data['email']) || empty($data['role'])) {
            return false;
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Validate role
        $validRoles = [ROLE_ADMIN, ROLE_MANAGER, ROLE_CLIENT];
        if (!in_array($data['role'], $validRoles)) {
            return false;
        }
        
        // Check if user exists
        $user = $this->userModel->getById($data['id']);
        if (!$user) {
            return false;
        }
        
        // Prepare update data
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role']
        ];
        
        // Update user
        $result = $this->userModel->update($data['id'], $updateData);
        
        // Update password if provided
        if ($result && !empty($data['password'])) {
            $result = $this->userModel->updatePassword($data['id'], $data['password']);
        }
        
        return $result;
    }
    
    /**
     * Delete a user
     * 
     * @param int $id User ID
     * @return bool Success or failure
     */
    public function deleteUser($id) {
        // Validate input
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        return $this->userModel->delete($id);
    }
    
    /**
     * Search users by name or email
     * 
     * @param string $keyword Search keyword
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Matching users and pagination info
     */
    public function searchUsers($keyword, $page = 1, $perPage = 10) {
        // This function is a placeholder and would need to be implemented in User model
        // For now, we'll just return all users filtered by the keyword
        
        $allUsers = $this->userModel->getAll(1000, 0); // Get all users
        
        // Filter users by keyword
        $matchingUsers = array_filter($allUsers, function($user) use ($keyword) {
            return (stripos($user['name'], $keyword) !== false || 
                    stripos($user['email'], $keyword) !== false);
        });
        
        // Count total matches
        $total = count($matchingUsers);
        
        // Calculate total pages
        $totalPages = ceil($total / $perPage);
        
        // Apply pagination manually
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $paginatedUsers = array_slice($matchingUsers, $offset, $perPage);
        
        return [
            'users' => $paginatedUsers,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage,
                'totalItems' => $total
            ],
            'keyword' => $keyword
        ];
    }
}
?>
