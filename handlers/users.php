<?php
session_start();
require_once '../config/config.php';
require_once 'api.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check action parameter
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'create':
        handleCreate();
        break;
        
    case 'update':
        handleUpdate();
        break;
        
    case 'delete':
        handleDelete();
        break;
        
    default:
        // Invalid action
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid action']);
        exit;
}

/**
 * Handle user creation
 */
function handleCreate() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }
    
    // Get input data
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $address = sanitizeInput($_POST['address'] ?? '');
    $role = sanitizeInput($_POST['role'] ?? '');
    $canGiveDiscount = isset($_POST['can_give_discount']) ? true : false;
    
    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Name, email, password, and role are required']);
        exit;
    }
    
    if ($password !== $confirmPassword) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Passwords do not match']);
        exit;
    }
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid email format']);
        exit;
    }
    
    // Role validation
    $validRoles = ['client', 'manager', 'admin'];
    if (!in_array($role, $validRoles)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid role']);
        exit;
    }
    
    // Get users data
    $users = getMockData('users.json');
    
    // Check if email already exists
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Email already in use']);
            exit;
        }
    }
    
    // Generate user ID
    $id = count($users) > 0 ? max(array_column($users, 'id')) + 1 : 1;
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Can give discount only applies to managers and admins
    if ($role === 'client') {
        $canGiveDiscount = false;
    }
    
    // Create new user
    $newUser = [
        'id' => $id,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'password_hash' => $passwordHash,
        'address' => $address,
        'role' => $role,
        'can_give_discount' => $canGiveDiscount,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Add user to data
    $users[] = $newUser;
    
    // Save data
    saveMockData('users.json', $users);
    
    // Send API request to external API
    $apiUser = $newUser;
    unset($apiUser['password_hash']); // Don't send password hash to API
    apiPost('users', $apiUser);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'user' => $apiUser]);
    exit;
}

/**
 * Handle user update
 */
function handleUpdate() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }
    
    // Get input data
    $id = intval($_POST['id'] ?? 0);
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $role = sanitizeInput($_POST['role'] ?? '');
    $canGiveDiscount = isset($_POST['can_give_discount']) ? true : false;
    
    // Validate input
    if ($id <= 0 || empty($name) || empty($email) || empty($role)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID, name, email, and role are required']);
        exit;
    }
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid email format']);
        exit;
    }
    
    // Role validation
    $validRoles = ['client', 'manager', 'admin'];
    if (!in_array($role, $validRoles)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid role']);
        exit;
    }
    
    // Get users data
    $users = getMockData('users.json');
    
    // Find user to update
    $userIndex = -1;
    foreach ($users as $index => $user) {
        if ($user['id'] === $id) {
            $userIndex = $index;
            break;
        }
    }
    
    if ($userIndex === -1) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'User not found']);
        exit;
    }
    
    // Check if email already exists (except for this user)
    foreach ($users as $user) {
        if ($user['id'] !== $id && $user['email'] === $email) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Email already in use by another user']);
            exit;
        }
    }
    
    // Don't allow changing own role
    if ($id === intval($_SESSION['user_id']) && $role !== $users[$userIndex]['role']) {
        $role = $users[$userIndex]['role']; // Keep existing role
    }
    
    // Can give discount only applies to managers and admins
    if ($role === 'client') {
        $canGiveDiscount = false;
    }
    
    // Update user
    $users[$userIndex]['name'] = $name;
    $users[$userIndex]['email'] = $email;
    $users[$userIndex]['phone'] = $phone;
    $users[$userIndex]['address'] = $address;
    $users[$userIndex]['role'] = $role;
    $users[$userIndex]['can_give_discount'] = $canGiveDiscount;
    
    // Save data
    saveMockData('users.json', $users);
    
    // Send API request to external API
    $apiUser = $users[$userIndex];
    unset($apiUser['password_hash']); // Don't send password hash to API
    apiPut('users/' . $id, $apiUser);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'user' => $apiUser]);
    exit;
}

/**
 * Handle user deletion
 */
function handleDelete() {
    // Get user ID
    $id = intval($_REQUEST['id'] ?? 0);
    
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid user ID']);
        exit;
    }
    
    // Don't allow deleting own account
    if ($id === intval($_SESSION['user_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'You cannot delete your own account']);
        exit;
    }
    
    // Get users data
    $users = getMockData('users.json');
    
    // Find user to delete
    $userIndex = -1;
    foreach ($users as $index => $user) {
        if ($user['id'] === $id) {
            $userIndex = $index;
            break;
        }
    }
    
    if ($userIndex === -1) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'User not found']);
        exit;
    }
    
    // Remove user from data
    array_splice($users, $userIndex, 1);
    
    // Save data
    saveMockData('users.json', $users);
    
    // Send API request to external API
    apiDelete('users/' . $id);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}
?>
