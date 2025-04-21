<h2>Send Notifications to users</h2>
<br />
<div class="white-box">
    <div class="d-md-flex mb-3">
        <h3 class="box-title mb-0">Send Notifications to users</h3>
    </div>
    <br />
    <div class="row">
        <div class="col-md-6">
            <h6>Title</h6>
            <div class="p-2">
                <input type="text" class="form-control" id="title" placeholder="Title" />
            </div>
        </div>
        <div class="col-md-6">
            <h6>Description</h6>
            <div class="p-2">
                <input class="form-control" type="text" id="message" placeholder="Description" />
            </div>
        </div>
    </div>
    <div class="p-2">
        <button type="button" class="w-100 btn btn-primary saveButton">Send</button>
    </div>
</div>

<script>
    $(".saveButton").click(function() {
        var title = $("#title").val();
        var message = $("#message").val();
        swal({
            title: 'Are you sure to send Notification?',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                var formData = new FormData();
                formData.append("mode", "sendNotification");
                formData.append("title", title);
                formData.append("message", message);
                $(".preloader").show();
                $.ajax({
                    url: "<?php echo $apiUrl  ?>",
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
                                text: "Your Notification has been sent successfully!",
                                confirmButtonText: 'Ok'
                            }).then((result) => {
                                window.location.reload();
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