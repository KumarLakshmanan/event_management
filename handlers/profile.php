<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
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
        setFlashMessage('Invalid action specified', 'danger');
        header("Location: ../pages/profile.php");
        exit;
}

/**
 * Handle profile update
 */
function handleUpdateProfile() {
    // Validate input
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    
    // Validate required fields
    if (empty($name) || empty($email)) {
        respondWithError('All required fields must be filled out');
        return;
    }
    
    // Get user ID
    $userId = $_SESSION['user_id'];
    
    // Update user in database or mock data
    if (USE_DATABASE) {
        $db = Database::getInstance();
        
        // Check if email is already taken by another user
        $existingUser = $db->querySingle("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $userId]);
        if ($existingUser) {
            respondWithError('Email is already in use by another account');
            return;
        }
        
        // Update user
        $userData = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address
        ];
        
        $result = updateRecord('users', $userId, $userData);
        
        if ($result) {
            // Update session
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            // Add notification
            addNotification(
                'profile_updated',
                "You've updated your profile information.",
                $userId,
                "../pages/profile.php"
            );
            
            respondWithSuccess('Profile updated successfully');
        } else {
            respondWithError('Failed to update profile');
        }
    } else {
        // Fallback to mock data
        $users = getMockData('users.json');
        $updated = false;
        
        // Find user
        foreach ($users as $index => $user) {
            if ($user['id'] == $userId) {
                // Check if email is already taken by another user
                foreach ($users as $u) {
                    if ($u['id'] != $userId && $u['email'] === $email) {
                        respondWithError('Email is already in use by another account');
                        return;
                    }
                }
                
                // Update user
                $users[$index]['name'] = $name;
                $users[$index]['email'] = $email;
                $users[$index]['phone'] = $phone;
                $users[$index]['address'] = $address;
                
                // Update session
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            saveMockData('users.json', $users);
            
            // Add notification
            addNotification(
                'profile_updated',
                "You've updated your profile information.",
                $userId,
                "../pages/profile.php"
            );
            
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
    
    // Validate required fields
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        respondWithError('All fields are required');
        return;
    }
    
    // Validate password match
    if ($newPassword !== $confirmPassword) {
        respondWithError('New passwords do not match');
        return;
    }
    
    // Get user ID
    $userId = $_SESSION['user_id'];
    
    // Verify current password and update password in database or mock data
    if (USE_DATABASE) {
        $db = Database::getInstance();
        
        // Get current user data
        $user = $db->querySingle("SELECT * FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            respondWithError('User not found');
            return;
        }
        
        // Verify current password
        if (!verifyPassword($currentPassword, $user['password_hash'])) {
            respondWithError('Current password is incorrect');
            return;
        }
        
        // Hash new password
        $newPasswordHash = hashPassword($newPassword);
        
        // Update password
        $result = $db->execute("UPDATE users SET password_hash = ? WHERE id = ?", [$newPasswordHash, $userId]);
        
        if ($result) {
            // Add notification
            addNotification(
                'password_changed',
                "You've successfully changed your password.",
                $userId,
                "../pages/profile.php"
            );
            
            respondWithSuccess('Password changed successfully');
        } else {
            respondWithError('Failed to change password');
        }
    } else {
        // Fallback to mock data
        $users = getMockData('users.json');
        $updated = false;
        
        // Find user
        foreach ($users as $index => $user) {
            if ($user['id'] == $userId) {
                // Verify current password
                if (!verifyPassword($currentPassword, $user['password'])) {
                    respondWithError('Current password is incorrect');
                    return;
                }
                
                // Hash new password
                $newPasswordHash = hashPassword($newPassword);
                
                // Update password
                $users[$index]['password'] = $newPasswordHash;
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            saveMockData('users.json', $users);
            
            // Add notification
            addNotification(
                'password_changed',
                "You've successfully changed your password.",
                $userId,
                "../pages/profile.php"
            );
            
            respondWithSuccess('Password changed successfully');
        } else {
            respondWithError('User not found');
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