<?php
$sql = "SELECT * FROM contactus ORDER BY id DESC";
$stmt = $pdoConn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-12 col-lg-12 col-sm-12">
        <div class="white-box">
            <div class="d-md-flex mb-3">
                <h3 class="box-title mb-0">All Contacts</h3>
            </div>
            <div class="table-responsive">
                <table class="table no-wrap bDataTable" id="bDataTable">
                    <thead>
                        <tr>
                            <th class="border-top-0">#</th>
                            <th class="border-top-0">Name</th>
                            <th class="border-top-0">Email</th>                         
                            <th class="border-top-0">Phone</th>
                            <th class="border-top-0">Message</th>
                            <th class="border-top-0">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($result as $key => $value) {
                        ?>
                            <tr>
                                <td><?php echo $key + 1; ?></td>
                                <td>
                                    <?php echo $value['name']; ?>
                                </td>
                                <td>
                                    <?php echo $value['email']; ?>
                                </td>
                                <td>
                                    <?php echo $value['phone']; ?>
                                </td>
                                <td>
                                    <?php echo $value['message']; ?>
                                </td>
                                <td>
                                    <?php echo date('d M h:i A', strtotime($value['created_at'])); ?>
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
                        clientid: $id,
                        mode: 'deleteclient'
                    },
                    success: function(data) {
                        if (data.error.code == '#200') {
                            swal({
                                title: 'Success!',
                                icon: 'success',
                                text: "Client deleted successfully",
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