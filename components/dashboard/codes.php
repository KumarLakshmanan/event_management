<?php



$categoryId = $_REQUEST['categoryid'] ?? "";
$collectionId = $_REQUEST['collectionid'] ?? "";
$result = [];
if ($categoryId != "" && $collectionId != "") {
    $sql = "SELECT * FROM items WHERE category_id = :categoryid AND collection_id = :collectionid ORDER BY id DESC";
    $stmt = $pdoConn->prepare($sql);
    $stmt->execute(['categoryid' => $categoryId, 'collectionid' => $collectionId]);
    $result = $stmt->fetchAll();
}
?>
<div class="row">
    <div class="col-md-12 col-lg-12 col-sm-12">
        <div class="white-box">
            <div class="d-md-flex mb-3">
                <h3 class="box-title mb-0">All Codes</h3>
            </div>
            <div class="row">

                <div class="col-md-6">
                    <select class="form-select" id="category" aria-label="Default select example">
                        <option value="0">Select Category</option>
                        <?php
                        $sql = "SELECT id,title FROM categories";
                        $stmt = $pdoConn->prepare($sql);
                        $stmt->execute();
                        $categories = $stmt->fetchAll();
                        foreach ($categories as $category) {
                        ?>
                            <option value="<?= $category['id'] ?>" <?= $categoryId == $category['id'] ? "selected" : "" ?>><?= htmlspecialchars($category['title']) ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
                <?php
                if ($categoryId != "") {
                ?>
                    <div class="col-md-6">
                        <select class="form-select" id="collection" aria-label="Default select example">
                            <option value="0">Select Collection</option>
                            <?php
                            $sql = "SELECT id,title FROM collections WHERE category_id = :categoryid";
                            $stmt = $pdoConn->prepare($sql);
                            $stmt->execute(['categoryid' => $categoryId]);
                            $collections = $stmt->fetchAll();
                            foreach ($collections as $collection) {
                            ?>
                                <option value="<?= $collection['id'] ?>" <?= $collectionId == $collection['id'] ? "selected" : "" ?>><?= htmlspecialchars($collection['title']) ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                <?php
                }
                ?>
            </div>
            <div class="p-3"></div>
            <div class="table-responsive">
                <table class="table no-wrap bDataTable" id="bDataTable">
                    <thead>
                        <tr>
                            <th class="border-top-0">#</th>
                            <th class="border-top-0">ID</th>
                            <th class="border-top-0">Name</th>
                            <th class="border-top-0">Author</th>
                            <th class="border-top-0">Status</th>
                            <th class="border-top-0">Created At</th>
                            <th class="border-top-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($result as $key => $value) {
                            echo '<tr>';
                            echo "<td>" . ($key + 1) . "</td>";
                            echo "<td>" . $value['id'] . "</td>";
                            echo "<td><img src='uploads/images/" . $value['image'] . "' width='100' height='50' class='img-circle' /> &nbsp; " . htmlspecialchars($value['title']) . "</td>";
                            echo "<td>" . $value['author'] . "</td>";
                            echo $value['status'] == 'public' ? '<td><span class="badge bg-success">Public</span></td>' : '<td><span class="badge bg-danger">' . $value['status'] . '</span></td>';
                            echo "<td>" . $value['created_at'] . "</td>";
                            echo '<td>
                                    <a href="' . $adminBaseUrl . 'editcode?codeid=' . $value['id'] . '" class="btn btn-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75zM21.41 6.34l-3.75-3.75l-2.53 2.54l3.75 3.75z"/></svg>
                                    </a>
                                    <a href="'. $value["codepenlink"] .'" class="btn btn-info" target="_blank">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="#fff" d="M11 17H7q-2.075 0-3.537-1.463T2 12q0-2.075 1.463-3.537T7 7h4v2H7q-1.25 0-2.125.875T4 12q0 1.25.875 2.125T7 15h4zm-3-4v-2h8v2zm5 4v-2h4q1.25 0 2.125-.875T20 12q0-1.25-.875-2.125T17 9h-4V7h4q2.075 0 3.538 1.463T22 12q0 2.075-1.463 3.538T17 17z"/></svg>
                                    </a>
                                </td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>0
    $(document).ready(function() {
        $('#bDataTable').DataTable();
    });
    $("#category").change(function() {
        var categoryid = $(this).val();
        window.location.href = "<?= $adminBaseUrl ?>codes?categoryid=" + categoryid;
    });
    if ($("#collection").length) {
        $("#collection").change(function() {
            var categoryid = $("#category").val();
            var collectionid = $(this).val();
            window.location.href = "<?= $adminBaseUrl ?>codes?categoryid=" + categoryid + "&collectionid=" + collectionid;
        });
    }
</script>