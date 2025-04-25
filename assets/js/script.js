/**
 * Custom JavaScript for Event Planning Platform
 */

// Format currency with £ symbol
function formatCurrency(value) {
    return '£' + parseFloat(value).toFixed(2);
}

// Initialize dynamic price calculation for custom packages
function initCustomPackagePricing() {
    console.log('Initializing custom package pricing...');
    
    // Check if we're on a page with service checkboxes (customize package page)
    const serviceCheckboxes = document.querySelectorAll('input[name="services[]"]');
    if (serviceCheckboxes.length === 0) {
        console.log('No service checkboxes found');
        return;
    }
    
    console.log(`Found ${serviceCheckboxes.length} service checkboxes`);
    
    // Get the total price display element
    const totalPriceDisplay = document.getElementById('totalPrice');
    if (!totalPriceDisplay) {
        console.log('No total price display found, creating one');
        // Create one if it doesn't exist
        const servicesSection = document.getElementById('servicesSection');
        if (servicesSection) {
            const priceDisplay = document.createElement('div');
            priceDisplay.className = 'alert alert-info mt-3';
            priceDisplay.innerHTML = '<strong>Total Price:</strong> <span id="totalPrice">£0.00</span>';
            servicesSection.querySelector('.card-body').appendChild(priceDisplay);
        }
    }
    
    // Function to calculate and update the total price
    function updateTotalPrice() {
        let total = 0;
        const selectedServices = document.querySelectorAll('input[name="services[]"]:checked');
        console.log(`${selectedServices.length} services selected`);
        
        selectedServices.forEach(checkbox => {
            // Extract price from the label (format: Service Name (£100.00))
            const label = document.querySelector(`label[for="${checkbox.id}"]`);
            if (label) {
                const priceMatch = label.innerText.match(/\(£([\d\.]+)\)/);
                if (priceMatch && priceMatch[1]) {
                    const price = parseFloat(priceMatch[1]);
                    total += price;
                    console.log(`Added service: ${label.innerText.split('(')[0].trim()} - £${price}`);
                }
            }
        });
        
        console.log(`Total price: £${total.toFixed(2)}`);
        
        // Update the price display
        const totalPriceElement = document.getElementById('totalPrice');
        if (totalPriceElement) {
            totalPriceElement.textContent = formatCurrency(total);
            
            // If there's a hidden input for price, update it too
            const priceInput = document.getElementById('calculated_price');
            if (priceInput) {
                priceInput.value = total;
                console.log(`Updated hidden input price to: ${total}`);
            }
        }
    }
    
    // Add event listeners to all service checkboxes
    serviceCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateTotalPrice);
    });
    
    // Initial calculation
    updateTotalPrice();
}

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
    
    // Custom package calculations
    initCustomPackagePricing();
    
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