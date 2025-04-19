<?php
require_once '../includes/header.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] === 'client') {
    header("Location: dashboard.php");
    exit;
}

$db = Database::getInstance();

$services = $db->query("SELECT * FROM services");
$bookings = $db->query("SELECT * FROM bookings");
$users = $db->query("SELECT * FROM users");
$guests = $db->query("SELECT * FROM guests");
$packages = $db->query("SELECT * FROM packages");

// Get single service for edit if specified
$editService = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    foreach ($services as $service) {
        if ($service['id'] == $_GET['edit']) {
            $editService = $service;
            break;
        }
    }
}
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Services</h1>
    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#serviceModal">
        <i class="fas fa-plus fa-sm text-white-50"></i> Add New Service
    </a>
</div>

<!-- Services Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Services List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered dataTable" width="100%" cellspacing="0">
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
                    <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?php echo $service['id']; ?></td>
                        <td><?php echo $service['name']; ?></td>
                        <td><?php echo $service['description']; ?></td>
                        <td>$<?php echo number_format($service['price'], 2); ?></td>
                        <td>
                            <a href="?edit=<?php echo $service['id']; ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="../handlers/services.php?action=delete&id=<?php echo $service['id']; ?>" class="btn btn-danger btn-sm btn-delete">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($services)): ?>
                    <tr>
                        <td colspan="5" class="text-center">No services found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Service Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1" role="dialog" aria-labelledby="serviceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serviceModalLabel"><?php echo $editService ? 'Edit Service' : 'Add New Service'; ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="serviceForm" class="api-form" action="../handlers/services.php" method="POST" data-redirect="services.php">
                    <input type="hidden" name="action" value="<?php echo $editService ? 'update' : 'create'; ?>">
                    <?php if ($editService): ?>
                    <input type="hidden" name="id" value="<?php echo $editService['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Service Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $editService ? $editService['name'] : ''; ?>" required>
                        <div id="nameFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo $editService ? $editService['price'] : ''; ?>" required>
                        </div>
                        <div id="priceFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo $editService ? $editService['description'] : ''; ?></textarea>
                        <div id="descriptionFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><?php echo $editService ? 'Update Service' : 'Add Service'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Auto open modal if edit parameter is present -->
<?php if ($editService): ?>
<script>
$(document).ready(function() {
    $('#serviceModal').modal('show');
});
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
