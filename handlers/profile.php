<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    respondWithError('Unauthorized', 401);
    exit;
}

// Get the requested action
$action = sanitizeInput($_POST['action'] ?? '');

switch ($action) {
    case 'update_profile':
        handleUpdateProfile();
        break;
    case 'change_password':
        handleChangePassword();
        break;
    default:
        respondWithError('Invalid action specified');
        break;
}

/**
 * Handle profile update
 */
function handleUpdateProfile() {
    // Validate input
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    
    if (empty($name) || empty($email)) {
        respondWithError('Name and email are required');
        return;
    }
    
    // Ensure user can only update their own profile
    $userId = $_SESSION['user_id'];
    
    // Update profile in database or mock data
    if (USE_DATABASE) {
        $db = Database::getInstance();
        
        // Check if email is already taken by another user
        $existingUser = $db->querySingle(
            "SELECT id FROM users WHERE email = ? AND id != ?", 
            [$email, $userId]
        );
        
        if ($existingUser) {
            respondWithError('Email is already in use by another account');
            return;
        }
        
        // Update user
        $userData = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone
        ];
        
        $result = updateRecord('users', $userId, $userData);
        
        if ($result) {
            // Update session
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            respondWithSuccess('Profile updated successfully');
        } else {
            respondWithError('Failed to update profile');
        }
    } else {
        // Fallback to mock data
        $users = getMockData('users.json');
        $updated = false;
        
        // Check if email is already taken by another user
        foreach ($users as $user) {
            if ($user['email'] === $email && $user['id'] != $userId) {
                respondWithError('Email is already in use by another account');
                return;
            }
        }
        
        // Update user
        foreach ($users as $index => $user) {
            if ($user['id'] == $userId) {
                $users[$index]['name'] = $name;
                $users[$index]['email'] = $email;
                $users[$index]['phone'] = $phone;
                
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            saveMockData('users.json', $users);
            
            // Update session
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            respondWithSuccess('Profile updated successfully');
        } else {
            respondWithError('User not found');
        }
    }
}

/**
 * Handle password change
 */
function handleChangePassword() {
    // Validate input
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        respondWithError('All fields are required');
        return;
    }
    
    if ($newPassword !== $confirmPassword) {
        respondWithError('New passwords do not match');
        return;
    }
    
    // Ensure user can only update their own password
    $userId = $_SESSION['user_id'];
    
    // Update password in database or mock data
    if (USE_DATABASE) {
        $db = Database::getInstance();
        
        // Get current user data
        $user = $db->querySingle("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if (!$user) {
            respondWithError('User not found');
            return;
        }
        
        // Verify current password
        if (!verifyPassword($currentPassword, $user['password'])) {
            respondWithError('Current password is incorrect');
            return;
        }
        
        // Update password
        $hashedPassword = hashPassword($newPassword);
        $result = $db->execute("UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $userId]);
        
        if ($result) {
            respondWithSuccess('Password updated successfully');
        } else {
            respondWithError('Failed to update password');
        }
    } else {
        // Fallback to mock data
        $users = getMockData('users.json');
        $updated = false;
        $user = null;
        
        // Find user
        foreach ($users as $index => $u) {
            if ($u['id'] == $userId) {
                $user = $u;
                
                // Verify current password
                if (!verifyPassword($currentPassword, $user['password'])) {
                    respondWithError('Current password is incorrect');
                    return;
                }
                
                // Update password
                $users[$index]['password'] = hashPassword($newPassword);
                $updated = true;
                break;
            }
        }
        
        if (!$user) {
            respondWithError('User not found');
            return;
        }
        
        if ($updated) {
            saveMockData('users.json', $users);
            respondWithSuccess('Password updated successfully');
        } else {
            respondWithError('Failed to update password');
        }
    }
}

/**
 * Respond with success JSON
 */
function respondWithSuccess($message, $data = []) {
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
function respondWithError($message, $code = 400) {
    http_response_code($code);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}
?>