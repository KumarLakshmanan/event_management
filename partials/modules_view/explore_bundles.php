<!-- Include Bootstrap 5 if not already -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.package-card {
    transition: all 0.4s ease;
    border: 1px solid #e0e0e0;
    border-radius: 1rem;
    overflow: hidden;
    background: #ffffff;
}
.package-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
}
.package-card img {
    height: 200px;
    object-fit: cover;
    width: 100%;
}
.package-card-body {
    padding: 1rem;
}
.package-title {
    font-weight: 700;
    font-size: 1.25rem;
}
.package-services {
    font-size: 0.9rem;
    color: #555;
}
.package-price {
    font-size: 1.1rem;
    font-weight: bold;
    color: #0d6efd;
}
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Available Bundles</h2>
        <a href="<?= $adminBaseUrl ?>addcustompackage" class="btn btn-success">
            + Create New Bundles
        </a>
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
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="package-card h-100">
                    <img src="<?= !empty($package['image_url']) ? $adminBaseUrl . "uploads/images/" . $package['image_url'] : 'assets/images/no-image.jpg' ?>" alt="<?= htmlspecialchars($package['package_name']) ?>">
                    <div class="package-card-body d-flex flex-column">
                        <h5 class="package-title"><?= htmlspecialchars($package['package_name']) ?></h5>
                        <p class="package-services mb-2">
                            <?= htmlspecialchars($package['service_name'] ?: 'No services listed.') ?>
                        </p>
                        <div class="package-price mb-3">£<?= htmlspecialchars($package['price']) ?></div>
                        <button 
                            class="btn btn-primary w-100 mt-auto select-package-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#packageModal"
                            data-package-id="<?= $package['package_id'] ?>"
                            data-package-name="<?= htmlspecialchars($package['package_name']) ?>">
                            Select Bundles
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal for Booking -->
<div class="modal fade" id="packageModal" tabindex="-1" aria-labelledby="packageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content rounded-4">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title fw-bold" id="packageModalLabel">Book a Bundles</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="modalPackageId">

        <div class="mb-3">
          <label for="eventDate" class="form-label">Event Date</label>
          <input type="date" id="eventDate" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="eventPlace" class="form-label">Event Place</label>
          <input type="text" id="eventPlace" class="form-control" placeholder="Enter Event Place" required>
        </div>
      </div>
      <div class="modal-footer border-top-0">
        <button type="button" id="saveButton" class="btn btn-success w-100">Book Now</button>
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
