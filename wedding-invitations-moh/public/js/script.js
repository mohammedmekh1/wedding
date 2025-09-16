
jQuery(document).ready(function($) {

    // Handle RSVP form submission (public)
    $('#wi-rsvp-form').on('submit', function(e) {
        e.preventDefault();

        var formData = {
            action: 'wi_handle_rsvp',
            nonce: wi_public.nonce,
            guest_id: $('input[name="guest_id"]').val(),
            response: $('input[name="rsvp_response"]:checked').val(),
            message: $('textarea[name="rsvp_message"]').val()
        };

        $.ajax({
            url: wi_public.ajax_url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#wi-rsvp-form button').prop('disabled', true).text('جاري الإرسال...');
            },
            success: function(response) {
                if (response.success) {
                    $('.wi-rsvp-form').html('<div class="wi-success-message">' + response.data.message + '</div>');
                } else {
                    alert('حدث خطأ، يرجى المحاولة مرة أخرى');
                }
            },
            error: function() {
                alert('حدث خطأ في الشبكة، يرجى المحاولة مرة أخرى');
            },
            complete: function() {
                $('#wi-rsvp-form button').prop('disabled', false).text('إرسال الرد');
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
