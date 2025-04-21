<?php


$sql = "SELECT * FROM categories ORDER BY id DESC";
$stmt = $pdoConn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll();
?>
<div class="row">
    <div class="col-md-12 col-lg-12 col-sm-12">
        <div class="white-box">
            <div class="d-md-flex mb-3">
                <h3 class="box-title mb-0">All Categories</h3>
            </div>
            <div class="p-3"></div>
            <div class="table-responsive">
                <table class="table no-wrap bDataTable" id="bDataTable">
                    <thead>
                        <tr>
                            <th class="border-top-0">#</th>
                            <th class="border-top-0">Category Name</th>
                            <th class="border-top-0">Category ID</th>
                            <th class="border-top-0">Collections</th>
                            <th class="border-top-0">Items</th>
                            <th class="border-top-0">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($result as $key => $value) {
                            echo '<tr>';
                            echo "<td>" . ($key + 1) . "</td>";
                            echo "<td><img src='uploads/images/" . $value['image'] . "' width='100' height='50' class='img-circle' /> &nbsp; " . htmlspecialchars($value['title']) . "</td>";
                            echo "<td>" . $value['id'] . "</td>";
                            echo "<td>" . $value['collections'] . "</td>";
                            echo "<td>" . $value['items'] . "</td>";
                            echo "<td>" . $value['created_at'] . "</td>";
                            echo '</tr>';
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
</script>