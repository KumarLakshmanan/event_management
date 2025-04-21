<?php
if (!isset($conn)) {
    $path = $_SERVER['DOCUMENT_ROOT'];
    include_once($baseDirectory . "/admin/api/config.php");
    $db = new Connection();
    $conn = $db->getConnection();
}

$sql = "SELECT * FROM users where type=0 ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-md-12 col-lg-12 col-sm-12">
        <div class="white-box">
            <div class="d-md-flex mb-3">
                <h3 class="box-title mb-0">All Clients</h3>
            </div>
            <div class="table-responsive">
                <table class="table no-wrap bDataTable" id="bDataTable">
                    <thead>
                        <tr>
                            <th class="border-top-0">#</th>
                            <th class="border-top-0">Username/Email</th>
                            <th class="border-top-0">Name</th>
                            <th class="border-top-0">Phone No</th>
                            <th class="border-top-0">Gender</th>
                            <th class="border-top-0">Address</th>
                            <th class="border-top-0">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($result as $key => $value) {
                        ?>
                            <tr>
                                <td><?php echo $key + 1; ?></td>
                                <td><?php echo $value["email"]; ?></td>
                                <td><?php echo $value["fullname"]; ?></td>
                                <td><?php echo $value["phone"] ?? ""; ?></td>
                                <td><?php echo $value["gender"] ?? ""; ?></td>
                                <td><?php echo $value["address"] ?? ""; ?></td>
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