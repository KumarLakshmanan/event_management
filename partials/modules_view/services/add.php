<div class="container">
    <div class="white-box">
        <h4>  Add Service</h4>
        <br />
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="serviceName">Service Name</label>
                    <input type="text" class="form-control" id="serviceName" placeholder="Enter Service Name">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="price">Price (£)</label>
                    <input type="text" class="form-control" id="price" placeholder="Enter Price">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="description">Description</label>
                    <input type="text" class="form-control" id="description" placeholder="Enter Description">
                </div>
            </div>
            
        </div>
        <div class="p-2">
            <button type="button" id="saveButton" class="w-100 btn btn-primary saveButton">Save changes</button>
        </div>
    </div>
</div>
<link href="<?= $adminBaseUrl ?>css/image-uploader.min.css" rel="stylesheet" />
<script src="<?= $adminBaseUrl ?>js/image-uploader.min.js"></script>
<script>
    let _xUserData = {
        "baseURL": "<?= $adminBaseUrl ?>",
        "auth": "<?= $_SESSION['token'] ?>",
    };
    if ($(".texteditor-content").length > 0) {
        $(".texteditor-content").richText();
    }
    $("#serviceName").on("input", function() {
        let serviceName = $(this).val();
    })
    $("#saveButton").click(function(event) {
        event.preventDefault();
        swal({
            title: 'Are you sure to save changes?',
            text: "Changes will be saved and deployed to the server!",
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                var formData = new FormData();
                formData.append("mode", "addservice");
                formData.append("service_name", $("#serviceName").val());
                formData.append("price", $("#price").val());
                formData.append("description", $("#description").val());
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