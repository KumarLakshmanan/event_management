<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/Package.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize Package model
$packageModel = new Package();

// Get featured packages (we'll just get the first 3)
$featuredPackages = $packageModel->getAll(3, 0, 'id', 'DESC');

// Set page title for header
$title = 'Home';
$showSidebar = false;

// Include header template
include_once 'templates/header.php';
?>

<!-- Hero Banner Section -->
<div class="homepage-banner">
    <div class="container">
        <h1>Plan Your Perfect Event</h1>
        <p class="lead my-4">From intimate gatherings to grand celebrations, we make your events memorable.</p>
        <div class="mt-4">
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo APP_URL; ?>/dashboard/packages.php" class="btn btn-primary btn-lg me-2">Browse Packages</a>
                <a href="<?php echo APP_URL; ?>/dashboard/index.php" class="btn btn-outline-light btn-lg">My Dashboard</a>
            <?php else: ?>
                <a href="<?php echo APP_URL; ?>/auth/register.php" class="btn btn-primary btn-lg me-2">Get Started</a>
                <a href="<?php echo APP_URL; ?>/auth/login.php" class="btn btn-outline-light btn-lg">Login</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Services Overview -->
<div class="container my-5">
    <div class="text-center mb-5">
        <h2>Our Event Planning Services</h2>
        <p class="lead">We offer a range of services to make your event special</p>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-glass-cheers fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Wedding Planning</h5>
                    <p class="card-text">Make your special day perfect with our comprehensive wedding planning services.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-birthday-cake fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Birthday Celebrations</h5>
                    <p class="card-text">Create unforgettable birthday experiences with custom themes and entertainment.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-briefcase fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Corporate Events</h5>
                    <p class="card-text">Impress your clients and team with professionally organized corporate events.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Featured Packages -->
<div class="bg-light py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Featured Packages</h2>
            <p class="lead">Choose from our carefully curated event packages</p>
        </div>
        
        <div class="row g-4">
            <?php if (empty($featuredPackages)): ?>
                <div class="col-12 text-center">
                    <p>No packages available at the moment. Please check back later.</p>
                </div>
            <?php else: ?>
                <?php foreach ($featuredPackages as $package): ?>
                    <div class="col-md-4">
                        <div class="card package-card">
                            <div class="package-image" 
                                 style="background-image: url('<?php echo !empty($package['image_path']) ? APP_URL . '/uploads/' . $package['image_path'] : 'https://source.unsplash.com/random/600x400/?event'; ?>');">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($package['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($package['description'], 0, 100) . '...'); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-5 fw-bold text-primary"><?php echo formatPrice($package['price']); ?></span>
                                    <a href="<?php echo APP_URL; ?>/dashboard/packages.php?id=<?php echo $package['id']; ?>" class="btn btn-outline-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?php echo APP_URL; ?>/dashboard/packages.php" class="btn btn-primary">View All Packages</a>
        </div>
    </div>
</div>

<!-- Testimonials -->
<div class="container my-5">
    <div class="text-center mb-5">
        <h2>What Our Clients Say</h2>
        <p class="lead">Hear from people who have experienced our services</p>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="mb-3 text-warning">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="card-text">"The team made our wedding day absolutely perfect. Every detail was taken care of, and we could fully enjoy our special day without any stress."</p>
                    <div class="d-flex align-items-center mt-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-circle fa-2x text-secondary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Sarah & Michael</h6>
                            <small class="text-muted">Wedding Client</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="mb-3 text-warning">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="card-text">"Our company retreat was a huge success thanks to the meticulous planning and execution by the team. Everyone is still talking about it!"</p>
                    <div class="d-flex align-items-center mt-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-circle fa-2x text-secondary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Robert Johnson</h6>
                            <small class="text-muted">Corporate Client</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="mb-3 text-warning">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <p class="card-text">"My daughter's sweet sixteen was everything she dreamed of and more. The decorations, entertainment, and food were all top-notch."</p>
                    <div class="d-flex align-items-center mt-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-circle fa-2x text-secondary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Jennifer Davis</h6>
                            <small class="text-muted">Birthday Event Client</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="bg-primary text-white py-5">
    <div class="container text-center">
        <h2>Ready to Plan Your Event?</h2>
        <p class="lead mb-4">Get started today and make your next event unforgettable.</p>
        <?php if (isLoggedIn()): ?>
            <a href="<?php echo APP_URL; ?>/dashboard/packages.php" class="btn btn-light btn-lg">Browse Packages</a>
        <?php else: ?>
            <a href="<?php echo APP_URL; ?>/auth/register.php" class="btn btn-light btn-lg me-2">Create Account</a>
            <a href="<?php echo APP_URL; ?>/auth/login.php" class="btn btn-outline-light btn-lg">Login</a>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5>Event Planning Platform</h5>
                <p>Making event planning simple, efficient, and enjoyable for everyone.</p>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo APP_URL; ?>">Home</a></li>
                    <li><a href="<?php echo APP_URL; ?>/dashboard/packages.php">Packages</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="<?php echo APP_URL; ?>/dashboard/index.php">Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo APP_URL; ?>/auth/login.php">Login</a></li>
                        <li><a href="<?php echo APP_URL; ?>/auth/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Contact Us</h5>
                <p><i class="fas fa-envelope me-2"></i> info@eventplanning.com</p>
                <p><i class="fas fa-phone me-2"></i> (123) 456-7890</p>
                <div class="mt-3">
                    <a href="#" class="me-2 text-white"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="me-2 text-white"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="me-2 text-white"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin fa-lg"></i></a>
                </div>
            </div>
        </div>
        <hr class="my-4">
        <div class="text-center">
            <p>&copy; <?php echo date('Y'); ?> Event Planning Platform. All rights reserved.</p>
        </div>
    </div>
</footer>

<?php include_once 'templates/footer.php'; ?>
