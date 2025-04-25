// main.js - Custom JavaScript for the Event Planning Platform

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Handle service selection in package creation/editing
    var servicesCheckboxes = document.querySelectorAll('.service-checkbox');
    if (servicesCheckboxes.length > 0) {
        servicesCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateSelectedServices();
            });
        });
        
        // Initialize selected services
        updateSelectedServices();
    }
    
    // Handle guest RSVP status updates
    var rsvpStatusSelects = document.querySelectorAll('.rsvp-status-select');
    if (rsvpStatusSelects.length > 0) {
        rsvpStatusSelects.forEach(function(select) {
            select.addEventListener('change', function() {
                var guestId = this.getAttribute('data-guest-id');
                var status = this.value;
                updateGuestRsvpStatus(guestId, status);
            });
        });
    }
    
    // Handle booking status updates
    var bookingStatusSelects = document.querySelectorAll('.booking-status-select');
    if (bookingStatusSelects.length > 0) {
        bookingStatusSelects.forEach(function(select) {
            select.addEventListener('change', function() {
                var bookingId = this.getAttribute('data-booking-id');
                var status = this.value;
                updateBookingStatus(bookingId, status);
            });
        });
    }
    
    // Handle discount application
    var discountForm = document.getElementById('discount-form');
    if (discountForm) {
        discountForm.addEventListener('submit', function(e) {
            e.preventDefault();
            applyDiscount();
        });
    }
    
    // Package selection for booking
    var packageCards = document.querySelectorAll('.package-select-card');
    if (packageCards.length > 0) {
        packageCards.forEach(function(card) {
            card.addEventListener('click', function() {
                var packageId = this.getAttribute('data-package-id');
                document.getElementById('selected-package-id').value = packageId;
                
                // Remove selected class from all cards
                packageCards.forEach(function(c) {
                    c.classList.remove('border-primary');
                });
                
                // Add selected class to clicked card
                this.classList.add('border-primary');
            });
        });
    }
    
    // Date picker initialization
    var datePickers = document.querySelectorAll('.datepicker');
    if (datePickers.length > 0) {
        datePickers.forEach(function(input) {
            input.addEventListener('input', function(e) {
                validateDateInput(e.target);
            });
        });
    }
    
    // Handle custom package service selection
    var customPackageCheckboxes = document.querySelectorAll('.custom-service-checkbox');
    if (customPackageCheckboxes.length > 0) {
        customPackageCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateCustomPackagePrice();
            });
        });
    }
});

// Function to update selected services list
function updateSelectedServices() {
    var selectedServices = [];
    var checkboxes = document.querySelectorAll('.service-checkbox:checked');
    var selectedServicesList = document.getElementById('selected-services-list');
    
    if (!selectedServicesList) return;
    
    selectedServicesList.innerHTML = '';
    
    checkboxes.forEach(function(checkbox) {
        var serviceId = checkbox.value;
        var serviceName = checkbox.getAttribute('data-service-name');
        var servicePrice = checkbox.getAttribute('data-service-price');
        
        selectedServices.push({
            id: serviceId,
            name: serviceName,
            price: servicePrice
        });
        
        var li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.innerHTML = serviceName + ' <span class="badge bg-primary rounded-pill">$' + servicePrice + '</span>';
        selectedServicesList.appendChild(li);
    });
    
    // Update hidden input for selected services
    if (document.getElementById('selected-services')) {
        document.getElementById('selected-services').value = JSON.stringify(selectedServices.map(function(s) { return s.id; }));
    }
}

// Function to update guest RSVP status via AJAX
function updateGuestRsvpStatus(guestId, status) {
    // Create form data
    var formData = new FormData();
    formData.append('action', 'update_rsvp');
    formData.append('guest_id', guestId);
    formData.append('status', status);
    
    // Send AJAX request
    fetch('guestController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI if needed
            var statusElement = document.querySelector('.guest-status-' + guestId);
            if (statusElement) {
                statusElement.innerHTML = getStatusBadgeHTML(status);
            }
            
            // Flash success message
            showToast('RSVP status updated successfully', 'success');
        } else {
            showToast('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        showToast('Error updating RSVP status', 'danger');
        console.error('Error:', error);
    });
}

// Function to update booking status via AJAX
function updateBookingStatus(bookingId, status) {
    // Create form data
    var formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('booking_id', bookingId);
    formData.append('status', status);
    
    // Send AJAX request
    fetch('bookingController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI if needed
            var statusElement = document.querySelector('.booking-status-' + bookingId);
            if (statusElement) {
                statusElement.innerHTML = getStatusBadgeHTML(status);
            }
            
            // Flash success message
            showToast('Booking status updated successfully', 'success');
        } else {
            showToast('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        showToast('Error updating booking status', 'danger');
        console.error('Error:', error);
    });
}

// Function to apply discount to booking
function applyDiscount() {
    var bookingId = document.getElementById('booking-id').value;
    var discountAmount = document.getElementById('discount-amount').value;
    
    // Validate inputs
    if (!bookingId || !discountAmount || isNaN(discountAmount) || discountAmount <= 0) {
        showToast('Please enter a valid discount amount', 'warning');
        return;
    }
    
    // Create form data
    var formData = new FormData();
    formData.append('action', 'apply_discount');
    formData.append('booking_id', bookingId);
    formData.append('discount_amount', discountAmount);
    
    // Send AJAX request
    fetch('bookingController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            var totalPriceElement = document.getElementById('booking-total-price');
            if (totalPriceElement) {
                totalPriceElement.textContent = '$' + data.new_total.toFixed(2);
            }
            
            var discountElement = document.getElementById('booking-discount');
            if (discountElement) {
                discountElement.textContent = '$' + data.discount.toFixed(2);
            }
            
            // Reset discount input
            document.getElementById('discount-amount').value = '';
            
            // Flash success message
            showToast('Discount applied successfully', 'success');
        } else {
            showToast('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        showToast('Error applying discount', 'danger');
        console.error('Error:', error);
    });
}

// Function to validate date input
function validateDateInput(input) {
    var selectedDate = new Date(input.value);
    var today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        input.setCustomValidity('Please select a future date');
        input.reportValidity();
    } else {
        input.setCustomValidity('');
    }
}

// Function to update custom package price
function updateCustomPackagePrice() {
    var totalPrice = 0;
    var checkboxes = document.querySelectorAll('.custom-service-checkbox:checked');
    
    checkboxes.forEach(function(checkbox) {
        var servicePrice = parseFloat(checkbox.getAttribute('data-service-price'));
        totalPrice += servicePrice;
    });
    
    var totalPriceElement = document.getElementById('custom-package-total');
    if (totalPriceElement) {
        totalPriceElement.textContent = '$' + totalPrice.toFixed(2);
    }
    
    // Update hidden input for total price
    if (document.getElementById('custom-package-price')) {
        document.getElementById('custom-package-price').value = totalPrice.toFixed(2);
    }
}

// Helper function to get status badge HTML
function getStatusBadgeHTML(status) {
    var badgeClass = '';
    
    switch (status) {
        case 'pending':
            badgeClass = 'bg-warning';
            break;
        case 'confirmed':
            badgeClass = 'bg-primary';
            break;
        case 'cancelled':
            badgeClass = 'bg-danger';
            break;
        case 'completed':
            badgeClass = 'bg-success';
            break;
        case 'accepted':
            badgeClass = 'bg-success';
            break;
        case 'declined':
            badgeClass = 'bg-danger';
            break;
        default:
            badgeClass = 'bg-secondary';
    }
    
    return '<span class="badge ' + badgeClass + '">' + status.charAt(0).toUpperCase() + status.slice(1) + '</span>';
}

// Function to show toast notifications
function showToast(message, type) {
    // Check if toast container exists, create if not
    var toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    var toastId = 'toast-' + Date.now();
    var toast = document.createElement('div');
    toast.className = 'toast';
    toast.id = toastId;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    // Set toast content
    toast.innerHTML = `
        <div class="toast-header bg-${type} text-white">
            <strong class="me-auto">Notification</strong>
            <small>Just now</small>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            ${message}
        </div>
    `;
    
    // Add toast to container
    toastContainer.appendChild(toast);
    
    // Initialize and show the toast
    var bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: 5000
    });
    bsToast.show();
}
