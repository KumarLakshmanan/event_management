<?php
if (isset($_GET['bookingid'])) {
    $bookingid = $_GET['bookingid'];
    $sql = "SELECT
        b.*,    
        p.id AS package_id,
        p.package_name,
        p.price,
        p.description,
        GROUP_CONCAT(s.service_name ORDER BY s.service_name SEPARATOR ', ') AS service_name,
        p.image_url
    FROM bookings b 
    LEFT JOIN package p ON p.id = b.package_id
    LEFT JOIN package_services ps ON p.id = ps.package_id
    LEFT JOIN service s ON ps.service_id = s.id
    WHERE b.id = :id
    GROUP BY p.id
    ORDER BY p.id DESC;";

    $stmt = $pdoConn->prepare($sql);
    $stmt->bindParam(':id', $bookingid);
    $stmt->execute();
    $property = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<div class="mt-4">
    <div class="card shadow p-4">
        <h2 class="mb-4">Booking Details</h2>
        <div class="row mb-3">
            <div class="col-md-6"><label class="form-label fw-bold">Event Date</label><div class="form-control-plaintext"><?= htmlspecialchars($property['event_date']) ?></div></div>
            <div class="col-md-6"><label class="form-label fw-bold">Event Place</label><div class="form-control-plaintext"><?= htmlspecialchars($property['event_place']) ?></div></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6"><label class="form-label fw-bold">Package Name</label><div class="form-control-plaintext"><?= htmlspecialchars($property['package_name']) ?></div></div>
            <div class="col-md-6"><label class="form-label fw-bold">Services Included</label><div class="form-control-plaintext"><?= htmlspecialchars($property['service_name']) ?></div></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6"><label class="form-label fw-bold">Price</label><div class="form-control-plaintext">£<?= number_format($property['price'], 2) ?></div></div>
            <div class="col-md-6"><label class="form-label fw-bold">Image</label><br>
                <?php if (!empty($property['image_url'])): ?>
                    <img src="<?= $adminBaseUrl ?>uploads/images/<?= $property['image_url'] ?>" class="img-thumbnail" style="max-width: 200px;">
                <?php else: ?>
                    <p class="text-muted">No image available</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Description</label>
            <div class="form-control-plaintext"><?= nl2br(htmlspecialchars($property['description'])) ?></div>
        </div>
        <?php if($_SESSION['discount_permission']===1 || $_SESSION['discount_permission']==="1"){ ?>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Discount</label>
                    <input type="number" class="form-control" id="discount_input" value="<?= $property['discount_amount'] ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Total (After Discount)</label>
                    <div class="form-control-plaintext" id="total_price_display">
                    £<?= number_format($property['price'] - $property['discount_amount'], 2) ?>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="booking_status" class="form-control" id="booking_status">
                        <option value="pending" <?= $property['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>                        
                        <option value="confirmed" <?= $property['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="completed" <?= $property['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>
        
    </div>

    <!-- Guest Section -->
    <div class="card shadow p-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Guest Details</h4>
            <!-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGuestModal">+ Add Guest</button> -->
        </div>
        <div id="guestList">
            <!-- Guest list loads here -->
        </div>
    </div>
    <div class="d-flex justify-content-between mt-3 gap-2">
        <a href="allbooking" class="btn btn-secondary">Back</a>
        <button type="button" class="btn btn-primary" id="saveButton">Update</button>
    </div>

</div>

<!-- Guest Modal -->
<div class="modal fade" id="addGuestModal" tabindex="-1" aria-labelledby="addGuestLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="guestForm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Guest</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="booking_id" value="<?= $bookingid ?>">
                <div class="mb-3">
                    <label class="form-label">Guest Name</label>
                    <input type="text" id="guest_name" name="guest_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Guest Email</label>
                    <input type="text" id="guest_email" name="guest_email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contact</label>
                    <input type="text" id="guest_contact" name="guest_contact" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" id="saveButton" class="btn btn-success">Save Guest</button>
            </div>
        </div>
    </form>
  </div>
</div>
<script>
$(document).ready(function() {
    loadGuestList();
    $('#discount_input').on('input', function() {
        let originalPrice = <?= $property['price'] ?>;
        let discount = parseFloat($(this).val()) || 0;
        let total = originalPrice - discount;
        $('#total_price_display').text('£' + total.toFixed(2));
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
                formData.append("mode", "updatestatusbooking");
                formData.append("booking_id", <?= $bookingid ?>);
                formData.append("discount_amount", $("#discount_input").val());
                formData.append("booking_status", $("#booking_status").val());
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
                                // // if (result.value) {
                                //     $('#addGuestModal').modal('hide');
                                //      $('#guestForm')[0].reset();
                                //     loadGuestList();
                                // // }
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

    // $('#guestForm').submit(function(e) {
    //     e.preventDefault();
    //     $.ajax({
    //         url: "save_guests",
    //         type: "POST",
    //         data: $(this).serialize(),
    //         success: function(res) {
    //             let data = JSON.parse(res);
    //             if (data.status === 'success') {
    //                 $('#addGuestModal').modal('hide');
    //                 $('#guestForm')[0].reset();
    //                 loadGuestList();
    //             } else {
    //                 alert("Failed to save guest.");
    //             }
    //         }
    //     });
    // });

    function loadGuestList() {

        $.ajax({
        url: "<?= $apiUrl ?>",
        type: "POST",
        data: {
            mode: "getguest",
            booking_id: <?= $bookingid ?>
        },
        success: function(response) {
            if (response.error.code === '#200') {
                let table = '<table class="table table-bordered"><thead><tr><th>Guest Name</th><th>Contact</th><th>Email</th><th>RSVP</th></tr></thead><tbody>';
                if (response.data.length > 0) {
                    response.data.forEach(g => {
                        table += `<tr>
                            <td>${g.guest_name}</td>
                            <td>${g.guest_contact}</td>
                            <td>${g.guest_email}</td>
                            <td>${g.rsvp_status == "2" ? '<span class="badge bg-success">Attending</span>' : g.rsvp_status == "1" ? '<span class="badge bg-danger">Not Attending</span>' : '<span class="badge bg-warning">Pending</span>'}</td>
                        </tr>`;
                    });
                } else {
                    table += `<tr><td colspan="4" class="text-muted">No guests added yet.</td></tr>`;
                }
                table += '</tbody></table>';
                $('#guestList').html(table);
            }
        }
    });


        
        // var formData = new FormData();
        //         formData.append("mode", "getguest");
        //         formData.append("booking_id", <?= $bookingid ?>);
        //         $(".preloader").show();
        //         $.ajax({
        //             url: "<?= $apiUrl ?>",
        //             type: "POST",
        //             data: formData,
        //             contentType: false,
        //             cache: false,
        //             processData: false,
        //             success: function(response) {
        //                 $(".preloader").hide();
        //                 if (response.error.code == '#200') {
        //                     $('#guestList').html(html);
        //                 } 
        //             }
        //         });

        // $.ajax({
        //     url: "get_guests",
        //     type: "GET",
        //     data: { booking_id: <?= $bookingid ?> },
        //     success: function(html) {
        //         $('#guestList').html(html);
        //     }
        // });
    }
});
</script>
<?php } ?>