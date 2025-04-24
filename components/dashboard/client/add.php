<div class="container">
    <div class="white-box">
        <h4>Fill the Following Form To Add Client</h4>
        <br />
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="fullname">Client Name</label>
                    <input type="text" class="form-control" id="fullname" placeholder="Enter Client Name">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" class="form-control" id="email" placeholder="Enter Email">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="text" class="form-control" id="password" placeholder="Enter Password">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" class="form-control" id="phone" placeholder="Enter Phone">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" class="form-control" id="address" placeholder="Enter Address">
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
    $("#fullname").on("input", function() {
        let fullname = $(this).val();
    })
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
                formData.append("mode", "addclient");
                formData.append("fullname", $("#fullname").val());
                formData.append("email", $("#email").val());
                formData.append("password", $("#password").val());
                formData.append("phone", $("#phone").val());
                formData.append("address", $("#address").val());
                formData.append("role", "client");
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