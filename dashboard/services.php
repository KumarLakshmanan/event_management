<?php

/**
 * Service management page for Event Planning Platform
 */

// Include configuration
require_once dirname(__DIR__) . '/includes/config.php';
require_once INCLUDES_PATH . 'auth.php';
require_once INCLUDES_PATH . 'functions.php';

// Require manager or admin role
requireRole(['manager', 'administrator']);

// Get database connection
$db = getDBConnection();

// Initialize variables
$error = '';
$success = '';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$service = null;

// Process actions
switch ($action) {
    case 'create':
        // Process form submission for creating a service
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = sanitizeInput($_POST['name'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);

            // Validate input
            if (empty($name)) {
                $error = "Service name is required";
            } elseif ($price <= 0) {
                $error = "Price must be greater than zero";
            } else {
                // Insert service into database
                try {
                    $stmt = $db->prepare("INSERT INTO products (name, description, price) VALUES (:name, :description, :price)");
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':description', $description);
                    $stmt->bindParam(':price', $price);
                    $stmt->execute();

                    // Set success message and redirect to list view
                    $_SESSION['alert_message'] = "Service '$name' has been created successfully";
                    $_SESSION['alert_type'] = "success";
                    header("Location: services.php");
                    exit;
                } catch (PDOException $e) {
                    $error = "Error creating service: " . $e->getMessage();
                }
            }
        }
        break;

    case 'edit':
        // Get service ID
        $serviceId = isset($_GET['id']) ? intval($_GET['id']) : 0;

        // Get service data
        if ($serviceId > 0) {
            $stmt = $db->prepare("SELECT * FROM products WHERE id = :id AND deleted = 0");
            $stmt->bindParam(':id', $serviceId);
            $stmt->execute();
            $service = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$service) {
                $_SESSION['alert_message'] = "Service not found";
                $_SESSION['alert_type'] = "danger";
                header("Location: services.php");
                exit;
            }
        } else {
            $_SESSION['alert_message'] = "Invalid service ID";
            $_SESSION['alert_type'] = "danger";
            header("Location: services.php");
            exit;
        }

        // Process form submission for editing a service
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = sanitizeInput($_POST['name'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);

            // Validate input
            if (empty($name)) {
                $error = "Service name is required";
            } elseif ($price <= 0) {
                $error = "Price must be greater than zero";
            } else {
                // Update service in database
                try {
                    $stmt = $db->prepare("UPDATE products SET name = :name, description = :description, price = :price WHERE id = :id");
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':description', $description);
                    $stmt->bindParam(':price', $price);
                    $stmt->bindParam(':id', $serviceId);
                    $stmt->execute();

                    // Set success message and redirect to list view
                    $_SESSION['alert_message'] = "Service '$name' has been updated successfully";
                    $_SESSION['alert_type'] = "success";
                    header("Location: services.php");
                    exit;
                } catch (PDOException $e) {
                    $error = "Error updating service: " . $e->getMessage();
                }
            }
        }
        break;

    case 'delete':
        // Get service ID
        $serviceId = isset($_GET['id']) ? intval($_GET['id']) : 0;

        // Check if service is used in any packages
        if ($serviceId > 0) {
            // $stmt = $db->prepare("SELECT COUNT(*) as count FROM bundle_products WHERE product_id = :id");
            // $stmt->bindParam(':id', $serviceId);
            // $stmt->execute();
            // $usageCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // if ($usageCount > 0) {
            //     $_SESSION['alert_message'] = "This service cannot be deleted because it is used in $usageCount package(s)";
            //     $_SESSION['alert_type'] = "danger";
            //     header("Location: services.php");
            //     exit;
            // }

            // Get service name for confirmation message
            $stmt = $db->prepare("SELECT name FROM products WHERE deleted = 0 AND id = :id");
            $stmt->bindParam(':id', $serviceId);
            $stmt->execute();
            $serviceName = $stmt->fetch(PDO::FETCH_ASSOC)['name'];

            // Delete service from database
            try {
                // $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
                $stmt = $db->prepare("UPDATE products SET deleted = 1 WHERE id = :id");
                $stmt->bindParam(':id', $serviceId);
                $stmt->execute();

                // Set success message and redirect to list view
                $_SESSION['alert_message'] = "Service '$serviceName' has been deleted successfully";
                $_SESSION['alert_type'] = "success";
                header("Location: services.php");
                exit;
            } catch (PDOException $e) {
                $_SESSION['alert_message'] = "Error deleting service: " . $e->getMessage();
                $_SESSION['alert_type'] = "danger";
                header("Location: services.php");
                exit;
            }
        } else {
            $_SESSION['alert_message'] = "Invalid service ID";
            $_SESSION['alert_type'] = "danger";
            header("Location: services.php");
            exit;
        }
        break;

    case 'list':
    default:
        // Get all services
        $stmt = $db->query("SELECT * FROM products WHERE deleted = 0 ORDER BY name");
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
}

// Set page title
$pageTitle = 'Service Management';

// Include extra scripts for create/edit forms
if ($action == 'create' || $action == 'edit') {
    $extraScripts = '
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
            });
        </script>
    ';
}

// Include header
include_once TEMPLATES_PATH . 'header.php';
?>

<div class="container">
    <?php if ($action == 'list'): ?>
        <!-- Services list view -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Service Management</h1>
            <a href="?action=create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Add New Service
            </a>
        </div>

        <?php if (empty($services)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>No services found. Please add a service to get started.
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($services as $s): ?>
                                    <tr>
                                        <td><?= $s['id'] ?></td>
                                        <td><?= htmlspecialchars($s['name']) ?></td>
                                        <td><?= htmlspecialchars($s['description'] ?? '') ?></td>
                                        <td><?= formatCurrency($s['price']) ?></td>
                                        <td>
                                            <a href="?action=edit&id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit Service">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="?action=delete&id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete"
                                                title="Delete Service" data-name="<?= htmlspecialchars($s['name']) ?>">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php elseif ($action == 'create'): ?>
        <!-- Create service form -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Add New Service</h1>
            <a href="services.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Services
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="post" action="?action=create" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Service Name*</label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required>
                        <div class="invalid-feedback">
                            Please enter a service name.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= isset($description) ? htmlspecialchars($description) : '' ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price*</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="price" name="price"
                                value="<?= isset($price) ? htmlspecialchars($price) : '' ?>" required>
                            <div class="invalid-feedback">
                                Please enter a valid price (greater than zero).
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="services.php" class="btn btn-outline-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Service</button>
                    </div>
                </form>
            </div>
        </div>

    <?php elseif ($action == 'edit'): ?>
        <!-- Edit service form -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Edit Service</h1>
            <a href="services.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Services
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="post" action="?action=edit&id=<?= $service['id'] ?>" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Service Name*</label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="<?= htmlspecialchars($service['name']) ?>" required>
                        <div class="invalid-feedback">
                            Please enter a service name.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($service['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price*</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="price" name="price"
                                value="<?= htmlspecialchars($service['price']) ?>" required>
                            <div class="invalid-feedback">
                                Please enter a valid price (greater than zero).
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="services.php" class="btn btn-outline-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Service</button>
                    </div>
                </form>
            </div>
        </div>
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
                Are you sure you want to delete the service: <span id="delete-item-name" class="fw-bold"></span>?
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