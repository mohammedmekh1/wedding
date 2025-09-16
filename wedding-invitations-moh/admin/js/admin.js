
jQuery(document).ready(function($) {

    // Handle RSVP form submission
    $('#wi-rsvp-form').on('submit', function(e) {
        e.preventDefault();

        var formData = {
            action: 'wi_handle_rsvp',
            nonce: wi_ajax.nonce,
            guest_id: $('input[name="guest_id"]').val(),
            response: $('input[name="rsvp_response"]:checked').val(),
            message: $('textarea[name="rsvp_message"]').val()
        };

        $.ajax({
            url: wi_ajax.ajax_url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#wi-rsvp-form button').prop('disabled', true).text('جاري الإرسال...');
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    $('#wi-rsvp-form')[0].reset();
                } else {
                    alert('حدث خطأ: ' + response.data.message);
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

    // Handle event creation
    $('#wi-create-event-form').on('submit', function(e) {
        e.preventDefault();

        var formData = {
            action: 'wi_create_event',
            nonce: wi_admin.nonce,
            title: $('#event_title').val(),
            description: $('#event_description').val(),
            event_date: $('#event_date').val(),
            venue: $('#event_venue').val()
        };

        $.ajax({
            url: wi_admin.ajax_url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#wi-create-event-form button').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    alert(wi_admin.strings.save_success);
                    location.reload();
                } else {
                    alert(wi_admin.strings.error_occurred);
                }
            },
            complete: function() {
                $('#wi-create-event-form button').prop('disabled', false);
            }
        });
    });

    // Handle guest creation
    $('#wi-create-guest-form').on('submit', function(e) {
        e.preventDefault();

        var formData = {
            action: 'wi_create_guest',
            nonce: wi_admin.nonce,
            event_id: $('#guest_event_id').val(),
            name: $('#guest_name').val(),
            email: $('#guest_email').val(),
            phone: $('#guest_phone').val()
        };

        $.ajax({
            url: wi_admin.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert(wi_admin.strings.save_success);
                    $('#wi-create-guest-form')[0].reset();
                    // Refresh guest list if exists
                    if ($('#guests-table').length) {
                        location.reload();
                    }
                } else {
                    alert(wi_admin.strings.error_occurred);
                }
            }
        });
    });

    // Delete confirmation
    $('.wi-delete-item').on('click', function(e) {
        if (!confirm(wi_admin.strings.confirm_delete)) {
            e.preventDefault();
        }
    });

    // Date picker initialization
    if ($.fn.datepicker) {
        $('.wi-datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
    }

    // Copy invitation link
    $('.wi-copy-link').on('click', function(e) {
        e.preventDefault();

        var link = $(this).data('link');
        if (navigator.clipboard) {
            navigator.clipboard.writeText(link).then(function() {
                alert('تم نسخ الرابط');
            });
        } else {
            // Fallback for older browsers
            var tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(link).select();
            document.execCommand('copy');
            tempInput.remove();
            alert('تم نسخ الرابط');
        }
    });
});
