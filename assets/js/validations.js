$(document).ready(function() {
    // Email validation pattern
    const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    
    // Phone validation pattern (accepts various formats)
    const phonePattern = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/;
    
    // Password requirements (at least 8 characters, 1 uppercase, 1 lowercase, 1 number)
    const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
    
    // Form validation for login
    $('#loginForm').on('submit', function(e) {
        let isValid = true;
        
        // Validate email
        const email = $('#email').val().trim();
        if (!email || !emailPattern.test(email)) {
            $('#email').addClass('is-invalid');
            $('#emailFeedback').text('Please enter a valid email address.');
            isValid = false;
        } else {
            $('#email').removeClass('is-invalid').addClass('is-valid');
        }
        
        // Validate password
        const password = $('#password').val();
        if (!password) {
            $('#password').addClass('is-invalid');
            $('#passwordFeedback').text('Please enter your password.');
            isValid = false;
        } else {
            $('#password').removeClass('is-invalid').addClass('is-valid');
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Form validation for registration
    $('#registerForm').on('submit', function(e) {
        let isValid = true;
        
        // Validate name
        const name = $('#name').val().trim();
        if (!name) {
            $('#name').addClass('is-invalid');
            $('#nameFeedback').text('Please enter your name.');
            isValid = false;
        } else {
            $('#name').removeClass('is-invalid').addClass('is-valid');
        }
        
        // Validate email
        const email = $('#email').val().trim();
        if (!email || !emailPattern.test(email)) {
            $('#email').addClass('is-invalid');
            $('#emailFeedback').text('Please enter a valid email address.');
            isValid = false;
        } else {
            $('#email').removeClass('is-invalid').addClass('is-valid');
        }
        
        // Validate phone
        const phone = $('#phone').val().trim();
        if (phone && !phonePattern.test(phone)) {
            $('#phone').addClass('is-invalid');
            $('#phoneFeedback').text('Please enter a valid phone number.');
            isValid = false;
        } else {
            $('#phone').removeClass('is-invalid');
            if (phone) $('#phone').addClass('is-valid');
        }
        
        // Validate password
        const password = $('#password').val();
        if (!password || !passwordPattern.test(password)) {
            $('#password').addClass('is-invalid');
            $('#passwordFeedback').text('Password must contain at least 8 characters including uppercase, lowercase and numbers.');
            isValid = false;
        } else {
            $('#password').removeClass('is-invalid').addClass('is-valid');
        }
        
        // Validate confirm password
        const confirmPassword = $('#confirmPassword').val();
        if (!confirmPassword || password !== confirmPassword) {
            $('#confirmPassword').addClass('is-invalid');
            $('#confirmPasswordFeedback').text('Passwords do not match.');
            isValid = false;
        } else {
            $('#confirmPassword').removeClass('is-invalid').addClass('is-valid');
        }
        
        // Validate address
        const address = $('#address').val().trim();
        if (!address) {
            $('#address').addClass('is-invalid');
            $('#addressFeedback').text('Please enter your address.');
            isValid = false;
        } else {
            $('#address').removeClass('is-invalid').addClass('is-valid');
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Form validation for package form
    $('#packageForm').on('submit', function(e) {
        let isValid = true;
        
        // Validate name
        const name = $('#name').val().trim();
        if (!name) {
            $('#name').addClass('is-invalid');
            $('#nameFeedback').text('Please enter package name.');
            isValid = false;
        } else {
            $('#name').removeClass('is-invalid').addClass('is-valid');
        }
        
        // Validate price
        const price = $('#price').val().trim();
        if (!price || isNaN(parseFloat(price)) || parseFloat(price) <= 0) {
            $('#price').addClass('is-invalid');
            $('#priceFeedback').text('Please enter a valid price.');
            isValid = false;
        } else {
            $('#price').removeClass('is-invalid').addClass('is-valid');
        }
        
        // Validate description
        const description = $('#description').val().trim();
        if (!description) {
            $('#description').addClass('is-invalid');
            $('#descriptionFeedback').text('Please enter a description.');
            isValid = false;
        } else {
            $('#description').removeClass('is-invalid').addClass('is-valid');
        }
        
        // Validate services
        const services = $('#services').val();
        if (!services || services.length === 0) {
            $('#services').next('.select2-container').css('border', '1px solid #e74a3b');
            $('#servicesFeedback').show();
            isValid = false;
        } else {
            $('#services').next('.select2-container').css('border', 'none');
            $('#servicesFeedback').hide();
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Form validation for service form
    $('#serviceForm').on('submit', function(e) {
        let isValid = true;
        
        // Validate name
        const name = $('#name').val().trim();
        if (!name) {
            $('#name').addClass('is-invalid');
            $('#nameFeedback').text('Please enter service name.');
            isValid = false;
        } else {
            $('#name').removeClass('is-invalid').addClass('is-valid');
        }
        
        // Validate price
        const price = $('#price').val().trim();
        if (!price || isNaN(parseFloat(price)) || parseFloat(price) <= 0) {
            $('#price').addClass('is-invalid');
            $('#priceFeedback').text('Please enter a valid price.');
            isValid = false;
        } else {
            $('#price').removeClass('is-invalid').addClass('is-valid');
        }
        
        // Validate description
        const description = $('#description').val().trim();
        if (!description) {
            $('#description').addClass('is-invalid');
            $('#descriptionFeedback').text('Please enter a description.');
            isValid = false;
        } else {
            $('#description').removeClass('is-invalid').addClass('is-valid');
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Form validation for booking form
    $('#bookingForm').on('submit', function(e) {
        let isValid = true;
        
        // Validate package
        const packageId = $('#package_id').val();
        if (!packageId) {
            $('#package_id').next('.select2-container').css('border', '1px solid #e74a3b');
            $('#packageFeedback').show();
            isValid = false;
        } else {
            $('#package_id').next('.select2-container').css('border', 'none');
            $('#packageFeedback').hide();
        }
        
        // Validate event date
        const eventDate = $('#event_date').val().trim();
        if (!eventDate) {
            $('#event_date').addClass('is-invalid');
            $('#eventDateFeedback').text('Please select an event date.');
            isValid = false;
        } else {
            $('#event_date').removeClass('is-invalid').addClass('is-valid');
        }
        
        // Validate event place
        const eventPlace = $('#event_place').val().trim();
        if (!eventPlace) {
            $('#event_place').addClass('is-invalid');
            $('#eventPlaceFeedback').text('Please enter the event location.');
            isValid = false;
        } else {
            $('#event_place').removeClass('is-invalid').addClass('is-valid');
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Form validation for guest form
    $('#guestForm').on('submit', function(e) {
        let isValid = true;
        
        // Validate name
        const name = $('#name').val().trim();
        if (!name) {
            $('#name').addClass('is-invalid');
            $('#nameFeedback').text('Please enter guest name.');
            isValid = false;
        } else {
            $('#name').removeClass('is-invalid').addClass('is-valid');
        }
        
        // Validate email
        const email = $('#email').val().trim();
        if (!email || !emailPattern.test(email)) {
            $('#email').addClass('is-invalid');
            $('#emailFeedback').text('Please enter a valid email address.');
            isValid = false;
        } else {
            $('#email').removeClass('is-invalid').addClass('is-valid');
        }
        
        // Validate phone
        const phone = $('#phone').val().trim();
        if (phone && !phonePattern.test(phone)) {
            $('#phone').addClass('is-invalid');
            $('#phoneFeedback').text('Please enter a valid phone number.');
            isValid = false;
        } else {
            $('#phone').removeClass('is-invalid');
            if (phone) $('#phone').addClass('is-valid');
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Form validation for user form
    $('#userForm').on('submit', function(e) {
        let isValid = true;
        
        // Validate name
        const name = $('#name').val().trim();
        if (!name) {
            $('#name').addClass('is-invalid');
            $('#nameFeedback').text('Please enter user name.');
            isValid = false;
        } else {
            $('#name').removeClass('is-invalid').addClass('is-valid');
        }
        
        // Validate email
        const email = $('#email').val().trim();
        if (!email || !emailPattern.test(email)) {
            $('#email').addClass('is-invalid');
            $('#emailFeedback').text('Please enter a valid email address.');
            isValid = false;
        } else {
            $('#email').removeClass('is-invalid').addClass('is-valid');
        }
        
        // Validate phone
        const phone = $('#phone').val().trim();
        if (phone && !phonePattern.test(phone)) {
            $('#phone').addClass('is-invalid');
            $('#phoneFeedback').text('Please enter a valid phone number.');
            isValid = false;
        } else {
            $('#phone').removeClass('is-invalid');
            if (phone) $('#phone').addClass('is-valid');
        }
        
        // Validate role
        const role = $('#role').val();
        if (!role) {
            $('#role').addClass('is-invalid');
            $('#roleFeedback').text('Please select a role.');
            isValid = false;
        } else {
            $('#role').removeClass('is-invalid').addClass('is-valid');
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Real-time validation for inputs
    $('input, textarea, select').on('blur', function() {
        const id = $(this).attr('id');
        const value = $(this).val().trim();
        
        switch(id) {
            case 'email':
                if (!value || !emailPattern.test(value)) {
                    $(this).addClass('is-invalid');
                    $('#emailFeedback').text('Please enter a valid email address.');
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
                break;
                
            case 'phone':
                if (value && !phonePattern.test(value)) {
                    $(this).addClass('is-invalid');
                    $('#phoneFeedback').text('Please enter a valid phone number.');
                } else {
                    $(this).removeClass('is-invalid');
                    if (value) $(this).addClass('is-valid');
                }
                break;
                
            case 'password':
                if (!value || !passwordPattern.test(value)) {
                    $(this).addClass('is-invalid');
                    $('#passwordFeedback').text('Password must contain at least 8 characters including uppercase, lowercase and numbers.');
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
                break;
                
            case 'confirmPassword':
                const password = $('#password').val();
                if (!value || password !== value) {
                    $(this).addClass('is-invalid');
                    $('#confirmPasswordFeedback').text('Passwords do not match.');
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
                break;
                
            case 'name':
            case 'address':
            case 'description':
            case 'event_place':
                if (!value) {
                    $(this).addClass('is-invalid');
                    $(`#${id}Feedback`).text('This field is required.');
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
                break;
                
            case 'price':
                if (!value || isNaN(parseFloat(value)) || parseFloat(value) <= 0) {
                    $(this).addClass('is-invalid');
                    $('#priceFeedback').text('Please enter a valid price.');
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
                break;
                
            case 'event_date':
                if (!value) {
                    $(this).addClass('is-invalid');
                    $('#eventDateFeedback').text('Please select an event date.');
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
                break;
        }
    });
    
    // Select2 validation
    $('.select2').on('change', function() {
        const id = $(this).attr('id');
        const value = $(this).val();
        
        if (!value || (Array.isArray(value) && value.length === 0)) {
            $(this).next('.select2-container').css('border', '1px solid #e74a3b');
            $(`#${id}Feedback`).show();
        } else {
            $(this).next('.select2-container').css('border', 'none');
            $(`#${id}Feedback`).hide();
        }
    });
});
