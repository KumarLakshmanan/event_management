<?php
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get mock data
$packages = getMockData('packages.json');
$services = getMockData('services.json');

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
    <h1 class="h3 mb-0 text-gray-800">Packages</h1>
    <?php if ($_SESSION['user_role'] !== 'client'): ?>
    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#packageModal">
        <i class="fas fa-plus fa-sm text-white-50"></i> Add New Package
    </a>
    <?php endif; ?>
</div>

<!-- Packages Cards -->
<div class="row">
    <?php foreach ($packages as $package): ?>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100">
            <?php if ($package['image_url']): ?>
            <img class="card-img-top" src="<?php echo $package['image_url']; ?>" alt="<?php echo $package['name']; ?>">
            <?php else: ?>
            <div class="card-img-top bg-light text-center py-5">
                <i class="fas fa-box fa-5x text-gray-300"></i>
            </div>
            <?php endif; ?>
            <div class="card-body">
                <h4 class="card-title">
                    <a href="#"><?php echo $package['name']; ?></a>
                    <?php if ($package['customized']): ?>
                    <span class="badge badge-info">Custom</span>
                    <?php endif; ?>
                </h4>
                <h5>$<?php echo number_format($package['price'], 2); ?></h5>
                <p class="card-text"><?php echo $package['description']; ?></p>
                
                <div class="mt-3">
                    <h6>Services Included:</h6>
                    <ul class="list-group list-group-flush">
                        <?php 
                        if (isset($package['services']) && is_array($package['services'])) {
                            foreach ($package['services'] as $serviceId) {
                                if (isset($servicesById[$serviceId])) {
                                    echo '<li class="list-group-item px-0">' . $servicesById[$serviceId]['name'] . '</li>';
                                }
                            }
                        } else {
                            echo '<li class="list-group-item px-0">No services included</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div class="card-footer">
                <?php if ($_SESSION['user_role'] === 'client'): ?>
                <a href="#" class="btn btn-primary book-package" data-package-id="<?php echo $package['id']; ?>" data-toggle="modal" data-target="#bookingModal">Book Now</a>
                <?php else: ?>
                <a href="?edit=<?php echo $package['id']; ?>" class="btn btn-info">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="../handlers/packages.php?action=delete&id=<?php echo $package['id']; ?>" class="btn btn-danger btn-delete">
                    <i class="fas fa-trash"></i> Delete
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php if (empty($packages)): ?>
    <div class="col-12">
        <div class="alert alert-info">
            No packages available at the moment.
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($_SESSION['user_role'] !== 'client'): ?>
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
                <form id="packageForm" class="api-form" action="../handlers/packages.php" method="POST" enctype="multipart/form-data" data-redirect="packages.php">
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
                                <span class="input-group-text">$</span>
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
                        <?php if ($editPackage && $editPackage['image_url']): ?>
                        <div class="mt-2">
                            <img src="<?php echo $editPackage['image_url']; ?>" alt="Current Image" class="img-thumbnail" style="max-height: 100px;">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="services">Services Included</label>
                        <select class="form-control select2" id="services" name="services[]" multiple="multiple">
                            <?php foreach ($services as $service): ?>
                            <option value="<?php echo $service['id']; ?>" <?php echo ($editPackage && in_array($service['id'], $editPackage['services'])) ? 'selected' : ''; ?>>
                                <?php echo $service['name']; ?> - $<?php echo number_format($service['price'], 2); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
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
<?php else: ?>
<!-- Booking Modal for Clients -->
<div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookingModalLabel">Book Package</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="bookingForm" class="api-form" action="../handlers/bookings.php" method="POST" data-redirect="my-bookings.php">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="package_id" id="package_id" value="">
                    
                    <div class="form-group">
                        <label for="event_date">Event Date</label>
                        <input type="text" class="form-control datepicker" id="event_date" name="event_date" required>
                        <div id="eventDateFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_place">Event Place</label>
                        <input type="text" class="form-control" id="event_place" name="event_place" required>
                        <div id="eventPlaceFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Book Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Set package ID when booking modal is opened
    $('.book-package').on('click', function() {
        var packageId = $(this).data('package-id');
        $('#package_id').val(packageId);
    });
});
</script>
<?php endif; ?>

<!-- Auto open modal if edit parameter is present -->
<?php if ($editPackage): ?>
<script>
$(document).ready(function() {
    $('#packageModal').modal('show');
});
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
