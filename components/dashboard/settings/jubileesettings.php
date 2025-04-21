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
                    <label>Old Title</label>
                    <input type="text" name="old_title" class="form-control" value="<?= getKeyValue('jubliee_old_title', $settings) ?>">
                </div>
                <div class="form-group col-md-6">
                    <label>New Title</label>
                    <input type="text" name="new_title" class="form-control" value="<?= getKeyValue('jubliee_new_title', $settings) ?>">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6">
                    <label>Old Description</label>
                    <textarea type="text" name="old_des" class="form-control" rows="5"><?= getKeyValue('jubliee_old_des', $settings) ?></textarea>
                </div>
                <div class="form-group col-md-6">
                    <label>New Description</label>
                    <textarea type="text" name="new_des" class="form-control" rows="5"><?= getKeyValue('jubliee_new_des', $settings) ?></textarea>
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
                formData.append("jubliee_old_title", $("input[name=old_title]").val());
                formData.append("jubliee_new_title", $("input[name=new_title]").val());
                formData.append("jubliee_old_des", $("textarea[name=old_des]").val());
                formData.append("jubliee_new_des", $("textarea[name=new_des]").val());
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