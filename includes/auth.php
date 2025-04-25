<?php
/**
 * Authentication functions for Event Planning Platform
 */

// Require config file
require_once INCLUDES_PATH . 'config.php';

/**
 * Register a new user
 * 
 * @param string $name User's full name
 * @param string $email User's email address
 * @param string $password User's password
 * @param string $phone User's phone number (optional)
 * @param string $address User's address (optional)
 * @return bool|string True on success, error message on failure
 */
function registerUser($name, $email, $password, $phone = null, $address = null) {
    try {
        $db = getDBConnection();
        
        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM members WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            return "Email address already registered";
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $db->prepare("INSERT INTO members (name, email, password, phone, address) 
                             VALUES (:name, :email, :password, :phone, :address)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->execute();
        
        return true;
    } catch (PDOException $e) {
        return "Registration failed: " . $e->getMessage();
    }
}

/**
 * Authenticate user login
 * 
 * @param string $email User's email
 * @param string $password User's password
 * @return bool|string True on success, error message on failure
 */
function loginUser($email, $password) {
    try {
        $db = getDBConnection();
        
        // Get user by email
        $stmt = $db->prepare("SELECT * FROM members WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return "Invalid email or password";
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            return "Invalid email or password";
        }
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['can_give_discount'] = $user['can_give_discount'];
        
        return true;
    } catch (PDOException $e) {
        return "Login failed: " . $e->getMessage();
    }
}

/**
 * Log out the current user
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = [];
    
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user data
 * 
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM members WHERE id = :id");
        $stmt->bindParam(':id', $_SESSION['user_id']);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Check if current user has a specific role
 * 
 * @param string|array $roles Role or array of roles to check
 * @return bool True if user has one of the roles, false otherwise
 */
function hasRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (is_string($roles)) {
        $roles = [$roles];
    }
    
    return in_array($_SESSION['user_role'], $roles);
}

/**
 * Check if current user can give discount
 * 
 * @return bool True if user can give discount, false otherwise
 */
function canGiveDiscount() {
    if (!isLoggedIn()) {
        return false;
    }
    
    return $_SESSION['can_give_discount'] == 1;
}

/**
 * Require user to be logged in
 * Redirects to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /auth/login.php");
        exit;
    }
}

/**
 * Require user to have one of the specified roles
 * Redirects to dashboard if user doesn't have required role
 * 
 * @param string|array $roles Role or array of roles required
 */
function requireRole($roles) {
    requireLogin();
    
    if (!hasRole($roles)) {
        header("Location: /dashboard/index.php");
        exit;
    }
}
?>