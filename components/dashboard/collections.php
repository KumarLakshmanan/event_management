<?php



$categoryId = $_REQUEST['categoryid'] ?? "";
$result = [];
if ($categoryId != "") {
	$sql = "SELECT * FROM collections WHERE category_id = :categoryid ORDER BY id DESC";
	$stmt = $pdoConn->prepare($sql);
	$stmt->execute(['categoryid' => $categoryId]);
	$result = $stmt->fetchAll();
}
?>
<div class="row">
	<div class="col-md-12 col-lg-12 col-sm-12">
		<div class="white-box">
			<div class="d-md-flex mb-3">
				<h3 class="box-title mb-0">All collections</h3>
			</div>
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
						<option value="<?= $category['id'] ?>" <?= $categoryId == $category['id'] ? "selected" : "" ?>>
							<?= $category['title'] ?>
						</option>
					<?php
					}
					?>
				</select>
			</div>
			<div class="p-3"></div>
			<div class="table-responsive">
				<table class="table no-wrap bDataTable" id="bDataTable">
					<thead>
						<tr>
							<th class="border-top-0">#</th>
							<th class="border-top-0">Collection Name</th>
							<th class="border-top-0">ID</th>
							<th class="border-top-0">Items Count</th>
							<th class="border-top-0">Created At</th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($result as $key => $value) {
							echo '<tr>';
							echo "<td>" . ($key + 1) . "</td>";
							echo "<td><img src='uploads/images/" . $value['image'] . "' width='100' height='50' class='img-circle' />" . htmlspecialchars($value['title']) . "</td>";
							echo "<td>" . $value['id'] . "</td>";
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
	$("#category").change(function() {
		var categoryid = $(this).val();
		window.location.href = "<?= $adminBaseUrl ?>collections?categoryid=" + categoryid;
	});
</script>
