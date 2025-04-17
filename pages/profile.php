<?php
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user data
$userId = $_SESSION['user_id'];

if (USE_DATABASE) {
    $db = Database::getInstance();
    $user = $db->querySingle("SELECT * FROM users WHERE id = ?", [$userId]);
} else {
    // Fallback to mock data
    $users = getMockData('users.json');
    $user = null;
    
    foreach ($users as $u) {
        if ($u['id'] == $userId) {
            $user = $u;
            break;
        }
    }
}

// If user not found, redirect to login
if (!$user) {
    setFlashMessage('User not found', 'danger');
    header("Location: login.php");
    exit;
}
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">My Profile</h1>
</div>

<div class="row">
    <!-- Profile Information -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Profile Information</h6>
            </div>
            <div class="card-body">
                <form id="profileForm" class="api-form" action="../handlers/profile.php" method="POST" data-redirect="profile.php">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                        <div id="nameFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                        <div id="emailFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone'] ?? ''; ?>">
                        <div id="phoneFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo $user['address'] ?? ''; ?></textarea>
                        <div id="addressFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" class="form-control-plaintext" value="<?php echo ucfirst($user['role']); ?>" readonly>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Change Password -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Change Password</h6>
            </div>
            <div class="card-body">
                <form id="passwordForm" class="api-form" action="../handlers/profile.php" method="POST" data-redirect="profile.php">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="currentPassword">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                        <div id="currentPasswordFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required>
                        <div id="newPasswordFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                        <div id="confirmPasswordFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Change Password</button>
                </form>
            </div>
        </div>
        
        <!-- Account Information -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Account Created:</strong>
                    <p><?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                </div>
                
                <?php if ($user['role'] === 'manager'): ?>
                <div class="mb-3">
                    <strong>Discount Permission:</strong>
                    <?php if ($user['can_give_discount']): ?>
                    <p><span class="badge badge-success">Enabled</span></p>
                    <?php else: ?>
                    <p><span class="badge badge-danger">Disabled</span></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>