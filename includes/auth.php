<?php
require_once 'config.php';

/**
 * Authenticate a user
 * 
 * @param string $email User's email
 * @param string $password User's password
 * @return array|false User data or false if authentication fails
 */
function authenticate($email, $password) {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        // Don't store password in session
        unset($user['password']);
        return $user;
    }
    
    return false;
}

/**
 * Register a new user
 * 
 * @param string $name User's name
 * @param string $email User's email
 * @param string $password User's password
 * @param string $role User's role (default: client)
 * @return int|false User ID or false if registration fails
 */
function registerUser($name, $email, $password, $role = ROLE_CLIENT) {
    $db = getDB();
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT count(*) FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->fetchColumn() > 0) {
        return false; // Email already exists
    }
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':role', $role);
    
    if ($stmt->execute()) {
        return $db->lastInsertId();
    }
    
    return false;
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user']);
}

/**
 * Get the current user data
 * 
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

/**
 * Check if current user has a specific role
 * 
 * @param string|array $roles Role or roles to check
 * @return bool True if user has the role, false otherwise
 */
function hasRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    return in_array($_SESSION['user']['role'], $roles);
}

/**
 * Check if user has permission to access a resource
 * 
 * @param string $permission Permission to check
 * @return bool True if user has permission, false otherwise
 */
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $role = $_SESSION['user']['role'];
    
    switch ($permission) {
        case 'view_dashboard':
            return true; // All logged in users can view their dashboard
        
        case 'manage_packages':
        case 'manage_services':
            return in_array($role, [ROLE_ADMIN, ROLE_MANAGER]);
        
        case 'manage_users':
        case 'assign_roles':
            return $role == ROLE_ADMIN;
        
        case 'book_event':
        case 'manage_guests':
            return in_array($role, [ROLE_CLIENT, ROLE_ADMIN, ROLE_MANAGER]);
        
        case 'apply_discount':
            // Check if the user has the discount permission (admin or manager with the can_apply_discount flag)
            if (!isset($_SESSION['user']['can_apply_discount'])) {
                return false;
            }
            
            // Admin or manager with explicit permission
            return in_array($role, [ROLE_ADMIN, ROLE_MANAGER]) && (int)$_SESSION['user']['can_apply_discount'] === 1;
        
        default:
            return false;
    }
}

/**
 * Redirect if user is not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * Redirect if user doesn't have required role
 * 
 * @param string|array $roles Required role(s)
 */
function requireRole($roles) {
    requireLogin();
    
    if (!hasRole($roles)) {
        header('Location: ' . APP_URL . '/dashboard/index.php?error=insufficient_permissions');
        exit;
    }
}

/**
 * Redirect if user doesn't have required permission
 * 
 * @param string $permission Required permission
 */
function requirePermission($permission) {
    requireLogin();
    
    if (!hasPermission($permission)) {
        header('Location: ' . APP_URL . '/dashboard/index.php?error=insufficient_permissions');
        exit;
    }
}

/**
 * Logout the current user
 */
function logout() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
}
?>
