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
                        <label for="images">FAQ Questions</label>
                        <textarea class="form-control" id="bannerTitles"><?= getKeyValue('faq_questions', $settings) ?></textarea>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <div class="p-2">
                        <label for="images">FAQ Answers</label>
                        <textarea class="form-control" id="bannerSubtitle"><?= getKeyValue('faq_answers', $settings) ?></textarea>
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
                formData.append("faq_questions", $("#bannerTitles").val());
                formData.append("faq_answers", $("#bannerSubtitle").val());
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