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

<div class="row">
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
</div>
<div class="row">
    <div class="col-md-12 col-lg-12 col-sm-12">
        <div class="white-box">
            <div class="d-md-flex mb-3">
                <h3 class="box-title mb-0">All Package</h3>
            </div>
            <div class="table-responsive">
                <table class="table no-wrap bDataTable" id="bDataTable">
                    <thead>
                        <tr>
                            <th class="border-top-0">#</th>
                            <th class="border-top-0">Package</th>
                            <th class="border-top-0">Price</th>
                            <th class="border-top-0">Service</th>
                            <th class="border-top-0">Image</th>
                            <th class="border-top-0">Description</th>
                            <th class="border-top-0">Date</th>
                            <th class="border-top-0">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($result as $key => $value) {
                        ?>
                            <tr>
                                <td><?php echo $key + 1; ?></td>
                                <td>
                                    <?php echo $value['package_name']; ?>
                                </td>
                                <td>
                                    <?php echo $value['price']; ?>
                                </td>                                
                                <td>
                                    <?php echo $value['service_name']; ?>
                                </td>
                                <td>
                                    <?php if ($value['image_url'] != '') { ?>
                                        <img src="<?= $baseUrl ?>uploads/images/<?= $value['image_url'] ?>" alt="Image" width="50" height="50">
                                    <?php } else { ?>
                                        -
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php echo $value['description']; ?>
                                </td>
                                <td>
                                    <?php echo date('d M h:i A', strtotime($value['created_at'])); ?>
                                </td>
                                <td>
                                    <a href="<?= $adminBaseUrl ?>editpackage?packageid=<?= $value['id'] ?>" class="btn btn-info">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="#" onclick="deleteCode('<?= $value['id'] ?>')" class="btn btn-danger">
                                        <i class="fa fa-trash"></i>
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
                                text: "Package deleted successfully",
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