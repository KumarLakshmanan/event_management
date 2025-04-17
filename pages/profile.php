<?php
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user data
if (USE_DATABASE) {
    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];
    
    $user = $db->querySingle("SELECT * FROM users WHERE id = ?", [$userId]);
} else {
    // Fallback to mock data
    $users = getMockData('users.json');
    $user = null;
    
    foreach ($users as $u) {
        if ($u['id'] == $_SESSION['user_id']) {
            $user = $u;
            break;
        }
    }
}

// Set default values if user not found (shouldn't happen but just in case)
if (!$user) {
    $user = [
        'id' => $_SESSION['user_id'],
        'name' => '',
        'email' => '',
        'phone' => '',
        'role' => $_SESSION['user_role'],
    ];
}
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">My Profile</h1>
</div>

<div class="row">
    <!-- Profile Information -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Profile Information</h6>
            </div>
            <div class="card-body">
                <form id="profileForm" class="api-form" action="../handlers/profile.php" method="POST" data-redirect="profile.php">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                    
                    <div class="form-group row">
                        <label for="name" class="col-sm-3 col-form-label">Full Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                            <div id="nameFeedback" class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="email" class="col-sm-3 col-form-label">Email</label>
                        <div class="col-sm-9">
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                            <div id="emailFeedback" class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="phone" class="col-sm-3 col-form-label">Phone</label>
                        <div class="col-sm-9">
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone'] ?? ''; ?>">
                            <div id="phoneFeedback" class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="role" class="col-sm-3 col-form-label">Role</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control-plaintext" id="role" value="<?php echo ucfirst($user['role']); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col-sm-9 offset-sm-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Change Password -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Change Password</h6>
            </div>
            <div class="card-body">
                <form id="passwordForm" class="api-form" action="../handlers/profile.php" method="POST" data-redirect="profile.php">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                        <div id="currentPasswordFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <div id="newPasswordFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <div id="confirmPasswordFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Add custom validation for password form
    $('#passwordForm').on('submit', function(e) {
        // Reset feedback
        $('.invalid-feedback').hide();
        
        // Validate password match
        if ($('#new_password').val() !== $('#confirm_password').val()) {
            $('#confirmPasswordFeedback').text('Passwords do not match').show();
            $('#confirm_password').addClass('is-invalid');
            e.preventDefault();
            return false;
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>