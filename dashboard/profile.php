<?php
/**
 * User profile page for Event Planning Platform
 */

// Include configuration
require_once dirname(__DIR__) . '/includes/config.php';
require_once INCLUDES_PATH . 'auth.php';
require_once INCLUDES_PATH . 'functions.php';

// Require user to be logged in
requireLogin();

// Initialize variables
$success = false;
$error = '';
$user = getCurrentUser();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = sanitizeInput($_POST['name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate data
    if (empty($name)) {
        $error = "Name cannot be empty";
    } else {
        try {
            $db = getDBConnection();
            
            // Check if changing password
            if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
                // Validate passwords
                if (empty($currentPassword)) {
                    $error = "Current password is required to change password";
                } elseif (empty($newPassword)) {
                    $error = "New password cannot be empty";
                } elseif (strlen($newPassword) < 6) {
                    $error = "New password must be at least 6 characters long";
                } elseif ($newPassword != $confirmPassword) {
                    $error = "New passwords do not match";
                } elseif (!password_verify($currentPassword, $user['password'])) {
                    $error = "Current password is incorrect";
                } else {
                    // Hash new password
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    
                    // Update user with new password
                    $stmt = $db->prepare("UPDATE members SET name = :name, phone = :phone, address = :address, password = :password WHERE id = :id");
                    $stmt->bindParam(':password', $hashedPassword);
                }
            } else {
                // Update user without changing password
                $stmt = $db->prepare("UPDATE members SET name = :name, phone = :phone, address = :address WHERE id = :id");
            }
            
            // Execute update if no error
            if (empty($error)) {
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':id', $_SESSION['user_id']);
                $stmt->execute();
                
                // Update session data
                $_SESSION['user_name'] = $name;
                
                // Refresh user data
                $user = getCurrentUser();
                
                // Set success message
                $success = true;
            }
        } catch (PDOException $e) {
            $error = "Error updating profile: " . $e->getMessage();
        }
    }
}

// Page title
$pageTitle = 'My Profile';

// Include header
include_once TEMPLATES_PATH . 'header.php';
?>

<div class="container">
    <h1 class="mb-4">My Profile</h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success" role="alert">
            <i class="bi bi-check-circle me-2"></i>Your profile has been updated successfully.
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Profile Information</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= $_SERVER['PHP_SELF'] ?>" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name*</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($user['name']) ?>" required>
                            <div class="invalid-feedback">
                                Please enter your full name.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" 
                                   value="<?= htmlspecialchars($user['email']) ?>" readonly>
                            <div class="form-text">Email address cannot be changed.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <input type="text" class="form-control" id="role" 
                                   value="<?= ucfirst($user['role']) ?>" readonly>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5 class="mb-3">Change Password</h5>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#current_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Enter your current password to change it.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" minlength="6">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#new_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Password must be at least 6 characters long.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#confirm_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Account Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Member since:</strong> <?= formatDate($user['created_at'], 'M d, Y') ?></p>
                    
                    <?php if ($user['role'] == 'client'): ?>
                        <?php 
                        // Get booking stats for client
                        $db = getDBConnection();
                        $stmt = $db->prepare("SELECT COUNT(*) as count FROM reservations WHERE user_id = :user_id");
                        $stmt->bindParam(':user_id', $user['id']);
                        $stmt->execute();
                        $bookingsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                        ?>
                        <p><strong>Total bookings:</strong> <?= $bookingsCount ?></p>
                        <div class="d-grid gap-2 mt-3">
                            <a href="<?= BASE_URL ?>dashboard/bookings.php" class="btn btn-outline-primary">View My Bookings</a>
                        </div>
                    <?php elseif ($user['role'] == 'manager' || $user['role'] == 'administrator'): ?>
                        <p><strong>Discount permission:</strong> <?= $user['can_give_discount'] ? 'Yes' : 'No' ?></p>
                        <?php if ($user['role'] == 'manager'): ?>
                            <p class="text-muted">As a manager, you can create packages and confirm bookings.</p>
                        <?php else: ?>
                            <p class="text-muted">As an administrator, you have access to all system features.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Security</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Keep your account secure by:</p>
                    <ul>
                        <li>Using a strong, unique password</li>
                        <li>Updating your contact information</li>
                        <li>Never sharing your login credentials</li>
                    </ul>
                    <div class="d-grid gap-2 mt-3">
                        <a href="<?= BASE_URL ?>auth/logout.php" class="btn btn-outline-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once TEMPLATES_PATH . 'footer.php';
?>