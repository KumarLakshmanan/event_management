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
                <div class="form-group col-md-12">
                    <div class="p-2">
                        <label for="images">Feature Name</label>
                        <textarea class="form-control" id="featureNames"><?= getKeyValue('feature_2_names', $settings) ?></textarea>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <div class="p-2">
                        <label for="images">Feature Description</label>
                        <textarea class="form-control" id="featureDescriptions"><?= getKeyValue('feature_2_descriptions', $settings) ?></textarea>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <div class="p-2">
                        <label for="images">Feature Images</label>
                        <div class="input-images"></div>
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
    $(document).ready(() => {
        imageUploader.init(".input-images");
    })
    let _xUserData = {
        "baseURL": "<?= $baseUrl ?>",
        "auth": "<?= $_SESSION['token'] ?>",
        ".input-images": "<?= getKeyValue('feature_2_images', $settings) ?>",
    };

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
                formData.append("feature_2_names", $("#featureNames").val());
                formData.append("feature_2_descriptions", $("#featureDescriptions").val());
                formData.append("feature_2_images", images);
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