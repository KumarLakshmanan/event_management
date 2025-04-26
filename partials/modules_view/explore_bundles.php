<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-transparent">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Available Bundles</h5>
                    <a href="<?= $adminBaseUrl ?>addcustompackage" class="btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i> Create Custom Bundle
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
        <?php
            $sql = "SELECT p.*, p.id AS package_id, p.package_name, p.price, p.description, p.image_url,
                    GROUP_CONCAT(s.service_name ORDER BY s.service_name SEPARATOR ', ') AS service_name
                    FROM package p
                    LEFT JOIN package_services ps ON p.id = ps.package_id
                    LEFT JOIN service s ON ps.service_id = s.id
                    WHERE p.created_by = 0
                    GROUP BY p.id
                    ORDER BY p.id DESC";
            $stmt = $pdoConn->prepare($sql);
            $stmt->execute();
            $package_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($package_list as $package):
        ?>
            <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                <div class="package-card h-100">
                    <div class="position-relative">
                        <img src="<?= !empty($package['image_url']) ? $adminBaseUrl . "uploads/images/" . $package['image_url'] : 'assets/images/no-image.jpg' ?>" alt="<?= htmlspecialchars($package['package_name']) ?>" class="img-fluid">
                        <div class="position-absolute top-0 end-0 p-2">
                            <span class="badge bg-primary">£<?= htmlspecialchars($package['price']) ?></span>
                        </div>
                    </div>
                    <div class="package-card-body d-flex flex-column">
                        <h5 class="package-title"><?= htmlspecialchars($package['package_name']) ?></h5>
                        <div class="package-services mb-3">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            <?= htmlspecialchars($package['service_name'] ?: 'No services listed.') ?>
                        </div>
                        <button 
                            class="btn btn-primary w-100 mt-auto select-package-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#packageModal"
                            data-package-id="<?= $package['package_id'] ?>"
                            data-package-name="<?= htmlspecialchars($package['package_name']) ?>">
                            <i class="bi bi-calendar-plus me-2"></i>Book Now
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal for Booking -->
<div class="modal fade" id="packageModal" tabindex="-1" aria-labelledby="packageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="packageModalLabel">Book a Bundle</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="modalPackageId">

        <div class="mb-4">
          <label for="eventDate" class="form-label"><i class="bi bi-calendar3 me-2"></i>Event Date</label>
          <input type="date" id="eventDate" class="form-control form-control-lg" required>
        </div>

        <div class="mb-4">
          <label for="eventPlace" class="form-label"><i class="bi bi-geo-alt me-2"></i>Event Location</label>
          <input type="text" id="eventPlace" class="form-control form-control-lg" placeholder="Enter event location" required>
        </div>
      </div>
      <div class="modal-footer border-top-0 justify-content-center p-3">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="saveButton" class="btn btn-success px-4"><i class="bi bi-check-circle me-2"></i>Confirm Booking</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert/dist/sweetalert.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const packageIdInput = document.getElementById('modalPackageId');
    const modalTitle = document.getElementById('packageModalLabel');

    document.querySelectorAll('.select-package-btn').forEach(button => {
        button.addEventListener('click', function () {
            const packageId = this.getAttribute('data-package-id');
            const packageName = this.getAttribute('data-package-name');
            packageIdInput.value = packageId;
            modalTitle.textContent = "Book: " + packageName;
        });
    });

    document.getElementById('saveButton').addEventListener('click', function (e) {
        e.preventDefault();
        const packageId = document.getElementById('modalPackageId').value;
        const eventDate = document.getElementById('eventDate').value;
        const eventPlace = document.getElementById('eventPlace').value;

        if (!eventDate || !eventPlace) {
            swal("Warning", "Please fill in Event Date and Place.", "warning");
            return;
        }

        const formData = new FormData();
        formData.append('mode', 'book_event');
        formData.append('package_id', packageId);
        formData.append('event_date', eventDate);
        formData.append('event_place', eventPlace);

        $(".preloader").show(); // If you have preloader

        fetch("<?= $apiUrl ?>", {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            $(".preloader").hide();
            if (data.error.code === '#200') {
                swal("Success", "Bundles booked successfully!", "success")
                    .then(() => {
                        $('#packageModal').modal('hide');
                        window.location.reload();
                    });
            } else {
                swal("Error", data.error.description || "Unknown error occurred", "error");
            }
        })
        .catch(error => {
            $(".preloader").hide();
            console.error(error);
            swal("Error", "An error occurred. Try again later.", "error");
        });
    });
});
</script>
