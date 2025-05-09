<?php
require_once '../includes/header.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] === 'client') {
    header("Location: dashboard.php");
    exit;
}

// Get packages and services data from database

$db = Database::getInstance();

// Get services for lookup
$services = $db->query("SELECT * FROM services ORDER BY name");

// Get all packages - without using array_agg
$packages = $db->query("SELECT * FROM packages ORDER BY name");

// For each package, get the services
foreach ($packages as &$package) {
    $package_services = $db->query(
        "SELECT service_id FROM package_services WHERE package_id = ?",
        [$package['id']]
    );
    $package['services'] = array_column($package_services, 'service_id');
}
unset($package); // break the reference


// Create lookup array for services
$servicesById = [];
foreach ($services as $service) {
    $servicesById[$service['id']] = $service;
}

// Get single package for edit if specified
$editPackage = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    foreach ($packages as $package) {
        if ($package['id'] == $_GET['edit']) {
            $editPackage = $package;
            break;
        }
    }
}
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Package Management</h1>
    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#packageModal">
        <i class="fas fa-plus fa-sm text-white-50"></i> Add New Package
    </a>
</div>

<!-- Packages Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Available Packages</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Services</th>
                        <th>Type</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($packages)): ?>
                        <?php foreach ($packages as $package): ?>
                            <tr>
                                <td><?php echo $package['id']; ?></td>
                                <td><?php echo $package['name']; ?></td>
                                <td>£<?php echo number_format($package['price'], 2); ?></td>
                                <td>
                                    <?php
                                    if (isset($package['services']) && is_array($package['services'])) {
                                        $serviceNames = [];
                                        foreach ($package['services'] as $serviceId) {
                                            if (isset($servicesById[$serviceId])) {
                                                $serviceNames[] = $servicesById[$serviceId]['name'];
                                            }
                                        }
                                        echo implode(', ', $serviceNames);
                                    } else {
                                        echo 'No services';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($package['customized']): ?>
                                        <span class="badge badge-info">Custom</span>
                                    <?php else: ?>
                                        <span class="badge badge-primary">Standard</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    // In a real application, you would look up the user name based on created_by
                                    echo isset($package['created_by']) ? 'User #' . $package['created_by'] : 'System';
                                    ?>
                                </td>
                                <td>
                                    <a href="?edit=<?php echo $package['id']; ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="../handlers/packages.php?action=delete&id=<?php echo $package['id']; ?>" class="btn btn-danger btn-sm btn-delete">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No packages available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Package Modal -->
<div class="modal fade" id="packageModal" tabindex="-1" role="dialog" aria-labelledby="packageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="packageModalLabel"><?php echo $editPackage ? 'Edit Package' : 'Add New Package'; ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="packageForm" class="api-form" action="../handlers/packages.php" method="POST" enctype="multipart/form-data" data-redirect="packages-admin.php">
                    <input type="hidden" name="action" value="<?php echo $editPackage ? 'update' : 'create'; ?>">
                    <?php if ($editPackage): ?>
                        <input type="hidden" name="id" value="<?php echo $editPackage['id']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="name">Package Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $editPackage ? $editPackage['name'] : ''; ?>" required>
                        <div id="nameFeedback" class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="price">Price</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">£</span>
                            </div>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo $editPackage ? $editPackage['price'] : ''; ?>" required>
                        </div>
                        <div id="priceFeedback" class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo $editPackage ? $editPackage['description'] : ''; ?></textarea>
                        <div id="descriptionFeedback" class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="image">Package Image</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="image" name="image" accept="image/*">
                            <label class="custom-file-label" for="image">Choose file</label>
                        </div>
                        <small class="form-text text-muted">Optional. Upload an image for the package.</small>
                        <?php if ($editPackage && isset($editPackage['image_url']) && $editPackage['image_url']): ?>
                            <div class="mt-2">
                                <img src="<?php echo $editPackage['image_url']; ?>" alt="Current Image" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="services">Services Included</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-magic"></i>
                                </span>
                            </div>
                            <select class="form-control select2" id="services" name="services[]" multiple="multiple">
                                <?php foreach ($services as $service): ?>
                                    <option value="<?php echo $service['id']; ?>" <?php echo ($editPackage && in_array($service['id'], $editPackage['services'])) ? 'selected' : ''; ?>>
                                        <?php echo $service['name']; ?> - £<?php echo number_format($service['price'], 2); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="servicesFeedback" class="invalid-feedback" style="display: none;">Please select at least one service.</div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><?php echo $editPackage ? 'Update Package' : 'Add Package'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Auto open modal if edit parameter is present -->
<?php if ($editPackage): ?>
    <script>
        $(document).ready(function() {
            $('#packageModal').modal('show');
        });
    </script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>