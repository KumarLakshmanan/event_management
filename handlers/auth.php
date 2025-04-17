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
        // Invalid action
        redirect('../pages/login.php', 'Invalid request', 'danger');
        break;
}

/**
 * Handle user login
 */
function handleLogin() {
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
    
    // Get users data
    $users = getMockData('users.json');
    
    // Find user by email
    $user = null;
    foreach ($users as $u) {
        if ($u['email'] === $email) {
            $user = $u;
            break;
        }
    }
    
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
function handleRegister() {
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
    
    // Get users data
    $users = getMockData('users.json');
    
    // Check if email already exists
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            redirect('../pages/register.php', 'Email already in use', 'danger');
            return;
        }
    }
    
    // Generate user ID
    $id = count($users) > 0 ? max(array_column($users, 'id')) + 1 : 1;
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Create new user
    $newUser = [
        'id' => $id,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'password_hash' => $passwordHash,
        'address' => $address,
        'role' => 'client', // Default role for new registrations
        'can_give_discount' => false,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Add user to data
    $users[] = $newUser;
    
    // Save data
    saveMockData('users.json', $users);
    
    // Send API request to external API
    sendApiRequest('register', $newUser);
    
    // Redirect to login page
    redirect('../pages/login.php', 'Registration successful. Please login.', 'success');
}

/**
 * Handle user logout
 */
function handleLogout() {
    // Destroy session
    session_unset();
    session_destroy();
    
    // Redirect to login page
    redirect('../pages/login.php', 'You have been logged out', 'info');
}

/**
 * Redirect to a page with a flash message
 */
function redirect($page, $message = null, $type = null) {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    
    header("Location: $page");
    exit;
}

/**
 * Send data to external API
 */
function sendApiRequest($endpoint, $data) {
    // For demo, we're just simulating API requests
    // In a real application, you would make actual API calls
    
    // You could use something like this:
    /*
    $apiUrl = API_URL . '/' . $endpoint;
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json'
    ));
    
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    return [
        'status' => $statusCode,
        'response' => json_decode($response, true)
    ];
    */
    
    // For demo, just return success
    return [
        'status' => 200,
        'response' => ['success' => true]
    ];
}
?>
