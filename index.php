<?php
/**
 * Home page for Event Planning Platform
 */

// Page title
$pageTitle = 'Home';

// Include header
require_once 'includes/config.php';
include_once TEMPLATES_PATH . 'header.php';
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <h1 class="display-4 mb-4">Welcome to <?= APP_NAME ?></h1>
        <p class="lead mb-5">Create unforgettable events with our comprehensive planning platform</p>
        
        <?php if (!isLoggedIn()): ?>
            <div class="mt-4">
                <a href="/auth/register.php" class="btn btn-primary btn-lg me-3">Get Started</a>
                <a href="/auth/login.php" class="btn btn-outline-secondary btn-lg">Sign In</a>
            </div>
        <?php else: ?>
            <a href="<?= getDashboardUrl() ?>" class="btn btn-primary btn-lg">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</div>

<!-- Features Section -->
<div class="container mb-5">
    <div class="text-center mb-5">
        <h2>Why Choose Our Platform?</h2>
        <p class="lead">We provide all the tools you need for successful event planning</p>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="bi bi-box"></i>
                </div>
                <h3>Comprehensive Packages</h3>
                <p>Choose from a variety of curated event packages or customize your own to meet specific needs.</p>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <h3>Seamless Booking</h3>
                <p>Our intuitive booking system makes it easy to schedule and manage events of any size.</p>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="bi bi-people"></i>
                </div>
                <h3>Guest Management</h3>
                <p>Keep track of attendees, send invitations, and monitor RSVPs all in one place.</p>
            </div>
        </div>
    </div>
</div>

<!-- How It Works Section -->
<div class="bg-light py-5 mb-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2>How It Works</h2>
            <p class="lead">Easy steps to plan your perfect event</p>
        </div>
        
        <div class="row">
            <div class="col-md-3">
                <div class="text-center">
                    <div class="fs-1 mb-3 text-primary">1</div>
                    <h4>Register Account</h4>
                    <p>Create your account to access all features</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="text-center">
                    <div class="fs-1 mb-3 text-primary">2</div>
                    <h4>Choose Package</h4>
                    <p>Select from our curated packages or customize your own</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="text-center">
                    <div class="fs-1 mb-3 text-primary">3</div>
                    <h4>Book Event</h4>
                    <p>Schedule your event and provide details</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="text-center">
                    <div class="fs-1 mb-3 text-primary">4</div>
                    <h4>Manage Guests</h4>
                    <p>Add guests and track RSVPs</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="container text-center mb-5">
    <h2>Ready to Plan Your Next Event?</h2>
    <p class="lead mb-4">Join our platform today and start creating memorable experiences</p>
    
    <?php if (!isLoggedIn()): ?>
        <a href="/auth/register.php" class="btn btn-primary btn-lg">Get Started Now</a>
    <?php else: ?>
        <a href="<?= getDashboardUrl() ?>" class="btn btn-primary btn-lg">Go to Dashboard</a>
    <?php endif; ?>
</div>

<?php
// Include footer
include_once TEMPLATES_PATH . 'footer.php';
?>