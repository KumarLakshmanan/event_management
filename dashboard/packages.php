<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../controllers/PackageController.php';
require_once __DIR__ . '/../models/Package.php';
require_once __DIR__ . '/../models/Service.php';

// Require login for all actions
requireLogin();

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize controller
$packageController = new PackageController();

// Get the current user
$user = getCurrentUser();

// Check for package ID in URL
$packageId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Get action from URL
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Initialize variables
$errors = [];
$success = false;
$package = [];
$services = [];

// Process package actions (create/edit/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user has permission to manage packages
    // if (!hasPermission('manage_packages') && $action !== 'book') {
    //     setFlashMessage('You do not have permission to perform this action.', 'danger');
    //     header('Location: ' . APP_URL . 'dashboard/packages.php');
    //     exit;
    // }

    // Handle create/edit package form submission
    if (isset($_POST['save_package'])) {
        $data = [
            'name' => sanitizeInput($_POST['name']),
            'description' => sanitizeInput($_POST['description']),
            'price' => (float)$_POST['price']
        ];

        // Get selected services if any
        if (isset($_POST['services']) && is_array($_POST['services'])) {
            $data['services'] = $_POST['services'];
        } else {
            $data['services'] = [];
        }

        // Check if updating existing package or creating new one
        if (isset($_POST['package_id']) && is_numeric($_POST['package_id'])) {
            // Update existing package
            $result = $packageController->update($_POST['package_id'], $data);
            if ($result) {
                setFlashMessage('Package updated successfully!', 'success');
                header('Location: ' . APP_URL . 'dashboard/packages.php');
                exit;
            } else {
                $errors[] = 'Failed to update package.';
                $package = $packageController->getPackageWithServices($_POST['package_id']);
            }
        } else {
            // Create new package
            $newPackageId = $packageController->create($data, $user['id']);
            if ($newPackageId) {
                setFlashMessage('Package created successfully!', 'success');
                header('Location: ' . APP_URL . 'dashboard/packages.php');
                exit;
            } else {
                $errors[] = 'Failed to create package.';
            }
        }
    }

    // Handle delete package form submission
    if (isset($_POST['delete_package']) && isset($_POST['package_id']) && is_numeric($_POST['package_id'])) {
        $result = $packageController->delete($_POST['package_id']);
        if ($result) {
            setFlashMessage('Package deleted successfully!', 'success');
            header('Location: ' . APP_URL . 'dashboard/packages.php');
            exit;
        } else {
            $errors[] = 'Failed to delete package.';
        }
    }
}

// Handle different page actions
if ($action === 'create') {
    // Create new package
    $services = $packageController->getAllServices();
    $pageTitle = 'Create Package';
    $template = 'create_edit';
} elseif ($action === 'edit' && hasPermission('manage_packages') && $packageId) {
    // Edit existing package
    $package = $packageController->getPackageWithServices($packageId);
    if (!$package) {
        setFlashMessage('Package not found.', 'danger');
        header('Location: ' . APP_URL . 'dashboard/packages.php');
        exit;
    }
    $services = $packageController->getAllServices();
    $pageTitle = 'Edit Package';
    $template = 'create_edit';
} elseif ($action === 'delete' && hasPermission('manage_packages') && $packageId) {
    // Confirm delete package
    $package = $packageController->getPackageWithServices($packageId);
    if (!$package) {
        setFlashMessage('Package not found.', 'danger');
        header('Location: ' . APP_URL . 'dashboard/packages.php');
        exit;
    }
    $pageTitle = 'Delete Package';
    $template = 'delete';
} elseif ($packageId) {
    // View package details
    $package = $packageController->getPackageWithServices($packageId);
    if (!$package) {
        setFlashMessage('Package not found.', 'danger');
        header('Location: ' . APP_URL . 'dashboard/packages.php');
        exit;
    }
    $pageTitle = 'Package Details: ' . $package['name'];
    $template = 'view';
} else {
    // List all packages
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $result = $packageController->getAllPackages($page, 10, $user['id']);
    $packages = $result['packages'];
    $pagination = $result['pagination'];
    $pageTitle = 'Packages';
    $template = 'list';
}

// Set up page title and sidebar flag for template
$title = $pageTitle;
$showSidebar = true;

// Include header
include_once __DIR__ . '/../templates/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?php echo $pageTitle; ?></h1>
        <?php if ($template === 'list' && hasRole(ROLE_CLIENT)): ?>
            <a href="<?php echo APP_URL; ?>dashboard/packages.php?action=create" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Create New Package
            </a>
        <?php endif; ?>

        <?php if ($template === 'list' && hasPermission('manage_packages')): ?>
            <a href="<?php echo APP_URL; ?>dashboard/packages.php?action=create" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Create New Package
            </a>
        <?php elseif ($template !== 'list'): ?>
            <a href="<?php echo APP_URL; ?>dashboard/packages.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Packages
            </a>
        <?php endif; ?>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($template === 'list'): ?>
        <!-- Package List View -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (empty($packages)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No packages available. <?php if (hasPermission('manage_packages')): ?>Please create some packages.<?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($packages as $pkg): ?>
                    <div class="col">
                        <div class="card package-card h-100">
                            <div class="package-image"
                                style="background-image: url('<?php echo !empty($pkg['image_path']) ? APP_URL . 'uploads/' . $pkg['image_path'] : 'https://source.unsplash.com/random/600x400/?event'; ?>');">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($pkg['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($pkg['description'], 0, 100) . (strlen($pkg['description']) > 100 ? '...' : '')); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-5 fw-bold text-primary"><?php echo formatPrice($pkg['price']); ?></span>
                                    <a href="<?php echo APP_URL; ?>dashboard/packages.php?id=<?php echo $pkg['id']; ?>" class="btn btn-outline-primary">View Details</a>
                                </div>
                            </div>
                            <?php if (hasPermission('manage_packages')): ?>
                                <div class="card-footer bg-white d-flex justify-content-between">
                                    <a href="<?php echo APP_URL; ?>dashboard/packages.php?action=edit&id=<?php echo $pkg['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </a>
                                    <a href="<?php echo APP_URL; ?>dashboard/packages.php?action=delete&id=<?php echo $pkg['id']; ?>" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash-alt me-1"></i>Delete
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total'] > 1): ?>
            <div class="mt-4">
                <?php echo getPagination($pagination['current'], $pagination['total'], '/dashboard/packages.php'); ?>
            </div>
        <?php endif; ?>

    <?php elseif ($template === 'view'): ?>
        <!-- Package Detail View -->
        <div class="row">
            <div class="col-md-6">
                <div class="package-image mb-4" style="height: 300px; background-image: url('<?php echo !empty($package['image_path']) ? APP_URL . 'uploads/' . $package['image_path'] : 'https://source.unsplash.com/random/600x400/?event'; ?>');"></div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Package Details</h5>
                    </div>
                    <div class="card-body">
                        <h4 class="mb-3"><?php echo htmlspecialchars($package['name']); ?></h4>
                        <p class="lead mb-4"><?php echo htmlspecialchars($package['description']); ?></p>
                        <h5 class="text-primary mb-4"><?php echo formatPrice($package['price']); ?></h5>

                        <?php if (hasRole(ROLE_CLIENT)): ?>
                            <div class="d-grid gap-2">
                                <a href="<?php echo APP_URL; ?>dashboard/bookings.php?action=create&package_id=<?php echo $package['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-calendar-plus me-1"></i>Book This Package
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (hasPermission('manage_packages')): ?>
                            <div class="d-flex justify-content-between mt-4">
                                <a href="<?php echo APP_URL; ?>dashboard/packages.php?action=edit&id=<?php echo $package['id']; ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-edit me-1"></i>Edit Package
                                </a>
                                <a href="<?php echo APP_URL; ?>dashboard/packages.php?action=delete&id=<?php echo $package['id']; ?>" class="btn btn-outline-danger">
                                    <i class="fas fa-trash-alt me-1"></i>Delete Package
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Included -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Services Included</h5>
            </div>
            <div class="card-body">
                <?php if (empty($package['services'])): ?>
                    <p class="text-muted">No services included in this package.</p>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($package['services'] as $service): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="service-item p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($service['name']); ?></h6>
                                        <span class="badge bg-primary"><?php echo formatPrice($service['price']); ?></span>
                                    </div>
                                    <p class="text-muted small mb-0 mt-2"><?php echo htmlspecialchars($service['description']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($template === 'create_edit'): ?>
        <!-- Create/Edit Package Form -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0"><?php echo empty($package) ? 'Create New Package' : 'Edit Package'; ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="package_id" value="<?php echo isset($package['id']) ? $package['id'] : ''; ?>">
                    <?php if (hasRole(ROLE_CLIENT)): ?>
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <?php endif; ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Package Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="<?php echo isset($package['name']) ? htmlspecialchars($package['name']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">£</span>
                                <input type="number" class="form-control" readonly id="price" name="price" step="0.01" min="0"
                                    value="<?php echo isset($package['price']) ? $package['price'] : '0.00'; ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($package['description']) ? htmlspecialchars($package['description']) : ''; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Package Image</label>
                        <?php if (!empty($package['image_path'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo APP_URL . 'uploads/' . $package['image_path']; ?>" class="img-thumbnail" style="max-height: 150px;" alt="Package Image">
                                <div class="form-text">Current image. Upload a new one to replace it.</div>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>

                    <hr>

                    <h5 class="mb-3">Included Services</h5>

                    <?php if (empty($services)): ?>
                        <div class="alert alert-info">
                            No services available. <a href="<?php echo APP_URL; ?>dashboard/services.php?action=create">Create some services first</a>.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($services as $service):
                                $isSelected = false;
                                if (!empty($package['services'])) {
                                    foreach ($package['services'] as $selectedService) {
                                        if ($service['id'] == $selectedService['id']) {
                                            $isSelected = true;
                                            break;
                                        }
                                    }
                                }
                            ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="form-check service-item p-3 border rounded <?php echo $isSelected ? 'border-primary' : ''; ?>">
                                        <input class="form-check-input service-checkbox" type="checkbox" name="services[]"
                                            value="<?php echo $service['id']; ?>" id="service_<?php echo $service['id']; ?>"
                                            data-service-name="<?php echo htmlspecialchars($service['name']); ?>"
                                            data-service-price="<?php echo $service['price']; ?>"
                                            <?php echo $isSelected ? 'checked' : ''; ?>>
                                        <label class="form-check-label w-100" for="service_<?php echo $service['id']; ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($service['name']); ?></h6>
                                                <span class="badge bg-primary"><?php echo formatPrice($service['price']); ?></span>
                                            </div>
                                            <p class="text-muted small mb-0 mt-2"><?php echo htmlspecialchars($service['description']); ?></p>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-3">
                            <h6>Selected Services:</h6>
                            <ul class="list-group" id="selected-services-list">
                                <!-- Selected services will be populated via JavaScript -->
                            </ul>
                            <input type="hidden" id="selected-services" name="selected_services">
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?php echo APP_URL; ?>dashboard/packages.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" name="save_package" class="btn btn-primary">
                            <?php echo empty($package) ? 'Create Package' : 'Update Package'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <?php elseif ($template === 'delete'): ?>
        <!-- Delete Package Confirmation -->
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">Confirm Deletion</h5>
            </div>
            <div class="card-body">
                <p>Are you sure you want to delete the package <strong><?php echo htmlspecialchars($package['name']); ?></strong>?</p>
                <p>This action cannot be undone. All data associated with this package will be permanently removed.</p>

                <div class="alert alert-warning">
                    <h6>Package Details:</h6>
                    <ul>
                        <li><strong>Name:</strong> <?php echo htmlspecialchars($package['name']); ?></li>
                        <li><strong>Price:</strong> <?php echo formatPrice($package['price']); ?></li>
                        <li><strong>Services:</strong> <?php echo count($package['services']); ?> included</li>
                    </ul>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">

                    <div class="d-flex justify-content-between">
                        <a href="<?php echo APP_URL; ?>dashboard/packages.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" name="delete_package" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-1"></i>Confirm Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>
<script>
    // on check or uncheck service checkbox
    // change the price
    const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
    const selectedServicesList = document.getElementById('selected-services-list');
    const selectedServicesInput = document.getElementById('selected-services');
    const priceInput = document.getElementById('price');
    let totalPrice = parseFloat(priceInput.value);
    
    serviceCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const servicePrice = parseFloat(this.dataset.servicePrice);
            const serviceName = this.dataset.serviceName;

            if (this.checked) {
                totalPrice += servicePrice;
                const listItem = document.createElement('li');
                listItem.classList.add('list-group-item');
                listItem.textContent = `${serviceName} - ${servicePrice}`;
                selectedServicesList.appendChild(listItem);
            } else {
                totalPrice -= servicePrice;
                const listItems = selectedServicesList.querySelectorAll('li');
                listItems.forEach(item => {
                    if (item.textContent.includes(serviceName)) {
                        selectedServicesList.removeChild(item);
                    }
                });
            }

            priceInput.value = totalPrice.toFixed(2);
            selectedServicesInput.value = Array.from(selectedServicesList.children).map(item => item.textContent).join(',');
        });
    });
</script>
<?php include_once __DIR__ . '/../templates/footer.php'; ?>