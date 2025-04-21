<style>
.card:hover {
    transform: translateY(-5px);
    transition: all 0.3s ease-in-out;
}
.card-title {
    font-weight: 600;
}
</style>
<div class="">
<div class="row">
    <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
        <div class="white-box">
            <div class="text-end">
                <a href="<?= $adminBaseUrl ?>addcustompackage" class="btn btn-success text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 32 32">
                        <path fill="currentColor" d="M17 15V8h-2v7H8v2h7v7h2v-7h7v-2z" />
                    </svg>
                    Create Custom Package
                </a>
            </div>
        </div>
    </div>
</div>
    <div class="white-box">
        <h4>Pick A Package</h4>
        <br />
        

        <div class="container mt-4">
            <div class="row">
                <?php
                    $sql = "SELECT
                                p.*,    
                                p.id AS package_id,
                                p.package_name,
                                p.price,
                                p.description,
                                p.image_url,
                                GROUP_CONCAT(s.service_name ORDER BY s.service_name SEPARATOR ', ') AS service_name
                            FROM package p
                            LEFT JOIN package_services ps ON p.id = ps.package_id
                            LEFT JOIN service s ON ps.service_id = s.id
                            where p.created_by = 0
                            GROUP BY p.id
                            ORDER BY p.id DESC";
                    $stmt = $pdoConn->prepare($sql);
                    $stmt->execute();
                    $package_list = $stmt->fetchAll(PDO::FETCH_ASSOC); 

                    foreach ($package_list as $index => $package):
                ?>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100 shadow-lg border-0 rounded-4 overflow-hidden">
                            <?php if (!empty($package['image_url'])): ?>
                                <img src="<?= $baseUrl ?>uploads/images/<?= $package['image_url'] ?>" class="card-img-top" alt="<?= htmlspecialchars($package['package_name']) ?>" style="height: 180px; object-fit: cover;">
                            <?php else: ?>
                                <img src="assets/images/no-image.jpg" class="card-img-top" alt="No Image" style="height: 180px; object-fit: cover;">
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($package['package_name']) ?></h5>
                                <p class="card-text text-muted" style="font-size: 0.9rem;">
                                    <?= htmlspecialchars($package['description']) ?>
                                </p>
                                <?php if (!empty($package['service_name'])): ?>
                                    <p class="card-text" style="font-size: 0.85rem;">
                                        <strong>Services:</strong><br>
                                        <?= htmlspecialchars($package['service_name']) ?>
                                    </p>
                                <?php endif; ?>
                                <p class="mt-auto mb-2"><strong>Price:</strong> ₹<?= htmlspecialchars($package['price']) ?></p>
                                <a href="#" 
                                class="btn btn-outline-primary w-100 select-package-btn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#packageModal"
                                data-package-id="<?= $package['package_id'] ?>"
                                data-package-name="<?= htmlspecialchars($package['package_name']) ?>">
                                Select Package
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>            
            
        </div>
        <!-- <div class="p-2">
            <button type="button" id="saveButton" class="w-100 btn btn-primary saveButton">Book Event</button>
        </div> -->
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="packageModal" tabindex="-1" aria-labelledby="packageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <!-- <form method="post" action="submit_event.php"> -->
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="packageModalLabel">Book Package</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

          <input type="hidden" name="package_id" id="modalPackageId">

          <div class="mb-3">
            <label for="eventDate" class="form-label">Event Date</label>
            <input type="date" class="form-control" id="eventDate" name="event_date" required>
          </div>

          <div class="mb-3">
            <label for="eventPlace" class="form-label">Event Place</label>
            <input type="text" class="form-control" id="eventPlace" name="event_place" placeholder="Enter place" required>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" id="saveButton" class="btn btn-success">Submit Booking</button>
        </div>
      </div>
    <!-- </form> -->
  </div>
</div>

<script>
    let _xUserData = {
        "baseURL": "<?= $baseUrl ?>",
        "auth": "<?= $_SESSION['token'] ?>",
    };
    $("#saveButton").click(function(event) {
    event.preventDefault();

    const packageId = $("#modalPackageId").val();
    const eventDate = $("#eventDate").val();
    const eventPlace = $("#eventPlace").val();

    if (!eventDate || !eventPlace) {
        swal("Missing Fields", "Please fill in both Event Date and Place.", "warning");
        return;
    }

    swal({
        title: 'Confirm Booking',
        text: "Do you want to book this package?",
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then((willSubmit) => {
        if (willSubmit) {
            const formData = new FormData();
            formData.append("mode", "book_event");
            formData.append("package_id", packageId);
            formData.append("event_date", eventDate);
            formData.append("event_place", eventPlace);

            $(".preloader").show();

            $.ajax({
                url: "<?= $apiUrl ?>",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $(".preloader").hide();
                    if (response.error.code == '#200') {
                        swal("Success", "Package booked successfully!", "success")
                            .then(() => {
                                $('#packageModal').modal('hide');
                                window.location.reload();
                            });
                    } else {
                        swal("Error", response.error.description, "error");
                    }
                },
                error: function(xhr, status, error) {
                    $(".preloader").hide();
                    swal("Error", "Something went wrong. Please try again.", "error");
                }
            });
        }
    });
});

    document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById('packageModal');
  const packageIdInput = document.getElementById('modalPackageId');
  const modalTitle = document.getElementById('packageModalLabel');

  document.querySelectorAll('.select-package-btn').forEach(button => {
    button.addEventListener('click', function () {
      const packageId = this.getAttribute('data-package-id');
      const packageName = this.getAttribute('data-package-name');
      packageIdInput.value = packageId;
      modalTitle.textContent = 'Book: ' + packageName;
    });
  });
});
</script>