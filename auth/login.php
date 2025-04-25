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
$email = '';
$errors = [];
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '/dashboard/index.php';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }
    
    // If no validation errors, attempt to authenticate
    if (empty($errors)) {
        $userModel = new User();
        $user = $userModel->authenticate($email, $password);
        
        if ($user) {
            // Authentication successful, set session and redirect
            $_SESSION['user'] = $user;
            
            // Set a flash message for successful login
            setFlashMessage('Welcome back, ' . $user['name'] . '!', 'success');
            
            // Redirect to requested page or dashboard
            header('Location: ' . APP_URL . $redirect);
            exit;
        } else {
            // Authentication failed
            $errors['login'] = 'Invalid email or password';
        }
    }
}

// Set page title for header
$title = 'Login';
$showSidebar = false;

// Include header template
include_once __DIR__ . '/../templates/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="auth-container">
                <h2 class="title">Login to Your Account</h2>
                
                <?php if (!empty($errors['login'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['login']; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
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
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                    
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p>Don't have an account? <a href="<?php echo APP_URL; ?>/auth/register.php">Register here</a></p>
                </div>
            </div>
            
            <!-- Demo Credentials (for development only) -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Demo Accounts</h5>
                    <p class="card-text">Use these credentials to test different roles:</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Admin:</strong> admin@example.com / admin123</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../templates/footer.php'; ?>
