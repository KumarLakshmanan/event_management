<?php
if (!isset($conn)) {
    $path = $_SERVER['DOCUMENT_ROOT'];
    include_once($baseDirectory . "/admin/api/config.php");
    $db = new Connection();
    $conn = $db->getConnection();
}

$sql = "SELECT 
    sb.id AS booking_id,
    sb.service_name,
    sb.reason,
    sb.created_at,
    sb.status,
     CASE 
        WHEN sb.status = 0 THEN 'Pending'
        WHEN sb.status = 1 THEN 'Accept'
        WHEN sb.status = 2 THEN 'Rejected'
        WHEN sb.status = 3 THEN 'Completed'
        ELSE 'Unknown'
    END AS status_text,
    u1.fullname AS user_name,
    u1.phone AS user_phone,
    u1.email AS user_email,
    u2.fullname AS service_man_name,
    u2.phone AS service_man_phone,
    u2.email AS service_man_email
FROM 
    service_booking sb
JOIN 
    users u1 ON sb.user_id = u1.id  -- Join to get user details
JOIN 
    users u2 ON sb.service_man_id = u2.id ORDER BY sb.id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-md-12 col-lg-12 col-sm-12">
        <div class="white-box">
            <div class="d-md-flex mb-3">
                <h3 class="box-title mb-0">All Booking Messages</h3>
            </div>
            <div class="table-responsive">
                <table class="table no-wrap bDataTable" id="bDataTable">
                    <thead>
                        <tr>
                            <th class="border-top-0">#</th>
                            <th class="border-top-0">Service</th>
                            <th class="border-top-0">Customer</th>
                            <th class="border-top-0">Customer Phone</th>
                            <th class="border-top-0">Customer Email</th>
                            <th class="border-top-0">Service Man</th>
                            <th class="border-top-0">Service Man Phone</th>
                            <th class="border-top-0">Service Man Email</th>
                            <th class="border-top-0">Status</th>
                            <th class="border-top-0">Reason</th>
                            <th class="border-top-0">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($result as $key => $value) {
                        ?>
                            <tr>
                                <td><?php echo $key + 1; ?></td>
                                <td><?php echo $value["service_name"]; ?></td>
                                <td><?php echo $value["user_name"]; ?></td>
                                <td><?php echo $value["user_phone"]; ?></td>
                                <td><?php echo $value["user_email"]; ?></td>
                                <td><?php echo $value["service_man_name"]; ?></td>
                                <td><?php echo $value["service_man_phone"]; ?></td>
                                <td><?php echo $value["service_man_email"]; ?></td>
                                <td><?php echo $value["status_text"]; ?></td>
                                <td><?php echo $value["reason"]; ?></td>
                                <td><?php echo date('d M h:i A', strtotime($value['created_at'])); ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- modal box -->
<div class="modal fade " id="modalViewMessage" tabindex="-1" role="dialog" aria-labelledby="modalViewMessage" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-fullscreen" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalViewMessage">Message</h5>
                <button type="btn btn-close" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

            </div>
        </div>
    </div>
</div>
<script>
    $(".btnViewMessage").click(function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr("href"),
            success: function(data) {
                $("#modalViewMessage .modal-body").html(data);
                $("#modalViewMessage").modal("show");
            }
        });
    });
    $(document).ready(function() {
        $('#bDataTable').DataTable();
    });
</script>