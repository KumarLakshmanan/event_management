<?php
if (!isset($_SESSION)) {
    session_start();
}
$today = date('Y-m-d');
$sql = "SELECT * FROM `settings`";
$result = $pdoConn->query($sql);
$settings = $result->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="p-3"></div>


<div class="container">
    <div class="white-box">
        <form action="" method="POST" id="shippingchargeFrom">
            <div class="row">
                <div class="form-group col-md-6">
                    <div class="p-2">
                        <label for="images">Welcome Message</label>
                        <input type="text" class="form-control" id="welcomeMessage" value="<?= getKeyValue('welcome_message', $settings) ?>" placeholder="Welcome Message">
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <div class="p-2">
                        <label for="images">Collect Phone</label>
                        <input type="text" class="form-control" id="collectPhone" value="<?= getKeyValue('collect_phone', $settings) ?>" placeholder="Collect Phone">
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <div class="p-2">
                        <label for="images">Collect Email</label>
                        <input type="text" class="form-control" id="collectEmail" value="<?= getKeyValue('collect_email', $settings) ?>" placeholder="Collect Email">
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <div class="p-2">
                        <label for="images">Collect Name</label>
                        <input type="text" class="form-control" id="collectName" value="<?= getKeyValue('collect_name', $settings) ?>" placeholder="Collect Name">
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <div class="p-2">
                        <label for="images">No Saved Reply Message</label>
                        <input type="text" class="form-control" id="noReply" value="<?= getKeyValue('no_saved_reply', $settings) ?>" placeholder="No Saved Reply Message">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-4">
                    <button id="saveButton" class="btn btn-success" value="">SAVE</button>
                </div>
            </div>
        </form>
    </div>
</div>

<link href="<?= $adminBaseUrl ?>css/image-uploader.min.css" rel="stylesheet" />
<script src="<?= $adminBaseUrl ?>js/image-uploader.min.js"></script>
<script>
    $("#saveButton").click(function(event) {
        event.preventDefault();
        swal({
            title: 'Are you sure to save changes?',
            text: "This will be saved and pushed to the server!",
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                var images = "";
                $(".input-images .uploaded-image").each(function() {
                    images += $(this).attr('data-name') + ",";
                });
                var formData = new FormData();
                formData.append("mode", "primarysettings");
                formData.append("welcome_message", $("#welcomeMessage").val());
                formData.append("collect_phone", $("#collectPhone").val());
                formData.append("collect_email", $("#collectEmail").val());
                formData.append("collect_name", $("#collectName").val());
                formData.append("no_saved_reply", $("#noReply").val());
                $.ajax({
                    url: "<?= $apiUrl ?>",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(response) {
                        $(".preloader").hide();
                        if (response.error.code == '#200') {
                            swal({
                                title: 'Success!',
                                icon: 'success',
                                text: "Successfully Updated!",
                                confirmButtonText: 'Ok'
                            }).then((result) => {
                                if (result.value) {
                                    window.location.reload();
                                }
                            });
                        } else {
                            swal({
                                title: 'Error!',
                                text: response.error.description,
                                icon: 'error',
                                confirmButtonText: 'Try Again'
                            })
                        }
                    }
                });
            }
        });
    });
</script>