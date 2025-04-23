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
GROUP BY b.id
ORDER BY b.id DESC;
";
$stmt = $pdoConn->prepare($sql);
// ['user_id' => $_SESSION['id']]
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- <div class="row">
    <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
        <div class="white-box">
            <div class="text-end">
                <a href="<?= $adminBaseUrl ?>addpackage" class="btn btn-success text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 32 32">
                        <path fill="currentColor" d="M17 15V8h-2v7H8v2h7v7h2v-7h7v-2z" />
                    </svg>
                    Add New Package
                </a>
            </div>
        </div>
    </div>
</div> -->
<div class="row">
    <div class="col-md-12 col-lg-12 col-sm-12">
        <div class="white-box">
            <div class="d-md-flex mb-3">
                <h3 class="box-title mb-0">All Booking</h3>
            </div>
            <div class="table-responsive">
                <table class="table no-wrap bDataTable" id="bDataTable">
                    <thead>
                        <tr>
                            <th class="border-top-0">#</th>
                            <th class="border-top-0">Package Name</th>
                            <th class="border-top-0">Type</th>
                            <th class="border-top-0">Services</th>
                            <th class="border-top-0">Event Date</th>
                            <th class="border-top-0">Event Place</th>
                            <th class="border-top-0">Price</th>
                            <th class="border-top-0">Status</th>
                            <th class="border-top-0">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($result as $index => $booking): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($booking['package_name']) ?></td>
                            <td><?= ucfirst($booking['package_type']) ?></td>
                            <td><?= htmlspecialchars($booking['service_name']) ?></td>
                            <td><?= htmlspecialchars($booking['event_date']) ?></td>
                            <td><?= htmlspecialchars($booking['event_place']) ?></td>
                            <td>£<?= number_format($booking['price'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $booking['status'] == 'pending' ? 'warning' : 'success' ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </td>
                            <td>
                                    <a href="<?= $adminBaseUrl ?>editallbooking?bookingid=<?= $booking['id'] ?>" class="btn btn-info">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="#" onclick="deleteCode('<?= $booking['id'] ?>')" class="btn btn-danger">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                </table>
            </div>
        </div>
    </div>
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