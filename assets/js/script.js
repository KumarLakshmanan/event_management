/**
 * Custom JavaScript for Event Planning Platform
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize Bootstrap popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Password toggle visibility
    const togglePassword = document.querySelectorAll('.toggle-password');
    togglePassword.forEach(button => {
        button.addEventListener('click', function() {
            const target = document.querySelector(this.getAttribute('data-target'));
            
            if (target.getAttribute('type') === 'password') {
                target.setAttribute('type', 'text');
                this.innerHTML = '<i class="bi bi-eye-slash"></i>';
            } else {
                target.setAttribute('type', 'password');
                this.innerHTML = '<i class="bi bi-eye"></i>';
            }
        });
    });
    
    // Confirm deletion modals
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-id');
            const targetName = this.getAttribute('data-name');
            const targetUrl = this.getAttribute('href');
            
            // Set modal content
            if (targetName) {
                document.getElementById('delete-item-name').textContent = targetName;
            }
            
            // Set confirm button action
            const confirmButton = document.getElementById('confirm-delete');
            confirmButton.onclick = function() {
                window.location.href = targetUrl;
            };
            
            // Show modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
    });
    
    // Date picker initialization
    const datePickers = document.querySelectorAll('.datepicker');
    datePickers.forEach(picker => {
        picker.addEventListener('click', function() {
            this.showPicker();
        });
    });
});