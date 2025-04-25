<?php
/**
 * Login page for Event Planning Platform
 */

// Include configuration
require_once dirname(__DIR__) . '/includes/config.php';

// Check if user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    header("Location: " . getDashboardUrl());
    exit;
}

// Process login form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password";
    } elseif (!isValidEmail($email)) {
        $error = "Please enter a valid email address";
    } else {
        // Attempt login
        $result = loginUser($email, $password);
        
        if ($result === true) {
            // Successful login, redirect to dashboard
            header("Location: " . getDashboardUrl());
            exit;
        } else {
            // Login failed, show error
            $error = $result;
        }
    }
}

// Page title
$pageTitle = 'Login';

// Include header
include_once TEMPLATES_PATH . 'header.php';
?>

<div class="container">
    <div class="auth-form">
        <h2 class="text-center mb-4">Login to Your Account</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="<?= $_SERVER['PHP_SELF'] ?>" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                <div class="invalid-feedback">
                    Please enter a valid email address.
                </div>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="invalid-feedback">
                    Please enter your password.
                </div>
            </div>
            
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
            
            <div class="text-center mt-3">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include_once TEMPLATES_PATH . 'footer.php';
?>