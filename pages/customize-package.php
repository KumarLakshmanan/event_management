<?php
require_once '../includes/header.php';

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header("Location: dashboard.php");
    exit;
}

// Get services data
$db = Database::getInstance();
$services = $db->query("SELECT * FROM services ORDER BY name");

?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Create Custom Package</h1>
    <a href="packages.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Packages
    </a>
</div>

<!-- Custom Package Form Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Customize Your Package</h6>
    </div>
    <div class="card-body">
        <form id="customPackageForm" class="api-form" action="../handlers/packages.php" method="POST" data-redirect="packages.php">
            <input type="hidden" name="action" value="create_custom">
            <input type="hidden" name="customized" value="1">
            <input type="hidden" name="created_by" value="<?php echo $_SESSION['user_id']; ?>">

            <div class="form-group">
                <label for="name">Package Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
                <div id="nameFeedback" class="invalid-feedback"></div>
            </div>

            <div class="form-group">
                <label for="description">Package Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                <div id="descriptionFeedback" class="invalid-feedback"></div>
            </div>

            <div class="form-group">
                <label>Select Services</label>
                <div class="alert alert-info mb-3">
                    <p><i class="fas fa-info-circle"></i> Select the services you want to include in your package. The total price will be calculated based on your selections.</p>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 50px;"></th>
                                <th>Service</th>
                                <th>Description</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td class="text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input service-checkbox"
                                                id="service_<?php echo $service['id']; ?>"
                                                name="services[]"
                                                value="<?php echo $service['id']; ?>"
                                                data-price="<?php echo $service['price']; ?>">
                                            <label class="custom-control-label" for="service_<?php echo $service['id']; ?>"></label>
                                        </div>
                                    </td>
                                    <td><?php echo $service['name']; ?></td>
                                    <td><?php echo $service['description']; ?></td>
                                    <td>£<?php echo number_format($service['price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($services)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No services available.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div id="servicesFeedback" class="text-danger" style="display: none;">Please select at least one service.</div>
            </div>

            <div class="card mb-4 border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Package Price</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalPrice">$0.00</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="price" id="priceInput" value="0">

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Create Custom Package</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Calculate total price when services are selected/deselected
        $('.service-checkbox').on('change', function() {
            calculateTotalPrice();
        });

        // Initial calculation when page loads
        calculateTotalPrice();

        // Form validation
        $('#customPackageForm').on('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
        });

        function calculateTotalPrice() {
            var total = 0;
            $('.service-checkbox:checked').each(function() {
                var price = parseFloat($(this).data('price'));
                if (!isNaN(price)) {
                    total += price;
                }
            });

            $('#totalPrice').text('$' + total.toFixed(2));
            $('#priceInput').val(total);

            // Debug - log the calculation
            console.log('Total price calculated: $' + total.toFixed(2));
            console.log('Number of checked services: ' + $('.service-checkbox:checked').length);
        }

        function validateForm() {
            var isValid = true;

            // Check if at least one service is selected
            if ($('.service-checkbox:checked').length === 0) {
                $('#servicesFeedback').show();
                isValid = false;
            } else {
                $('#servicesFeedback').hide();
            }

            return isValid;
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>