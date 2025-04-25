<?php
/**
 * Package management page for Event Planning Platform
 */

// Include configuration
require_once dirname(__DIR__) . '/includes/config.php';
require_once INCLUDES_PATH . 'auth.php';
require_once INCLUDES_PATH . 'functions.php';

// Get database connection
$db = getDBConnection();

// Initialize variables
$error = '';
$success = '';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$package = null;
$packageServices = [];
$allServices = [];

// Check permissions based on role
if (hasRole('client')) {
    // Clients can view packages and create customized packages
    if ($action !== 'list' && $action !== 'create' && $action !== 'customize') {
        $action = 'list';
    }
} elseif (!hasRole('manager') && !hasRole('administrator')) {
    // Users without roles shouldn't be here
    setAlert('danger', 'You do not have permission to access this page');
    header('Location: index.php');
    exit;
}

// Process actions
switch ($action) {
    case 'customize':
        // This action creates a customized package for clients
        // Ensure user is logged in and has client role
        if (!hasRole('client')) {
            setAlert('danger', 'You must be logged in as a client to create customized packages');
            header('Location: packages.php');
            exit;
        }
        
        // Get all available services
        $stmt = $db->query("SELECT * FROM products ORDER BY name");
        $allServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process form submission for creating a customized package
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = sanitizeInput($_POST['name'] ?? 'Customized Package');
            $description = sanitizeInput($_POST['description'] ?? '');
            $selectedServiceIds = $_POST['services'] ?? [];
            $calculatedPrice = isset($_POST['calculated_price']) ? (float)$_POST['calculated_price'] : 0;
            
            // Calculate price based on selected services if not provided
            if ($calculatedPrice == 0 && !empty($selectedServiceIds)) {
                $serviceIds = implode(',', array_map('intval', $selectedServiceIds));
                $stmt = $db->query("SELECT SUM(price) as total FROM products WHERE id IN ($serviceIds)");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $calculatedPrice = $result['total'] ?? 0;
            }
            
            // Validate input
            if (empty($name)) {
                $error = "Package name is required";
            } elseif (empty($selectedServiceIds)) {
                $error = "Please select at least one service for your customized package";
            } else {
                // Begin transaction
                $db->beginTransaction();
                
                try {
                    // Insert package into database (always customized for this action)
                    $customized = 1;
                    $stmt = $db->prepare("INSERT INTO bundles (name, description, price, customized, created_by, created_at) 
                                         VALUES (:name, :description, :price, :customized, :created_by, CURRENT_TIMESTAMP)");
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':description', $description);
                    $stmt->bindParam(':price', $calculatedPrice);
                    $stmt->bindParam(':customized', $customized);
                    $stmt->bindParam(':created_by', $_SESSION['user_id']);
                    $stmt->execute();
                    
                    // Get the newly inserted package ID
                    $packageId = $db->lastInsertId();
                    
                    // Insert package services
                    $insertServiceStmt = $db->prepare("INSERT INTO bundle_products (bundle_id, product_id) VALUES (:bundle_id, :product_id)");
                    
                    foreach ($selectedServiceIds as $serviceId) {
                        $insertServiceStmt->bindParam(':bundle_id', $packageId);
                        $insertServiceStmt->bindParam(':product_id', $serviceId);
                        $insertServiceStmt->execute();
                    }
                    
                    // Create notification
                    createNotification(
                        $_SESSION['user_id'],
                        'package_created',
                        'Created a new customized package: ' . $name,
                        'packages.php?action=view&id=' . $packageId
                    );
                    
                    // Commit transaction
                    $db->commit();
                    
                    // Set success message and redirect to view
                    setAlert('success', 'Your customized package has been created successfully with a total price of ' . formatCurrency($calculatedPrice));
                    header('Location: packages.php?action=view&id=' . $packageId);
                    exit;
                } catch (PDOException $e) {
                    // Rollback transaction on error
                    $db->rollBack();
                    $error = "Error creating customized package: " . $e->getMessage();
                }
            }
        }
        break;
        
    case 'create':
        // Get all available services
        $stmt = $db->query("SELECT * FROM products ORDER BY name");
        $allServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process form submission for creating a package
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = sanitizeInput($_POST['name'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $customized = isset($_POST['customized']) ? 1 : 0;
            $selectedServiceIds = $_POST['services'] ?? [];
            
            // Validate input
            if (empty($name)) {
                $error = "Package name is required";
            } elseif ($price < 0) {
                $error = "Price cannot be negative";
            } elseif (empty($selectedServiceIds) && !$customized) {
                $error = "Please select at least one service for the package";
            } else {
                // Begin transaction
                $db->beginTransaction();
                
                try {
                    // Insert package into database
                    $stmt = $db->prepare("INSERT INTO bundles (name, description, price, customized, created_by, created_at) 
                                         VALUES (:name, :description, :price, :customized, :created_by, CURRENT_TIMESTAMP)");
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':description', $description);
                    $stmt->bindParam(':price', $price);
                    $stmt->bindParam(':customized', $customized);
                    $stmt->bindParam(':created_by', $_SESSION['user_id']);
                    $stmt->execute();
                    
                    // Get the newly inserted package ID
                    $packageId = $db->lastInsertId();
                    
                    // Insert package services if any selected
                    if (!empty($selectedServiceIds)) {
                        $insertServiceStmt = $db->prepare("INSERT INTO bundle_products (bundle_id, product_id) VALUES (:bundle_id, :product_id)");
                        
                        foreach ($selectedServiceIds as $serviceId) {
                            $insertServiceStmt->bindParam(':bundle_id', $packageId);
                            $insertServiceStmt->bindParam(':product_id', $serviceId);
                            $insertServiceStmt->execute();
                        }
                    }
                    
                    // Upload image if provided
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $imagePath = uploadImage($_FILES['image'], 'assets/img/packages');
                        
                        if ($imagePath) {
                            // Update package with image path
                            $stmt = $db->prepare("UPDATE bundles SET image_url = :image_url WHERE id = :id");
                            $stmt->bindParam(':image_url', $imagePath);
                            $stmt->bindParam(':id', $packageId);
                            $stmt->execute();
                        }
                    }
                    
                    // Commit transaction
                    $db->commit();
                    
                    // Set success message and redirect to list view
                    $_SESSION['alert_message'] = "Package '$name' has been created successfully";
                    $_SESSION['alert_type'] = "success";
                    header("Location: packages.php");
                    exit;
                } catch (PDOException $e) {
                    // Rollback transaction on error
                    $db->rollBack();
                    $error = "Error creating package: " . $e->getMessage();
                }
            }
        }
        break;
        
    case 'edit':
        // Get package ID
        $packageId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        // Get all available services
        $stmt = $db->query("SELECT * FROM products ORDER BY name");
        $allServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get package data
        if ($packageId > 0) {
            $stmt = $db->prepare("SELECT * FROM bundles WHERE id = :id");
            $stmt->bindParam(':id', $packageId);
            $stmt->execute();
            $package = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$package) {
                $_SESSION['alert_message'] = "Package not found";
                $_SESSION['alert_type'] = "danger";
                header("Location: packages.php");
                exit;
            }
            
            // Get package services
            $stmt = $db->prepare("
                SELECT ps.*, p.name, p.price
                FROM bundle_products ps
                JOIN products p ON ps.product_id = p.id
                WHERE ps.bundle_id = :bundle_id
            ");
            $stmt->bindParam(':bundle_id', $packageId);
            $stmt->execute();
            $packageServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Create array of service IDs for easier checking in the form
            $selectedServiceIds = array_map(function($service) {
                return $service['product_id'];
            }, $packageServices);
        } else {
            $_SESSION['alert_message'] = "Invalid package ID";
            $_SESSION['alert_type'] = "danger";
            header("Location: packages.php");
            exit;
        }
        
        // Process form submission for editing a package
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = sanitizeInput($_POST['name'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $customized = isset($_POST['customized']) ? 1 : 0;
            $selectedServiceIds = $_POST['services'] ?? [];
            
            // Validate input
            if (empty($name)) {
                $error = "Package name is required";
            } elseif ($price < 0) {
                $error = "Price cannot be negative";
            } elseif (empty($selectedServiceIds) && !$customized) {
                $error = "Please select at least one service for the package";
            } else {
                // Begin transaction
                $db->beginTransaction();
                
                try {
                    // Update package in database
                    $stmt = $db->prepare("UPDATE bundles SET name = :name, description = :description, price = :price, customized = :customized WHERE id = :id");
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':description', $description);
                    $stmt->bindParam(':price', $price);
                    $stmt->bindParam(':customized', $customized);
                    $stmt->bindParam(':id', $packageId);
                    $stmt->execute();
                    
                    // Delete existing package services
                    $stmt = $db->prepare("DELETE FROM bundle_products WHERE bundle_id = :bundle_id");
                    $stmt->bindParam(':bundle_id', $packageId);
                    $stmt->execute();
                    
                    // Insert new package services if any selected
                    if (!empty($selectedServiceIds)) {
                        $insertServiceStmt = $db->prepare("INSERT INTO bundle_products (bundle_id, product_id) VALUES (:bundle_id, :product_id)");
                        
                        foreach ($selectedServiceIds as $serviceId) {
                            $insertServiceStmt->bindParam(':bundle_id', $packageId);
                            $insertServiceStmt->bindParam(':product_id', $serviceId);
                            $insertServiceStmt->execute();
                        }
                    }
                    
                    // Upload image if provided
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $imagePath = uploadImage($_FILES['image'], 'assets/img/packages');
                        
                        if ($imagePath) {
                            // Update package with image path
                            $stmt = $db->prepare("UPDATE bundles SET image_url = :image_url WHERE id = :id");
                            $stmt->bindParam(':image_url', $imagePath);
                            $stmt->bindParam(':id', $packageId);
                            $stmt->execute();
                        }
                    }
                    
                    // Commit transaction
                    $db->commit();
                    
                    // Set success message and redirect to list view
                    $_SESSION['alert_message'] = "Package '$name' has been updated successfully";
                    $_SESSION['alert_type'] = "success";
                    header("Location: packages.php");
                    exit;
                } catch (PDOException $e) {
                    // Rollback transaction on error
                    $db->rollBack();
                    $error = "Error updating package: " . $e->getMessage();
                }
            }
        }
        break;
        
    case 'delete':
        // Require manager or admin role
        requireRole(['manager', 'administrator']);
        
        // Get package ID
        $packageId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        // Check if package is used in any bookings
        if ($packageId > 0) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM reservations WHERE bundle_id = :id");
            $stmt->bindParam(':id', $packageId);
            $stmt->execute();
            $usageCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($usageCount > 0) {
                $_SESSION['alert_message'] = "This package cannot be deleted because it is used in $usageCount booking(s)";
                $_SESSION['alert_type'] = "danger";
                header("Location: packages.php");
                exit;
            }
            
            // Get package name for confirmation message
            $stmt = $db->prepare("SELECT name FROM bundles WHERE id = :id");
            $stmt->bindParam(':id', $packageId);
            $stmt->execute();
            $packageName = $stmt->fetch(PDO::FETCH_ASSOC)['name'];
            
            // Begin transaction
            $db->beginTransaction();
            
            try {
                // Delete package services
                $stmt = $db->prepare("DELETE FROM bundle_products WHERE bundle_id = :id");
                $stmt->bindParam(':id', $packageId);
                $stmt->execute();
                
                // Delete package
                $stmt = $db->prepare("DELETE FROM bundles WHERE id = :id");
                $stmt->bindParam(':id', $packageId);
                $stmt->execute();
                
                // Commit transaction
                $db->commit();
                
                // Set success message and redirect to list view
                $_SESSION['alert_message'] = "Package '$packageName' has been deleted successfully";
                $_SESSION['alert_type'] = "success";
                header("Location: packages.php");
                exit;
            } catch (PDOException $e) {
                // Rollback transaction on error
                $db->rollBack();
                $_SESSION['alert_message'] = "Error deleting package: " . $e->getMessage();
                $_SESSION['alert_type'] = "danger";
                header("Location: packages.php");
                exit;
            }
        } else {
            $_SESSION['alert_message'] = "Invalid package ID";
            $_SESSION['alert_type'] = "danger";
            header("Location: packages.php");
            exit;
        }
        break;
        
    case 'view':
        // Get package ID
        $packageId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        // Get package data
        if ($packageId > 0) {
            $stmt = $db->prepare("
                SELECT b.*, m.name as created_by_name 
                FROM bundles b
                LEFT JOIN members m ON b.created_by = m.id
                WHERE b.id = :id
            ");
            $stmt->bindParam(':id', $packageId);
            $stmt->execute();
            $package = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$package) {
                $_SESSION['alert_message'] = "Package not found";
                $_SESSION['alert_type'] = "danger";
                header("Location: packages.php");
                exit;
            }
            
            // Get package services
            $stmt = $db->prepare("
                SELECT ps.*, p.name, p.description, p.price
                FROM bundle_products ps
                JOIN products p ON ps.product_id = p.id
                WHERE ps.bundle_id = :bundle_id
            ");
            $stmt->bindParam(':bundle_id', $packageId);
            $stmt->execute();
            $packageServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $_SESSION['alert_message'] = "Invalid package ID";
            $_SESSION['alert_type'] = "danger";
            header("Location: packages.php");
            exit;
        }
        break;
        
    case 'list':
    default:
        // Determine which packages to show based on user role
        if (hasRole(['manager', 'administrator'])) {
            // Admins and managers see all packages
            $stmt = $db->query("SELECT b.*, u.name as created_by_name 
                                FROM bundles b
                                LEFT JOIN members u ON b.created_by = u.id
                                ORDER BY b.name");
        } else {
            // Regular clients see only:
            // 1. System-created packages (not user-created custom packages)
            // 2. Their own custom packages
            $userId = $_SESSION['user_id'];
            $stmt = $db->prepare("SELECT b.*, u.name as created_by_name 
                                  FROM bundles b
                                  LEFT JOIN members u ON b.created_by = u.id
                                  WHERE b.created_by IS NULL 
                                  OR b.created_by = :user_id 
                                  ORDER BY b.name");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
        }
        
        $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get services count for each package
        foreach ($packages as &$pkg) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM bundle_products WHERE bundle_id = :id");
            $stmt->bindParam(':id', $pkg['id']);
            $stmt->execute();
            $pkg['services_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        }
        break;
}

// Set page title
if ($action == 'customize') {
    $pageTitle = 'Create Customized Package';
} else {
    $pageTitle = 'Package Management';
}

// Initialize extraScripts variable
$extraScripts = '';

// Include extra scripts for forms
if ($action == 'create' || $action == 'edit' || $action == 'customize') {
    // Add the dynamic price calculation script for customize view
    if ($action == 'customize') {
        $extraScripts .= '
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Initialize dynamic price calculation
                initCustomPackagePricing();
            });
        </script>
        ';
    }
    
    $extraScripts .= '
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Price input formatting
                const priceInput = document.getElementById("price");
                if (priceInput) {
                    priceInput.addEventListener("input", function() {
                        if (this.value.length > 0) {
                            // Ensure value is a valid number
                            this.value = this.value.replace(/[^0-9.]/g, "");
                            
                            // Allow only one decimal point
                            const decimalPoints = this.value.match(/\./g);
                            if (decimalPoints && decimalPoints.length > 1) {
                                this.value = this.value.slice(0, this.value.lastIndexOf("."));
                            }
                        }
                    });
                }
                
                // Preview image
                const imageInput = document.getElementById("image");
                const imagePreview = document.getElementById("imagePreview");
                const currentImage = document.getElementById("currentImage");
                
                if (imageInput && imagePreview) {
                    imageInput.addEventListener("change", function() {
                        if (this.files && this.files[0]) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                imagePreview.src = e.target.result;
                                imagePreview.style.display = "block";
                                if (currentImage) {
                                    currentImage.style.display = "none";
                                }
                            };
                            reader.readAsDataURL(this.files[0]);
                        }
                    });
                }
                
                // Toggle customized package
                const customizedCheckbox = document.getElementById("customized");
                const servicesSection = document.getElementById("servicesSection");
                
                if (customizedCheckbox && servicesSection) {
                    customizedCheckbox.addEventListener("change", function() {
                        if (this.checked) {
                            servicesSection.style.display = "none";
                        } else {
                            servicesSection.style.display = "block";
                        }
                    });
                    
                    // Initial state
                    if (customizedCheckbox.checked) {
                        servicesSection.style.display = "none";
                    }
                }
            });
        </script>
    ';
}

// Include header
include_once TEMPLATES_PATH . 'header.php';
?>

<div class="container">
    <?php if ($action == 'list'): ?>
        <!-- Packages list view -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Event Packages</h1>
            <div>
                <?php if (hasRole('client') && !hasRole('administrator')): ?>
                    <a href="?action=customize" class="btn btn-info me-2">
                        <i class="bi bi-pencil-square me-2"></i>Customize Your Package
                    </a>
                <?php endif; ?>
                <?php if (hasRole(['manager', 'administrator'])): ?>
                    <a href="?action=create" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Create New Package
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (empty($packages)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>No packages found.
                <?php if (hasRole(['manager', 'administrator'])): ?>
                    Please create a package to get started.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($packages as $pkg): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card package-card h-100">
                            <?php if (!empty($pkg['image_url'])): ?>
                                <img src="/<?= htmlspecialchars($pkg['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($pkg['name']) ?>">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center">
                                    <i class="bi bi-box text-secondary" style="font-size: 4rem;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($pkg['name']) ?></h5>
                                <p class="card-text text-muted">
                                    <?php if ($pkg['customized']): ?>
                                        <span class="badge bg-info">Customizable</span>
                                    <?php else: ?>
                                        <small><?= $pkg['services_count'] ?> services included</small>
                                    <?php endif; ?>
                                </p>
                                
                                <?php if (!empty($pkg['description'])): ?>
                                    <p class="card-text"><?= nl2br(htmlspecialchars($pkg['description'])) ?></p>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="fs-5 fw-bold text-primary">
                                        <?= !empty($pkg['price']) ? formatCurrency($pkg['price']) : 'Custom pricing' ?>
                                    </span>
                                    <div>
                                        <a href="?action=view&id=<?= $pkg['id'] ?>" class="btn btn-outline-primary">View Details</a>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (hasRole(['manager', 'administrator'])): ?>
                                <div class="card-footer bg-white">
                                    <div class="d-flex justify-content-between">
                                        <a href="?action=edit&id=<?= $pkg['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil me-1"></i>Edit
                                        </a>
                                        <a href="?action=delete&id=<?= $pkg['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete"
                                           data-name="<?= htmlspecialchars($pkg['name']) ?>"
                                           onclick="return confirm('Are you sure you want to delete the package: <?= htmlspecialchars($pkg['name']) ?>?');">
                                            <i class="bi bi-trash me-1"></i>Delete
                                        </a>
                                    </div>
                                </div>
                            <?php elseif (hasRole('client')): ?>
                                <div class="card-footer bg-white">
                                    <a href="<?= BASE_URL ?>dashboard/bookings.php?action=create&package_id=<?= $pkg['id'] ?>" class="btn btn-primary w-100">
                                        <i class="bi bi-calendar-plus me-2"></i>Book This Package
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
    <?php elseif ($action == 'customize'): ?>
        <?php if (hasRole('administrator')): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>Administrators cannot create customized packages. This feature is available only to clients.
            </div>
            <div class="text-center mt-3">
                <a href="<?= BASE_URL ?>dashboard/packages.php" class="btn btn-primary">Return to Packages</a>
            </div>
        <?php else: ?>
        <!-- Customize Package Form -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Create Your Customized Package</h1>
            <a href="<?= BASE_URL ?>dashboard/packages.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Packages
            </a>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger mb-4"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php endif; ?>
        
        <form method="post" action="packages.php?action=customize">
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Your Customized Package</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="name" class="form-label">Package Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="My Customized Package" required>
                                <div class="form-text">Name your custom package</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Notes</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                <div class="form-text">Any special requirements or notes</div>
                            </div>
                            
                            <input type="hidden" id="calculated_price" name="calculated_price" value="0">
                            
                            <div class="alert alert-info">
                                <p><i class="bi bi-info-circle me-2"></i>Select the services you want included in your custom package. The price will update automatically.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="packages.php" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Create My Package</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div id="servicesSection" class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Available Services</h5>
                            <div class="badge bg-primary fs-6" id="totalPrice">£0.00</div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($allServices)): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>No services available.
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($allServices as $service): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="service_<?= $service['id'] ?>" 
                                                               name="services[]" value="<?= $service['id'] ?>">
                                                        <label class="form-check-label fw-bold" for="service_<?= $service['id'] ?>">
                                                            <?= htmlspecialchars($service['name']) ?> 
                                                            <span class="badge bg-info">(£<?= number_format($service['price'], 2) ?>)</span>
                                                        </label>
                                                    </div>
                                                    <p class="mt-2 mb-0 text-muted small"><?= htmlspecialchars($service['description']) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php elseif ($action == 'view'): ?>
        <!-- Package detail view -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?= htmlspecialchars($package['name']) ?></h1>
            <div>
                <?php if (hasRole('client')): ?>
                    <a href="<?= BASE_URL ?>dashboard/bookings.php?action=create&package_id=<?= $package['id'] ?>" class="btn btn-primary">
                        <i class="bi bi-calendar-plus me-2"></i>Book This Package
                    </a>
                <?php endif; ?>
                <a href="packages.php" class="btn btn-outline-secondary ms-2">
                    <i class="bi bi-arrow-left me-2"></i>Back to Packages
                </a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="mb-3">Package Details</h5>
                        <div class="mb-4">
                            <?php if (!empty($package['description'])): ?>
                                <p><?= nl2br(htmlspecialchars($package['description'])) ?></p>
                            <?php else: ?>
                                <p class="text-muted">No description available.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>Price</h6>
                                <p class="fs-5 fw-bold text-primary">
                                    <?= !empty($package['price']) ? formatCurrency($package['price']) : 'Custom pricing' ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6>Type</h6>
                                <p>
                                    <?php if ($package['customized']): ?>
                                        <span class="badge bg-info">Customizable Package</span>
                                        <small class="d-block text-muted mt-1">Services can be selected during booking</small>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Fixed Package</span>
                                        <small class="d-block text-muted mt-1">Includes specific services</small>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Created By</h6>
                                <p><?= htmlspecialchars($package['created_by_name'] ?? 'System') ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Created On</h6>
                                <p><?= formatDate($package['created_at']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!$package['customized']): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Included Services</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($packageServices)): ?>
                                <p class="text-muted">No services included in this package.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Service</th>
                                                <th>Description</th>
                                                <th>Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $totalValue = 0;
                                            foreach ($packageServices as $service): 
                                                $totalValue += $service['price'];
                                            ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($service['name']) ?></td>
                                                    <td><?= htmlspecialchars($service['description'] ?? '') ?></td>
                                                    <td><?= formatCurrency($service['price']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2" class="text-end">Total Value:</th>
                                                <th><?= formatCurrency($totalValue) ?></th>
                                            </tr>
                                            <?php if (!empty($package['price']) && $totalValue > $package['price']): ?>
                                                <tr>
                                                    <td colspan="2" class="text-end text-success">You Save:</td>
                                                    <td class="text-success fw-bold">
                                                        <?= formatCurrency($totalValue - $package['price']) ?>
                                                        (<?= round((($totalValue - $package['price']) / $totalValue) * 100) ?>%)
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tfoot>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Package Image</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php if (!empty($package['image_url'])): ?>
                            <img src="/<?= htmlspecialchars($package['image_url']) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($package['name']) ?>">
                        <?php else: ?>
                            <div class="bg-light py-5 rounded">
                                <i class="bi bi-image text-secondary" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">No image available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (hasRole(['manager', 'administrator'])): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Management Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="?action=edit&id=<?= $package['id'] ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil me-2"></i>Edit Package
                                </a>
                                <a href="?action=delete&id=<?= $package['id'] ?>" class="btn btn-outline-danger btn-delete"
                                   data-name="<?= htmlspecialchars($package['name']) ?>"
                                   onclick="return confirm('Are you sure you want to delete the package: <?= htmlspecialchars($package['name']) ?>?');">
                                    <i class="bi bi-trash me-2"></i>Delete Package
                                </a>
                            </div>
                        </div>
                    </div>
                <?php elseif (hasRole('client')): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Booking Information</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">Ready to book this package for your event?</p>
                            <div class="d-grid">
                                <a href="<?= BASE_URL ?>dashboard/bookings.php?action=create&package_id=<?= $package['id'] ?>" class="btn btn-primary">
                                    <i class="bi bi-calendar-plus me-2"></i>Book This Package
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    <?php elseif ($action == 'create' || $action == 'edit'): ?>
        <!-- Create/Edit package form -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?= $action == 'create' ? 'Create New Package' : 'Edit Package' ?></h1>
            <a href="packages.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Packages
            </a>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="?action=<?= $action ?><?= ($action == 'edit') ? '&id=' . $package['id'] : '' ?>" class="needs-validation" novalidate enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Package Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="name" class="form-label">Package Name*</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= isset($package['name']) ? htmlspecialchars($package['name']) : '' ?>" required>
                                <div class="invalid-feedback">
                                    Please enter a package name.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?= isset($package['description']) ? htmlspecialchars($package['description']) : '' ?></textarea>
                                <div class="form-text">Provide details about what this package offers and what makes it special.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="price" class="form-label">Package Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">£</span>
                                    <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" 
                                           value="<?= isset($package['price']) ? htmlspecialchars($package['price']) : '' ?>">
                                </div>
                                <div class="form-text">Leave blank or set to 0 for custom pricing.</div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="customized" name="customized" value="1" 
                                       <?= (isset($package['customized']) && $package['customized'] == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="customized">
                                    Customizable Package
                                </label>
                                <div class="form-text">If checked, clients can select individual services during booking. Services below will be ignored.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="servicesSection" class="card mb-4" <?= (isset($package['customized']) && $package['customized'] == 1) ? 'style="display: none;"' : '' ?>>
                        <div class="card-header">
                            <h5 class="mb-0">Included Services</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($allServices)): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>No services available. Please <a href="/dashboard/services.php?action=create">create some services</a> first.
                                </div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <div class="form-text mb-3">Select the services that are included in this package:</div>
                                    <div class="row">
                                        <?php foreach ($allServices as $service): ?>
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="service_<?= $service['id'] ?>" 
                                                           name="services[]" value="<?= $service['id'] ?>" 
                                                           <?= (isset($selectedServiceIds) && in_array($service['id'], $selectedServiceIds)) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="service_<?= $service['id'] ?>">
                                                        <?= htmlspecialchars($service['name']) ?> 
                                                        <span class="text-muted">(<?= formatCurrency($service['price']) ?>)</span>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Package Image</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="image" class="form-label">Upload Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <div class="form-text">Recommended size: 800x600 pixels. Max file size: 2MB.</div>
                            </div>
                            
                            <div class="text-center mt-3">
                                <?php if ($action == 'edit' && !empty($package['image_url'])): ?>
                                    <p>Current Image:</p>
                                    <img id="currentImage" src="/<?= htmlspecialchars($package['image_url']) ?>" class="img-fluid rounded mb-3" alt="Current package image">
                                <?php endif; ?>
                                <img id="imagePreview" class="img-fluid rounded mb-3" style="display: none;" alt="Image preview">
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="packages.php" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <?= $action == 'create' ? 'Create Package' : 'Update Package' ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal (hidden) -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the package: <span id="delete-item-name" class="fw-bold"></span>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirm-delete" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once TEMPLATES_PATH . 'footer.php';
?>