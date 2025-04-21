$(document).ready(function() {
    // Toggle sidebar
    $('#sidebarToggle').on('click', function() {
        $('body').toggleClass('sidebar-toggle');
    });

    // Initialize DataTables
    if ($.fn.DataTable) {
        $('.dataTable').DataTable({
            responsive: true,
            ordering: true,
            searching: true,
            paging: true,
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]]
        });
    }

    // Initialize Select2
    if ($.fn.select2) {
        $('.select2').select2({
            placeholder: 'Select an option',
            allowClear: true
        });
    }

    // Initialize datepicker
    if ($.fn.datepicker) {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            startDate: '0d' // Starting from today
        });
    }

    // Initialize tooltip
    $('[data-toggle="tooltip"]').tooltip();

    // File input handling
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass('selected').html(fileName);
        
        // Preview image if selected
        if (this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result);
                $('#imagePreview').show();
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Handle form submissions
    $('form.api-form').on('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        let form = $(this);
        if (!form[0].checkValidity()) {
            e.stopPropagation();
            form.addClass('was-validated');
            return;
        }
        
        // Show loading spinner
        showSpinner();
        
        // Get form data
        let formData = new FormData(form[0]);
        
        // Get redirect URL
        let redirectUrl = form.data('redirect') || 'dashboard.php';
        
        // Get API endpoint
        let endpoint = form.attr('action');
        
        // Send AJAX request
        $.ajax({
            url: endpoint,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // For demo: Redirect without waiting for API response
                window.location.href = redirectUrl;
            },
            error: function(xhr, status, error) {
                // Hide spinner
                hideSpinner();
                
                // Show error message
                showAlert('Error submitting form. Please try again.', 'danger');
                
                console.error('Error:', error);
            }
        });
        
    });

    // Handle delete buttons
    $('.btn-delete').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to delete this item?')) {
            return;
        }
        
        let deleteUrl = $(this).attr('href');
        let redirectUrl = $(this).data('redirect') || window.location.href;
        
        // Show loading spinner
        showSpinner();
        
        // Send AJAX request
        $.ajax({
            url: deleteUrl,
            type: 'DELETE',
            success: function(response) {
                // For demo: Redirect without waiting for API response
                window.location.href = redirectUrl;
            },
            error: function(xhr, status, error) {
                // Hide spinner
                hideSpinner();
                
                // Show error message
                showAlert('Error deleting item. Please try again.', 'danger');
                
                console.error('Error:', error);
            }
        });
        
    });

    // Handle RSVP buttons
    $('.btn-rsvp').on('click', function(e) {
        e.preventDefault();
        
        let guestId = $(this).data('guest-id');
        let rsvpStatus = $(this).data('rsvp-status');
        let redirectUrl = $(this).data('redirect') || window.location.href;
        
        // Show loading spinner
        showSpinner();
        
        // Send AJAX request
        $.ajax({
            url: '../handlers/guests.php',
            type: 'POST',
            data: {
                action: 'update_rsvp',
                guest_id: guestId,
                rsvp_status: rsvpStatus
            },
            success: function(response) {
                // For demo: Redirect without waiting for API response
                window.location.href = redirectUrl;
            },
            error: function(xhr, status, error) {
                // Hide spinner
                hideSpinner();
                
                // Show error message
                showAlert('Error updating RSVP status. Please try again.', 'danger');
                
                console.error('Error:', error);
            }
        });
        
    });

    // Function to show loading spinner
    function showSpinner() {
        $('body').append('<div class="spinner-overlay"><div class="spinner-border text-light" role="status"><span class="sr-only">Loading...</span></div></div>');
    }

    // Function to hide loading spinner
    function hideSpinner() {
        $('.spinner-overlay').remove();
    }

    // Function to show alert message
    function showAlert(message, type) {
        let alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        
        $('#alertContainer').html(alertHtml);
        
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    }

    // Check for flash messages
    if ($('#flashMessage').length) {
        setTimeout(function() {
            $('#flashMessage').alert('close');
        }, 5000);
    }
});
