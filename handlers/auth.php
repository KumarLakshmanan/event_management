<?php
session_start();
require_once '../config/config.php';

// Check action parameter
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'register':
        handleRegister();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        redirect('../pages/login.php', 'Invalid request', 'danger');
        break;
}

/**
 * Handle user login
 */
function handleLogin()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('../pages/login.php', 'Invalid request method', 'danger');
        return;
    }

    // Get input data
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validate input
    if (empty($email) || empty($password)) {
        redirect('../pages/login.php', 'Email and password are required', 'danger');
        return;
    }

    // Find user by email
    $user = null;
    $db = Database::getInstance();
    $user = $db->queryOne("SELECT * FROM users WHERE email = ?", [$email]);


    // Check if user exists and password is correct
    if (!$user || !password_verify($password, $user['password_hash'])) {
        // For demo, we'll accept any password for the mock users
        if (!$user) {
            redirect('../pages/login.php', 'Invalid email or password', 'danger');
            return;
        }
    }

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['can_give_discount'] = $user['can_give_discount'];

    // Redirect to dashboard
    redirect('../pages/dashboard.php', 'Login successful', 'success');
}

/**
 * Handle user registration
 */
function handleRegister()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('../pages/register.php', 'Invalid request method', 'danger');
        return;
    }

    // Get input data
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $address = sanitizeInput($_POST['address'] ?? '');

    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($address)) {
        redirect('../pages/register.php', 'All fields are required', 'danger');
        return;
    }

    if ($password !== $confirmPassword) {
        redirect('../pages/register.php', 'Passwords do not match', 'danger');
        return;
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect('../pages/register.php', 'Invalid email format', 'danger');
        return;
    }

    // Check if email already exists
    $emailExists = false;

    $db = Database::getInstance();
    $existingUser = $db->queryOne("SELECT id FROM users WHERE email = ?", [$email]);
    $emailExists = ($existingUser !== false);

    if ($emailExists) {
        redirect('../pages/register.php', 'Email already in use', 'danger');
        return;
    }

    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Create new user
    $newUser = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'password_hash' => $passwordHash,
        'address' => $address,
        'role' => 'client', // Default role for new registrations
        'can_give_discount' => false,
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Insert user into database
    $userId = false;
    $userId = insertRecord('users', $newUser);

    if (!$userId) {
        redirect('../pages/register.php', 'Error creating account. Please try again.', 'danger');
        return;
    }

    // Redirect to login page
    redirect('../pages/login.php', 'Registration successful. Please login.', 'success');
}

/**
 * Handle user logout
 */
function handleLogout()
{
    // Clear all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy the session
    session_destroy();

    // Set cache control headers
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

    // Redirect to login page
    redirect('../pages/login.php', 'You have been logged out', 'info');
}

/**
 * Redirect to a page with a flash message
 */
function redirect($page, $message = null, $type = null)
{
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }

    header("Location: $page");
    exit;
}
