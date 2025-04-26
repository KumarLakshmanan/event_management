<?php


$sql = "SELECT
    b.*,    
    p.id AS package_id,
    p.package_name,
    p.price,
    p.description,
    GROUP_CONCAT(s.service_name ORDER BY s.service_name SEPARATOR ', ') AS service_name
FROM bookings b 
LEFT JOIN package p ON p.id = b.package_id
LEFT JOIN package_services ps ON p.id = ps.package_id
LEFT JOIN service s ON ps.service_id = s.id
where b.user_id = :user_id
GROUP BY b.id
ORDER BY b.id DESC;
";
$stmt = $pdoConn->prepare($sql);
$stmt->execute(['user_id' => $_SESSION['id']]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-transparent">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Bookings</h5>
                    <a href="<?= $adminBaseUrl ?>explore_bundles" class="btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i> Book New Event
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
                    <div class="stat-card-title">TOTAL BOOKINGS</div>
                    <div class="stat-card-value"><?= count($result) ?></div>
                </div>
                <div class="stat-card-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>
            </div>
        </div>
    </div>
    <?php 
    $pendingCount = 0;
    $confirmedCount = 0;
    $completedCount = 0;
    
    foreach ($result as $booking) {
        $status = strtolower($booking['status']);
        if ($status === 'pending') $pendingCount++;
        if ($status === 'confirmed') $confirmedCount++;
        if ($status === 'completed') $completedCount++;
    }
    ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-card-title">PENDING</div>
                    <div class="stat-card-value"><?= $pendingCount ?></div>
                </div>
                <div class="stat-card-icon">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-card-title">CONFIRMED</div>
                    <div class="stat-card-value"><?= $confirmedCount ?></div>
                </div>
                <div class="stat-card-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-card-title">COMPLETED</div>
                    <div class="stat-card-value"><?= $completedCount ?></div>
                </div>
                <div class="stat-card-icon">
                    <i class="bi bi-trophy"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bookings List -->
<div class="row g-4">
    <?php foreach ($result as $key => $value): ?>
    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
        <div class="package-card h-100">
            <div class="position-relative">
                <div class="position-absolute top-0 end-0 p-2">
                    <span class="badge bg-primary">£<?= htmlspecialchars($value['price']) ?></span>
                </div>
                <?php if ($value['status'] == 0) { ?>
                    <div class="position-absolute top-0 start-0 p-2">
                        <span class="badge bg-warning">Pending</span>
                    </div>
                <?php } else if ($value['status'] == 1) { ?>
                    <div class="position-absolute top-0 start-0 p-2">
                        <span class="badge bg-success">Confirmed</span>
                    </div>
                <?php } else if ($value['status'] == 2) { ?>
                    <div class="position-absolute top-0 start-0 p-2">
                        <span class="badge bg-danger">Cancelled</span>
                    </div>
                <?php } ?>
            </div>
            <div class="package-card-body d-flex flex-column">
                <h5 class="package-title"><?= htmlspecialchars($value['package_name']) ?></h5>
                <div class="mb-2">
                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                    <small><?= htmlspecialchars($value['service_name'] ?: 'No services listed.') ?></small>
                </div>
                <div class="mb-2">
                    <i class="bi bi-calendar-event text-muted me-2"></i>
                    <span><?= date('d M Y', strtotime($value['event_date'])) ?></span>
                </div>
                <div class="mb-3">
                    <i class="bi bi-geo-alt text-muted me-2"></i>
                    <span><?= htmlspecialchars($value['event_place']) ?></span>
                </div>
                <div class="d-flex mt-auto">
                    <a href="<?= $adminBaseUrl ?>editbooking?bookingid=<?= $value['id'] ?>" class="btn btn-primary flex-grow-1 me-2">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <button class="btn btn-danger" onclick="deleteCode('<?= $value['id'] ?>')">
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
                        bookingid: $id,
                        mode: 'deletebooking'
                    },
                    success: function(data) {
                        if (data.error.code == '#200') {
                            swal({
                                title: 'Success!',
                                icon: 'success',
                                text: "Booking deleted successfully",
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