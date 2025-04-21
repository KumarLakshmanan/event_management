<div class="container">
    <div class="white-box">
        <h4>Create Custom Package</h4>
        <br />
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="packageName">Package Name</label>
                    <input type="text" class="form-control" id="packageName" placeholder="Enter Package Name">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="event_date">Event Date</label>
                    <input type="date" class="form-control" id="event_date" placeholder="Enter Event Date">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="event_place">Event Place</label>
                    <input type="text" class="form-control" id="event_place" placeholder="Enter Event Place">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="serviceType">Service</label>
                    <?php
                        $sql = "SELECT * FROM service";
                        $stmt = $pdoConn->prepare($sql);
                        $stmt->execute();
                        $service_list = $stmt->fetchAll(PDO::FETCH_ASSOC); 
                    ?>
                    <select class="form-control" id="serviceType" multiple>
                        <?php foreach ($service_list as $service): ?>
                            <option value="<?= htmlspecialchars($service['id']) ?>">
                                <?= htmlspecialchars($service['service_name']) ?> - <?= htmlspecialchars($service['price']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="text" class="form-control" id="price" readonly placeholder="Enter Price">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="authorImage">Image</label>
                    <div class="input-images-1" id="images" style="padding-top: .5rem;"></div>
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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let _xUserData = {
        "baseURL": "<?= $baseUrl ?>",
        "auth": "<?= $_SESSION['token'] ?>",
    };
    $('#serviceType').select2({
        placeholder: "Select Service"
    });
    $(document).ready(() => {
        imageUploader.init(".input-images-1");
        imageUploader.init(".input-images-2");
    })
    if ($(".texteditor-content").length > 0) {
        $(".texteditor-content").richText();
    }
    $("#packageName").on("input", function() {
        let packageName = $(this).val();
    })

    const servicePrices = {
        <?php foreach ($service_list as $service): ?>
            <?= $service['id'] ?>: <?= $service['price'] ?>,
        <?php endforeach; ?>
    };

    $('#serviceType').on('change', function () {
        let selectedServices = $(this).val(); // array of selected IDs
        let totalPrice = 0;

        if (selectedServices && selectedServices.length > 0) {
            selectedServices.forEach(serviceId => {
                totalPrice += parseFloat(servicePrices[serviceId] || 0);
            });
        }

        $('#price').val(totalPrice.toFixed(2)); // Set calculated price
    });

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
                formData.append("mode", "addcustompackage");
                formData.append("event_date", $("#event_date").val());
                formData.append("event_place", $("#event_place").val());
                formData.append("package_name", $("#packageName").val());
                formData.append("price", $("#price").val());
                formData.append("description", $("#description").val());      
                let serviceTypes = $('#serviceType').val(); // array of selected values
                formData.append("service_types", JSON.stringify(serviceTypes));          
                formData.append("image", $(".input-images-1 .uploaded-image").attr('data-name'));
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