
jQuery(document).ready(function($) {

    // Handle RSVP form submission (public)
    // Use a selector that works for multiple forms on a page
    $(document).on('submit', 'form[id^="wi-rsvp-form-"]', function(e) {
        e.preventDefault();

        var form = $(this);
        var formWrapper = form.parent('.wi-rsvp-form');
        var submitButton = form.find('button');

        var formData = {
            action: 'wi_handle_rsvp',
            nonce: form.find('input[name="wi_rsvp_nonce"]').val(), // Use the actual nonce from the form
            guest_id: form.find('input[name="guest_id"]').val(),
            response: form.find('input[name="rsvp_response"]:checked').val(),
            message: form.find('textarea[name="rsvp_message"]').val()
        };

        $.ajax({
            url: wi_public.ajax_url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                submitButton.prop('disabled', true).text('جاري الإرسال...');
            },
            success: function(response) {
                if (response.success) {
                    var message = '<div class="wi-success-message">' + response.data.message + '</div>';

                    if (response.data.qr_code_image) {
                        var qr_image = '<img src="' + response.data.qr_code_image + '" alt="QR Code" class="wi-qr-code-image">';
                        formWrapper.html(message + qr_image);
                    } else {
                        formWrapper.html(message);
                    }

                } else {
                    alert(response.data.message || 'حدث خطأ، يرجى المحاولة مرة أخرى');
                }
            },
            error: function() {
                alert('حدث خطأ في الشبكة، يرجى المحاولة مرة أخرى');
                submitButton.prop('disabled', false).text('إرسال الرد');
            }
        });
    });

    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        var target = $($(this).attr('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top - 20
            }, 500);
        }
    });

    // Animation on scroll
    function animateOnScroll() {
        $('.wi-fade-in').each(function() {
            var elementTop = $(this).offset().top;
            var scrollTop = $(window).scrollTop();
            var windowHeight = $(window).height();

            if (scrollTop + windowHeight > elementTop + 100) {
                $(this).addClass('wi-visible');
            }
        });
    }

    $(window).on('scroll', animateOnScroll);
    animateOnScroll(); // Initial call
});
