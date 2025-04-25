<?php
/**
 * Registration page for Event Planning Platform
 */

// Include configuration
require_once dirname(__DIR__) . '/includes/config.php';
require_once INCLUDES_PATH . 'auth.php';
require_once INCLUDES_PATH . 'functions.php';

// Check if user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    header("Location: " . getDashboardUrl());
    exit;
}

// Process registration form submission
$error = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "Please fill all required fields";
    } elseif (!isValidEmail($email)) {
        $error = "Please enter a valid email address";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    } else {
        // Attempt registration
        $result = registerUser($name, $email, $password, $phone, $address);
        
        if ($result === true) {
            // Registration successful, show success message
            $success = true;
            // Clear form data
            $name = $email = $phone = $address = '';
        } else {
            // Registration failed, show error
            $error = $result;
        }
    }
}

// Page title
$pageTitle = 'Register';

// Include header
include_once TEMPLATES_PATH . 'header.php';
?>

<div class="container">
    <div class="auth-form">
        <h2 class="text-center mb-4">Create an Account</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                Registration successful! You can now <a href="login.php">login</a> to your account.
            </div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
            <form method="post" action="<?= $_SERVER['PHP_SELF'] ?>" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name*</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required>
                    <div class="invalid-feedback">
                        Please enter your full name.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email address*</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                    <div class="invalid-feedback">
                        Please enter a valid email address.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="<?= isset($phone) ? htmlspecialchars($phone) : '' ?>">
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="2"><?= isset($address) ? htmlspecialchars($address) : '' ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password*</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="form-text">Password must be at least 6 characters long.</div>
                    <div class="invalid-feedback">
                        Please enter a password with at least 6 characters.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirm Password*</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#confirmPassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback">
                        Please confirm your password.
                    </div>
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
                
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include_once TEMPLATES_PATH . 'footer.php';
?>