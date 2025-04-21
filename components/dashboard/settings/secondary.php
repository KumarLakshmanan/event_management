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
                        <label for="images">Appbar Logo</label>
                        <div class="input-images-1" id="images" style="padding-top: .5rem;"></div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <div class="p-2">
                        <label for="images">Sidebar Logo</label>
                        <div class="input-images-2" id="images" style="padding-top: .5rem;"></div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <div class="p-2">
                        <label for="images">Footer Logo</label>
                        <div class="input-images-3" id="images" style="padding-top: .5rem;"></div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <div class="p-2">
                        <label for="images">Loader Logo</label>
                        <div class="input-images-4" id="images" style="padding-top: .5rem;"></div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <div class="p-2">
                        <label for="images">Chatbot Logo</label>
                        <div class="input-images-5" id="images" style="padding-top: .5rem;"></div>
                    </div>
                </div>
                <!-- about header image -->
                <div class="form-group col-md-6">
                    <div class="p-2">
                        <label for="images">About Header Image</label>
                        <div class="input-images-6" id="images" style="padding-top: .5rem;"></div>
                    </div>
                </div>
                <!-- gallery -->
                <div class="form-group col-md-6">
                    <div class="p-2">
                        <label for="images">Gallery Page</label>
                        <div class="input-images-7" id="images" style="padding-top: .5rem;"></div>
                    </div>
                </div>
                <!-- faq -->
                <div class="form-group col-md-6">
                    <div class="p-2">
                        <label for="images">FAQ Page</label>
                        <div class="input-images-8" id="images" style="padding-top: .5rem;"></div>
                    </div>
                </div>
                <!-- contact -->
                <div class="form-group col-md-6">
                    <div class="p-2">
                        <label for="images">Contact Page</label>
                        <div class="input-images-9" id="images" style="padding-top: .5rem;"></div>
                    </div>
                </div>
                <!-- blog -->
                <div class="form-group col-md-6">
                    <div class="p-2">
                        <label for="images">Blog Page</label>
                        <div class="input-images-10" id="images" style="padding-top: .5rem;"></div>
                    </div>
                </div>
                <!-- booking -->
                <div class="form-group col-md-6">
                    <div class="p-2">
                        <label for="images">Booking Page</label>
                        <div class="input-images-11" id="images" style="padding-top: .5rem;"></div>
                    </div>
                </div>
                <!-- booking -->
                <div class="form-group col-md-6">
                    <div class="p-2">
                        <label for="images">Sharing Thumbnail</label>
                        <div class="input-images-12" id="images" style="padding-top: .5rem;"></div>
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
        imageUploader.init(".input-images-1");
        imageUploader.init(".input-images-2");
        imageUploader.init(".input-images-3");
        imageUploader.init(".input-images-4");
        imageUploader.init(".input-images-5");
        imageUploader.init(".input-images-6");
        imageUploader.init(".input-images-7");
        imageUploader.init(".input-images-8");
        imageUploader.init(".input-images-9");
        imageUploader.init(".input-images-10");
        imageUploader.init(".input-images-11");
        imageUploader.init(".input-images-12");
    })
    let _xUserData = {
        "baseURL": "<?= $baseUrl ?>",
        "auth": "<?= $_SESSION['token'] ?>",
        ".input-images-1": "<?= getKeyValue('appbar_logo', $settings) ?>",
        ".input-images-2": "<?= getKeyValue('sidebar_logo', $settings) ?>",
        ".input-images-3": "<?= getKeyValue('footer_logo', $settings) ?>",
        ".input-images-4": "<?= getKeyValue('loader_logo', $settings) ?>",
        ".input-images-5": "<?= getKeyValue('chatbot_logo', $settings) ?>",
        ".input-images-6": "<?= getKeyValue('about_header_image', $settings) ?>",
        ".input-images-7": "<?= getKeyValue('gallery_header_image', $settings) ?>",
        ".input-images-8": "<?= getKeyValue('faq_header_image', $settings) ?>",
        ".input-images-9": "<?= getKeyValue('contact_header_image', $settings) ?>",
        ".input-images-10": "<?= getKeyValue('blog_header_image', $settings) ?>",
        ".input-images-11": "<?= getKeyValue('booking_header_image', $settings) ?>",
        ".input-images-12": "<?= getKeyValue('sharing_thumbnail', $settings) ?>",
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
                var formData = new FormData();
                formData.append("mode", "primarysettings");
                formData.append('appbar_logo', $(".input-images-1 .uploaded-image").attr('data-name'));
                formData.append('sidebar_logo', $(".input-images-2 .uploaded-image").attr('data-name'));
                formData.append('footer_logo', $(".input-images-3 .uploaded-image").attr('data-name'));
                formData.append('loader_logo', $(".input-images-4 .uploaded-image").attr('data-name'));
                formData.append('chatbot_logo', $(".input-images-5 .uploaded-image").attr('data-name'));
                formData.append('about_header_image', $(".input-images-6 .uploaded-image").attr('data-name'));
                formData.append('gallery_header_image', $(".input-images-7 .uploaded-image").attr('data-name'));
                formData.append('faq_header_image', $(".input-images-8 .uploaded-image").attr('data-name'));
                formData.append('contact_header_image', $(".input-images-9 .uploaded-image").attr('data-name'));
                formData.append('blog_header_image', $(".input-images-10 .uploaded-image").attr('data-name'));
                formData.append('booking_header_image', $(".input-images-11 .uploaded-image").attr('data-name'));
                formData.append('sharing_thumbnail', $(".input-images-12 .uploaded-image").attr('data-name'));
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