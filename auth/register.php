<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/User.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/dashboard/index.php');
    exit;
}

// Initialize variables
$name = '';
$email = '';
$errors = [];

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $name = sanitizeInput($_POST['name']);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate input
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters long';
    }
    
    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    // If no validation errors, create account
    if (empty($errors)) {
        $userModel = new User();
        $userId = $userModel->create($name, $email, $password, ROLE_CLIENT);
        
        if ($userId) {
            // Registration successful, set flash message
            setFlashMessage('Account created successfully! You can now log in.', 'success');
            
            // Redirect to login page
            header('Location: ' . APP_URL . '/auth/login.php');
            exit;
        } else {
            // Registration failed, likely due to email already in use
            $user = $userModel->getByEmail($email);
            if ($user) {
                $errors['email'] = 'Email is already in use';
            } else {
                $errors['register'] = 'Registration failed. Please try again.';
            }
        }
    }
}

// Set page title for header
$title = 'Register';
$showSidebar = false;

// Include header template
include_once __DIR__ . '/../templates/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="auth-container">
                <h2 class="title">Create an Account</h2>
                
                <?php if (!empty($errors['register'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['register']; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                               id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                               id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                               id="password" name="password" required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                        <?php endif; ?>
                        <div class="form-text">Password must be at least 6 characters long.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                               id="confirm_password" name="confirm_password" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">I agree to the Terms of Service and Privacy Policy</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Create Account</button>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p>Already have an account? <a href="<?php echo APP_URL; ?>/auth/login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../templates/footer.php'; ?>
