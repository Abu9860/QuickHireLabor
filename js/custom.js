// Mobile menu toggle
$(document).ready(function() {
    $('.navbar-toggler').on('click', function() {
        $(this).toggleClass('active');
        $('#navbarSupportedContent').toggleClass('show');
    });
    
    // Close menu when clicking outside
    $(document).click(function(event) {
        if (!$(event.target).closest('.navbar').length) {
            $('#navbarSupportedContent').removeClass('show');
            $('.navbar-toggler').removeClass('active');
        }
    });
    
    // Set active menu item based on current page
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    $(`.nav-link[href="${currentPage}"]`).addClass('active');
    
    // Handle FAQ link active state
    if(window.location.hash === '#faq-header') {
        $('.nav-link[href="#faq-header"]').addClass('active');
    }
});

// Enhanced dropdown functionality for mobile
$(document).ready(function() {
    // Handle dropdown clicks on mobile
    if ($(window).width() < 992) {
        $('.dropdown .nav-link').on('click', function(e) {
            if ($(this).next('.dropdown-content').length > 0) {
                e.preventDefault();
                $(this).next('.dropdown-content').slideToggle();
            }
        });
    }
    
    // Add active class based on current page URL
    var currentUrl = window.location.href;
    
    // Handle dropdown active states
    $('.dropdown-content a').each(function() {
        var linkUrl = $(this).attr('href');
        if (currentUrl.indexOf(linkUrl) !== -1) {
            $(this).addClass('active');
            $(this).closest('.dropdown').find('.nav-link').addClass('active');
        }
    });
    
    // Add accessibility attributes
    $('.dropdown .nav-link').attr('aria-haspopup', 'true');
    $('.dropdown-content').attr('aria-label', 'submenu');
});

// Newsletter Form Submission
$(document).ready(function() {
    $('#newsletterForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            type: 'POST',
            url: 'newsletter.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    $('#newsletterForm')[0].reset();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('An error occurred. Please try again later.');
            }
        });
    });
});

// Contact Form Submission with AJAX
$(document).ready(function() {
    $('form[action="contactus.php"]').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            type: 'POST',
            url: 'contactus.php',
            data: $(this).serialize(),
            success: function(response) {
                alert('Thank you for contacting us! We will get back to you soon.');
                $('form[action="contactus.php"]')[0].reset();
            },
            error: function() {
                alert('An error occurred. Please try again later.');
            }
        });
    });
});