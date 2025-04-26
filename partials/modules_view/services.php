<?php

$sql = "SELECT * FROM service ORDER BY id DESC";
$stmt = $pdoConn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-transparent">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manage Services</h5>
                    <a href="<?= $adminBaseUrl ?>addservice" class="btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i> Add New Service
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-card-title">TOTAL SERVICES</div>
                    <div class="stat-card-value"><?= count($result) ?></div>
                </div>
                <div class="stat-card-icon">
                    <i class="bi bi-gear"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Services List -->
<div class="row g-4">
    <?php $i = 1; foreach ($result as $row): ?>
    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
        <div class="package-card h-100">
            <div class="package-card-body d-flex flex-column">
                <h5 class="package-title"><?= htmlspecialchars($row['service_name']) ?></h5>
                <p class="text-muted mb-3"><?= htmlspecialchars($row['description'] ?? 'No description available.') ?></p>
                <div class="d-flex mt-auto">
                    <a href="<?= $adminBaseUrl ?>editservice?serviceid=<?= $row['id'] ?>" class="btn btn-primary flex-grow-1 me-2">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <button class="btn btn-danger delete-service" data-id="<?= $row['id'] ?>">
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
    $('.addLink').click(function() {
        var linkurl = $('#announcementurl').val();
        var linkname = $('#announcementname').val();
        var linkdescription = $('#announcementdescription').val();
        if (linkname == '' || linkdescription == '') {
            alert('Please fill all fields');
            return false;
        }
        var formdata = new FormData();
        formdata.append('mode', 'addannouncement');
        formdata.append('name', linkname);
        formdata.append('url', linkurl);
        formdata.append('description', linkdescription);
        formdata.append('pdf', $('#announcementpdf')[0].files[0]);
        $.ajax({
            url: "<?= $apiUrl ?>",
            cache: false,
            beforeSend: function(xhr) {
                xhr.setRequestHeader("Cache-Control", "no-cache");
                xhr.setRequestHeader("pragma", "no-cache");
            },
            dataType: "json",
            processData: false,
            contentType: false,
            type: 'POST',
            data: formdata,
            success: function(data) {
                if (data.error.code == '#200') {
                    swal({
                        title: 'Success!',
                        icon: 'success',
                        text: "Announcement added successfully",
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        location.reload();
                    });
                }
            }
        });
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
                        serviceid: $id,
                        mode: 'deleteservice'
                    },
                    success: function(data) {
                        if (data.error.code == '#200') {
                            swal({
                                title: 'Success!',
                                icon: 'success',
                                text: "Service deleted successfully",
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