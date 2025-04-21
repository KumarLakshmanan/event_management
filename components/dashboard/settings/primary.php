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
                    <label>App Name </label>
                    <input type="text" name="app_name" class="form-control" value="<?= getKeyValue('app_name', $settings) ?>">
                </div>
            </div>
            <!-- phone number -->
            <div class="row">
                <div class="form-group col-md-6">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?= getKeyValue('phone', $settings) ?>">
                </div>
                <div class="form-group col-md-6">
                    <label>Email</label>
                    <input type="text" name="email" class="form-control" value="<?= getKeyValue('email', $settings) ?>">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" value="<?= getKeyValue('address', $settings) ?>">
                </div>
                <div class="form-group col-md-6">
                    <label>Full Address</label>
                    <input type="text" name="full_address" class="form-control" value="<?= getKeyValue('full_address', $settings) ?>">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6">
                    <label>Latitude</label>
                    <input type="text" name="latitude" class="form-control" value="<?= getKeyValue('latitude', $settings) ?>">
                </div>
                <div class="form-group col-md-6">
                    <label>Longitude</label>
                    <input type="text" name="longitude" class="form-control" value="<?= getKeyValue('longitude', $settings) ?>">
                </div>
            </div>
            <div class="row">
                <div class="form-group  col-md-6">
                    <label>Instagram Url</label>
                    <input type="text" name="instagram" class="form-control" value="<?= getKeyValue('instagram', $settings) ?>">
                </div>
                <div class="form-group  col-md-6">
                    <label>Facebook Url</label>
                    <input type="text" name="facebook" class="form-control" value="<?= getKeyValue('facebook', $settings) ?>">
                </div>
            </div>
            <div class="row">
                <div class="form-group  col-md-6">
                    <label>Youtube Url</label>
                    <input type="text" name="youtube" class="form-control" value="<?= getKeyValue('youtube', $settings) ?>">
                </div>
                <div class="form-group col-md-6">
                    <label>Whatsapp Number</label>
                    <input type="text" name="whatsapp" class="form-control" value="<?= getKeyValue('whatsapp', $settings) ?>">
                </div>
            </div>
            <div class="row">
                <div class="form-group  col-md-6">
                    <label>Short Description</label>
                    <input type="text" name="short_des" class="form-control" value="<?= getKeyValue('short_des', $settings) ?>">
                </div>
                <div class="form-group  col-md-6">
                    <label>Keywords </label>
                    <input type="text" name="keywords" class="form-control" value="<?= getKeyValue('keywords', $settings) ?>">
                </div>
            </div>
            <div class="row">
                <div class="form-group  col-md-6">
                    <label>Opening Timing</label>
                    <input type="text" name="opening_timing" class="form-control" value="<?= getKeyValue('opening_timing', $settings) ?>">
                </div>
            </div>
            <div class="form-group ">
                <label>Long Description </label>
                <textarea type="text" name="long_des" class="form-control"><?= getKeyValue('long_des', $settings) ?></textarea>
            </div>

            <div class="row">
                <div class="form-group col-md-4">
                    <button id="saveButton" class="btn btn-success" value="">SAVE</button>
                </div>
            </div>
        </form>
    </div>
</div>

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
                var formData = new FormData();
                formData.append("mode", "primarysettings");
                formData.append("app_name", $("input[name='app_name']").val());
                formData.append("phone", $("input[name='phone']").val());
                formData.append("email", $("input[name='email']").val());
                formData.append("address", $("input[name='address']").val());
                formData.append("full_address", $("input[name='full_address']").val());
                formData.append("city", $("input[name='city']").val());
                formData.append("latitude", $("input[name='latitude']").val());
                formData.append("longitude", $("input[name='longitude']").val());
                formData.append("instagram", $("input[name='instagram']").val());
                formData.append("facebook", $("input[name='facebook']").val());
                formData.append("linkedin", $("input[name='linkedin']").val());
                formData.append("youtube", $("input[name='youtube']").val());
                formData.append("whatsapp", $("input[name='whatsapp']").val());
                formData.append("short_des", $("input[name='short_des']").val());
                formData.append("keywords", $("input[name='keywords']").val());
                formData.append("long_des", $("textarea[name='long_des']").val());
                formData.append("opening_timing", $("input[name='opening_timing']").val());
                $(".preloader").show();
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
                                text: "Your Course has been saved successfully!",
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

<style>
    #keywordsInput {
        width: 100%;
        float: left;
    }

    #keywordsInput>input {
        padding: 7px;
        width: calc(100%);
    }
</style>