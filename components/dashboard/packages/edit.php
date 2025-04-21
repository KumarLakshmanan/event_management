<?php

$csrfToken = md5(uniqid(rand(), TRUE));
if (isset($_GET['packageid'])) {
    $packageid = $_GET['packageid'];
    $sql = "SELECT
    p.*,    
    p.id AS package_id,
    p.package_name,
    p.price,
    p.description,
    GROUP_CONCAT(s.service_name ORDER BY s.service_name SEPARATOR ', ') AS service_name,
    GROUP_CONCAT(s.id ORDER BY s.id SEPARATOR ', ') AS service_id
FROM package p
LEFT JOIN package_services ps ON p.id = ps.package_id
LEFT JOIN service s ON ps.service_id = s.id WHERE p.id = :id";
    $stmt = $pdoConn->prepare($sql);
    $stmt->bindParam(':id', $packageid);
    $stmt->execute();
    $propertyEdit = $stmt->fetch(PDO::FETCH_ASSOC);
    $propertyEdit['service_id'] = explode(',', $propertyEdit['service_id']);
?>
    <div class="container">
        <div class="white-box">
            <h2>Update Package</h2>
            <br />
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="packageName">Package Name</label>
                        <input type="text" class="form-control" id="packageName" placeholder="Enter Package Name" value="<?= $propertyEdit['package_name'] ?>">
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
                        <option value="<?= htmlspecialchars($service['id']) ?>" 
                            <?= in_array($service['id'], $propertyEdit['service_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($service['service_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                </div>
            </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input type="text" class="form-control" id="price" placeholder="Enter Price" value="<?= $propertyEdit['price'] ?>">
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
                        <input type="text" class="form-control" id="description" placeholder="Enter Description" value="<?= $propertyEdit['description'] ?>">
                    </div>
                </div>
            </div>
            <div class="p-2">
                <button type="button" class="w-100 btn btn-primary" id="saveButton">Save changes</button>
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
            ".input-images-1": "<?= $propertyEdit['image_url'] ?>",
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
                    formData.append("mode", "editpackage");
                    formData.append("package_id", "<?= $propertyEdit['id'] ?>");
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
<?php
}
