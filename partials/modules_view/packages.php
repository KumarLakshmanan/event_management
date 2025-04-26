<?php


$sql = "SELECT
    p.*,    
    p.id AS package_id,
    p.package_name,
    p.price,
    p.description,
    GROUP_CONCAT(s.service_name ORDER BY s.service_name SEPARATOR ', ') AS service_name
FROM package p
LEFT JOIN package_services ps ON p.id = ps.package_id
LEFT JOIN service s ON ps.service_id = s.id
where p.created_by = 0
GROUP BY p.id
ORDER BY p.id DESC;
";
$stmt = $pdoConn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-transparent">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manage Bundles</h5>
                    <a href="<?= $adminBaseUrl ?>addpackage" class="btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i> Add New Bundle
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-card-title">TOTAL BUNDLES</div>
                    <div class="stat-card-value"><?= count($result) ?></div>
                </div>
                <div class="stat-card-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <?php $i = 1; foreach ($result as $row): ?>
    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
        <div class="package-card h-100">
            <div class="position-relative">
                <img src="<?= !empty($row['image_url']) ? $adminBaseUrl . "uploads/images/" . $row['image_url'] : 'assets/images/no-image.jpg' ?>" alt="<?= htmlspecialchars($row['package_name']) ?>" class="img-fluid">
                <div class="position-absolute top-0 end-0 p-2">
                    <span class="badge bg-primary">£<?= htmlspecialchars($row['price']) ?></span>
                </div>
            </div>
            <div class="package-card-body d-flex flex-column">
                <h5 class="package-title"><?= htmlspecialchars($row['package_name']) ?></h5>
                <div class="package-services mb-3">
                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                    <?= htmlspecialchars($row['service_name'] ?: 'No services listed.') ?>
                </div>
                <div class="d-flex mt-auto">
                    <a href="<?= $adminBaseUrl ?>editpackage/<?= $row['id'] ?>" class="btn btn-primary flex-grow-1 me-2">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <button class="btn btn-danger delete-package" data-id="<?= $row['id'] ?>">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<script>
    $(document).ready(function() {
        $('#bDataTable').DataTable();
    });

    function deleteCode($id) {
        swal({
            title: 'Are you sure?',
            text: "You want to delete this data",
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: "<?= $apiUrl ?>",
                    type: 'POST',
                    data: {
                        packageid: $id,
                        mode: 'deletepackage'
                    },
                    success: function(data) {
                        if (data.error.code == '#200') {
                            swal({
                                title: 'Success!',
                                icon: 'success',
                                text: "Bundles deleted successfully",
                                confirmButtonText: 'Ok'
                            }).then((result) => {
                                location.reload();
                            });
                        }
                    }
                });
            }
        });
    }
</script>