<?php
if (!isset($conn)) {
    $path = $_SERVER['DOCUMENT_ROOT'];
    include_once($baseDirectory . "/admin/api/config.php");
    $db = new Connection();
    $conn = $db->getConnection();
}

$sql = "SELECT * FROM booking_forms ORDER BY id DESC";
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
                            <th class="border-top-0">Name</th>
                            <th class="border-top-0">Phone No</th>
                            <th class="border-top-0">Date</th>
                            <th class="border-top-0">Status</th>
                            <th class="border-top-0">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($result as $key => $value) {
                        ?>
                            <tr>
                                <td><?php echo $key + 1; ?></td>
                                <td><?php echo $value["name"]; ?></td>
                                <td><?php echo $value["phone"] ?? ""; ?></td>
                                <td><?php echo date('d M h:i A', strtotime($value['created_at'])); ?></td>
                                <td>
                                    <?php
                                    if ($value["status"] == "unread") {
                                        echo '<span class="btn-sm btn btn-primary">unread</span>';
                                    } else {
                                        echo '<span class="btn-sm btn btn-success">readed</span>';
                                    }
                                    ?>
                                <td class="p-1">
                                    <a href="<?php echo $baseUrl ?>api/viewBooking.php?id=<?php echo $value['id']; ?>" class="btn btn-primary btnViewMessage">
                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="20px" height="20px" preserveAspectRatio="xMidYMid meet" viewBox="0 0 16 16">
                                            <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
                                                <path d="M1.75 8s2-4.25 6.25-4.25S14.25 8 14.25 8s-2 4.25-6.25 4.25S1.75 8 1.75 8z" />
                                                <circle cx="8" cy="8" r="1.25" fill="currentColor" />
                                            </g>
                                        </svg>
                                    </a>
                                </td>
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