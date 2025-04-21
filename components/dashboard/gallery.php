<?php

$sql = "SELECT * FROM announcements ORDER BY id DESC";
$stmt = $pdoConn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
        <div class="white-box">
            <div class="text-end">
                <a href="<?= $adminBaseUrl ?>addannouncement" class="btn btn-success text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 32 32">
                        <path fill="currentColor" d="M17 15V8h-2v7H8v2h7v7h2v-7h7v-2z" />
                    </svg>
                    Add New FAQ
                </a>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-lg-12 col-sm-12">
        <div class="white-box">
            <div class="d-md-flex mb-3">
                <h3 class="box-title mb-0">All FAQ</h3>
            </div>
            <div class="table-responsive">
                <table class="table no-wrap bDataTable" id="bDataTable">
                    <thead>
                        <tr>
                            <th class="border-top-0">#</th>
                            <th class="border-top-0">Title</th>
                            <th class="border-top-0">Link</th>
                            <th class="border-top-0">Pdf</th>
                            <th class="border-top-0">Images</th>
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
                                    <?php
                                    echo $value['name'];
                                    ?>
                                </td>
                                <td>
                                    <a href="<?php echo $value['url']; ?>" target="_blank">
                                        <?php echo $value['url']; ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?php echo $baseUrl . "uploads/pdf/" . $value['pdf']; ?>" target="_blank">
                                        <?php echo $value['pdf']; ?>
                                    </a>
                                </td>
                                <td>
                                    <?php
                                    $images = explode(',', $value['image']);
                                    foreach ($images as $key => $image) {
                                        if ($image != '') {
                                    ?>
                                            <a href="<?php echo $baseUrl . "uploads/images/" . $image; ?>" target="_blank">
                                                <?php echo "Image " . ($key + 1); ?>
                                            </a>
                                    <?php
                                        }
                                    }
                                    ?>
                                <td><?php echo date('d M h:i A', strtotime($value['created_date'])); ?></td>
                                <td>
                                    <a href="<?= $adminBaseUrl ?>editannouncement?announcementid=<?= $value['id'] ?>" class="btn btn-info">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="#" onclick="if(confirm('Are you sure to delete ?')){deleteCode('<?= $value['id'] ?>')}" class="btn btn-danger">
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
        $.ajax({
            url: "<?= $apiUrl ?>",
            type: 'POST',
            data: {
                announcementid: $id,
                mode: 'deleteannouncement'
            },
            success: function(data) {
                if (data.error.code == '#200') {
                    swal({
                        title: 'Success!',
                        icon: 'success',
                        text: "Announcement deleted successfully",
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        location.reload();
                    });
                }
            }
        });
    }
</script>