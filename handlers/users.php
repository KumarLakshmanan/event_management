<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit;
}

// Get the requested action
$action = sanitizeInput($_POST['action'] ?? $_GET['action'] ?? '');

switch ($action) {
    case 'create':
        handleCreateUser();
        break;
    case 'update':
        handleUpdateUser();
        break;
    case 'delete':
        handleDeleteUser();
        break;
    default:
        setFlashMessage('Invalid action specified', 'danger');
        header("Location: ../pages/user-management.php");
        exit;
}

/**
 * Handle user creation
 */
function handleCreateUser()
{
    // Validate input
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $role = sanitizeInput($_POST['role'] ?? 'client');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $canGiveDiscount = isset($_POST['can_give_discount']) ? true : false;

    // Validate required fields
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        respondWithError('All required fields must be filled out');
        return;
    }

    // Validate password match
    if ($password !== $confirmPassword) {
        respondWithError('Passwords do not match');
        return;
    }

    // Validate role
    if (!in_array($role, ['client', 'manager', 'admin'])) {
        respondWithError('Invalid role');
        return;
    }

    // Hash password
    $hashedPassword = hashPassword($password);

    // Create user in database

    $db = Database::getInstance();

    // Check if email is already taken
    $existingUser = $db->querySingle("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existingUser) {
        respondWithError('Email is already in use');
        return;
    }

    // Insert user
    $userData = [
        'name' => $name,
        'email' => $email,
        'password' => $hashedPassword,
        'phone' => $phone,
        'address' => $address,
        'role' => $role,
        'can_give_discount' => $canGiveDiscount ? 1 : 0,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $userId = insertRecord('users', $userData);

    if ($userId) {
        // Add notification
        addNotification(
            'register',
            "New user {$name} ({$role}) has been created by admin.",
            null, // System notification
            "../pages/user-management.php?edit={$userId}"
        );

        respondWithSuccess('User created successfully', ['id' => $userId]);
    } else {
        respondWithError('Failed to create user');
    }
}

/**
 * Handle user update
 */
function handleUpdateUser()
{
    // Validate input
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $role = sanitizeInput($_POST['role'] ?? 'client');
    $canGiveDiscount = isset($_POST['can_give_discount']) ? true : false;

    // Validate required fields
    if (!$id || empty($name) || empty($email)) {
        respondWithError('All required fields must be filled out');
        return;
    }

    // Validate role
    if (!in_array($role, ['client', 'manager', 'admin'])) {
        respondWithError('Invalid role');
        return;
    }

    // Update user in database

    $db = Database::getInstance();

    // Get current user data
    $user = $db->querySingle("SELECT * FROM users WHERE id = ?", [$id]);
    if (!$user) {
        respondWithError('User not found');
        return;
    }

    // Check if email is already taken by another user
    $existingUser = $db->querySingle("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $id]);
    if ($existingUser) {
        respondWithError('Email is already in use by another account');
        return;
    }

    // Self-edit restrictions: cannot change own role
    if ($id == $_SESSION['user_id']) {
        $role = $user['role']; // Keep original role
    }

    // Update user
    $userData = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'role' => $role,
        'can_give_discount' => $canGiveDiscount ? 1 : 0
    ];

    $result = updateRecord('users', $id, $userData);

    if ($result) {
        // Update session if updating own account
        if ($id == $_SESSION['user_id']) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
        }

        respondWithSuccess('User updated successfully');
    } else {
        respondWithError('Failed to update user');
    }
}

/**
 * Handle user deletion
 */
function handleDeleteUser()
{
    // Validate input
    $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

    // Cannot delete yourself
    if ($id == $_SESSION['user_id']) {
        setFlashMessage('You cannot delete your own account', 'danger');
        header("Location: ../pages/user-management.php");
        exit;
    }

    // Delete user from database

    $db = Database::getInstance();

    // Get user data before deleting
    $user = $db->querySingle("SELECT name, role FROM users WHERE id = ?", [$id]);
    if (!$user) {
        setFlashMessage('User not found', 'danger');
        header("Location: ../pages/user-management.php");
        exit;
    }

    // Delete user
    $result = $db->execute("DELETE FROM users WHERE id = ?", [$id]);

    if ($result) {
        // Add notification
        addNotification(
            'user_deleted',
            "User {$user['name']} ({$user['role']}) has been deleted by admin.",
            null // System notification
        );

        setFlashMessage('User deleted successfully', 'success');
    } else {
        setFlashMessage('Failed to delete user', 'danger');
    }


    header("Location: ../pages/user-management.php");
    exit;
}

/**
 * Respond with success JSON
 */
function respondWithSuccess($message, $data = [])
{
    $response = [
        'success' => true,
        'message' => $message
    ];

    if (!empty($data)) {
        $response['data'] = $data;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

/**
 * Respond with error JSON
 */
function respondWithError($message, $code = 400)
{
    http_response_code($code);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}
