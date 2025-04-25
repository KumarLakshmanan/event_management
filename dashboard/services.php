<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../controllers/ServiceController.php';
require_once __DIR__ . '/../models/Service.php';

// Require login for all actions
requireLogin();

// Check if user has permission to manage services
if (!hasPermission('manage_services')) {
    setFlashMessage('You do not have permission to manage services.', 'danger');
    header('Location: ' . APP_URL . 'dashboard/index.php');
    exit;
}

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize controller
$serviceController = new ServiceController();

// Check for service ID in URL
$serviceId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Get action from URL
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Initialize variables
$errors = [];
$success = false;
$service = [];

// Process service actions (create/edit/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle create/edit service form submission
    if (isset($_POST['save_service'])) {
        $data = [
            'name' => sanitizeInput($_POST['name']),
            'description' => sanitizeInput($_POST['description']),
            'price' => (float)$_POST['price']
        ];
        
        // Check if updating existing service or creating new one
        if (isset($_POST['service_id']) && is_numeric($_POST['service_id'])) {
            // Update existing service
            $result = $serviceController->update($_POST['service_id'], $data);
            if ($result) {
                setFlashMessage('Service updated successfully!', 'success');
                header('Location: ' . APP_URL . 'dashboard/services.php');
                exit;
            } else {
                $errors[] = 'Failed to update service.';
                $service = $serviceController->getService($_POST['service_id']);
            }
        } else {
            // Create new service
            $newServiceId = $serviceController->create($data);
            if ($newServiceId) {
                setFlashMessage('Service created successfully!', 'success');
                header('Location: ' . APP_URL . 'dashboard/services.php');
                exit;
            } else {
                $errors[] = 'Failed to create service.';
            }
        }
    }
    
    // Handle delete service form submission
    if (isset($_POST['delete_service']) && isset($_POST['service_id']) && is_numeric($_POST['service_id'])) {
        $result = $serviceController->delete($_POST['service_id']);
        if ($result) {
            setFlashMessage('Service deleted successfully!', 'success');
            header('Location: ' . APP_URL . 'dashboard/services.php');
            exit;
        } else {
            $errors[] = 'Failed to delete service. It may be in use by one or more packages.';
        }
    }
}

// Handle different page actions
if ($action === 'create') {
    // Create new service
    $pageTitle = 'Create Service';
    $template = 'create_edit';
} elseif ($action === 'edit' && $serviceId) {
    // Edit existing service
    $service = $serviceController->getService($serviceId);
    if (!$service) {
        setFlashMessage('Service not found.', 'danger');
        header('Location: ' . APP_URL . 'dashboard/services.php');
        exit;
    }
    $pageTitle = 'Edit Service';
    $template = 'create_edit';
} elseif ($action === 'delete' && $serviceId) {
    // Confirm delete service
    $service = $serviceController->getService($serviceId);
    if (!$service) {
        setFlashMessage('Service not found.', 'danger');
        header('Location: ' . APP_URL . 'dashboard/services.php');
        exit;
    }
    $packages = $serviceController->getPackagesWithService($serviceId);
    $pageTitle = 'Delete Service';
    $template = 'delete';
} elseif ($serviceId) {
    // View service details
    $service = $serviceController->getService($serviceId);
    if (!$service) {
        setFlashMessage('Service not found.', 'danger');
        header('Location: ' . APP_URL . 'dashboard/services.php');
        exit;
    }
    $packages = $serviceController->getPackagesWithService($serviceId);
    $pageTitle = 'Service Details: ' . $service['name'];
    $template = 'view';
} else {
    // List all services
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $result = $serviceController->getAllServices($page);
    $services = $result['services'];
    $pagination = $result['pagination'];
    $pageTitle = 'Services';
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
        
        <?php if ($template === 'list'): ?>
            <a href="<?php echo APP_URL; ?>dashboard/services.php?action=create" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Create New Service
            </a>
        <?php elseif ($template !== 'list'): ?>
            <a href="<?php echo APP_URL; ?>dashboard/services.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Services
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
        <!-- Service List View -->
        <div class="card">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title mb-0">All Services</h5>
                    </div>
                    <div class="col-auto">
                        <form class="d-flex" action="" method="GET">
                            <input type="text" class="form-control me-2" name="search" placeholder="Search services..." 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($services)): ?>
                    <div class="p-4 text-center">
                        <p>No services found. Create some services to get started.</p>
                        <a href="<?php echo APP_URL; ?>dashboard/services.php?action=create" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-1"></i>Create New Service
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($services as $svc): ?>
                                    <tr>
                                        <td><?php echo $svc['id']; ?></td>
                                        <td><?php echo htmlspecialchars($svc['name']); ?></td>
                                        <td>
                                            <?php 
                                            $desc = htmlspecialchars($svc['description']);
                                            echo strlen($desc) > 50 ? substr($desc, 0, 50) . '...' : $desc;
                                            ?>
                                        </td>
                                        <td><?php echo formatPrice($svc['price']); ?></td>
                                        <td><?php echo formatDate($svc['created_at'], 'M j, Y'); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo APP_URL; ?>dashboard/services.php?id=<?php echo $svc['id']; ?>" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo APP_URL; ?>dashboard/services.php?action=edit&id=<?php echo $svc['id']; ?>" class="btn btn-outline-secondary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo APP_URL; ?>dashboard/services.php?action=delete&id=<?php echo $svc['id']; ?>" class="btn btn-outline-danger">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($pagination['total'] > 1): ?>
                <div class="card-footer">
                    <?php echo getPagination($pagination['current'], $pagination['total'], '/dashboard/services.php'); ?>
                </div>
            <?php endif; ?>
        </div>
        
    <?php elseif ($template === 'view'): ?>
        <!-- Service Detail View -->
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Service Details</h5>
                    </div>
                    <div class="card-body">
                        <h4 class="mb-3"><?php echo htmlspecialchars($service['name']); ?></h4>
                        <h5 class="text-primary mb-3"><?php echo formatPrice($service['price']); ?></h5>
                        <div class="mb-4">
                            <h6>Description:</h6>
                            <p><?php echo htmlspecialchars($service['description']); ?></p>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo APP_URL; ?>dashboard/services.php?action=edit&id=<?php echo $service['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>Edit Service
                            </a>
                            <a href="<?php echo APP_URL; ?>dashboard/services.php?action=delete&id=<?php echo $service['id']; ?>" class="btn btn-outline-danger">
                                <i class="fas fa-trash-alt me-1"></i>Delete Service
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="small text-muted">
                            <div>Created: <?php echo formatDate($service['created_at']); ?></div>
                            <div>Last Updated: <?php echo formatDate($service['updated_at']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Included In Packages</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($packages)): ?>
                            <p class="text-muted">This service is not included in any package.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($packages as $package): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <a href="<?php echo APP_URL; ?>dashboard/packages.php?id=<?php echo $package['id']; ?>">
                                                <?php echo htmlspecialchars($package['name']); ?>
                                            </a>
                                        </div>
                                        <span class="badge bg-primary rounded-pill"><?php echo formatPrice($package['price']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
    <?php elseif ($template === 'create_edit'): ?>
        <!-- Create/Edit Service Form -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0"><?php echo empty($service) ? 'Create New Service' : 'Edit Service'; ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="service_id" value="<?php echo isset($service['id']) ? $service['id'] : ''; ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Service Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo isset($service['name']) ? htmlspecialchars($service['name']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">£</span>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" 
                                       value="<?php echo isset($service['price']) ? $service['price'] : '0.00'; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($service['description']) ? htmlspecialchars($service['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo APP_URL; ?>dashboard/services.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" name="save_service" class="btn btn-primary">
                            <?php echo empty($service) ? 'Create Service' : 'Update Service'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
    <?php elseif ($template === 'delete'): ?>
        <!-- Delete Service Confirmation -->
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">Confirm Deletion</h5>
            </div>
            <div class="card-body">
                <p>Are you sure you want to delete the service <strong><?php echo htmlspecialchars($service['name']); ?></strong>?</p>
                <p>This action cannot be undone.</p>
                
                <?php if (!empty($packages)): ?>
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">Warning!</h6>
                        <p>This service is currently included in <?php echo count($packages); ?> package(s):</p>
                        <ul>
                            <?php foreach ($packages as $package): ?>
                                <li><?php echo htmlspecialchars($package['name']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="mb-0">You need to remove this service from all packages before it can be deleted.</p>
                    </div>
                <?php endif; ?>
                
                <div class="alert alert-info">
                    <h6>Service Details:</h6>
                    <ul>
                        <li><strong>Name:</strong> <?php echo htmlspecialchars($service['name']); ?></li>
                        <li><strong>Price:</strong> <?php echo formatPrice($service['price']); ?></li>
                        <li><strong>Description:</strong> <?php echo htmlspecialchars($service['description']); ?></li>
                    </ul>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo APP_URL; ?>dashboard/services.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" name="delete_service" class="btn btn-danger" <?php echo !empty($packages) ? 'disabled' : ''; ?>>
                            <i class="fas fa-trash-alt me-1"></i>Confirm Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../templates/footer.php'; ?>
