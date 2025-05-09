<?php
session_start();
require_once '../config/config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Login</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        body {
            
            background-size: cover;
        }
        
        .login-card {
            max-width: 400px;
            margin: 100px auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Alert Container -->
        <div id="alertContainer"></div>
        
        <!-- Flash Message -->
        <?php if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])): ?>
            <div id="flashMessage" class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['flash_message']; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
        <?php endif; ?>
        
        <div class="card login-card shadow">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h1 class="h4 text-gray-900 mb-2">Welcome Back!</h1>
                    <p class="text-muted">Enter your credentials to login</p>
                </div>
                
                <form id="loginForm" class="api-form" method="POST" action="../handlers/auth.php" data-redirect="dashboard.php">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div id="emailFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div id="passwordFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox small">
                            <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                            <label class="custom-control-label" for="remember">Remember Me</label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        Login
                    </button>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <a class="small" href="register.php">Create an Account!</a>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/validations.js"></script>
</body>
</html>
