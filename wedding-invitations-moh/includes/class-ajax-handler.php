<?php
/**
 * AJAX requests handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class WI_Ajax_Handler {

    /**
     * Initialize AJAX hooks
     */
    public static function init() {
        add_action('wp_ajax_wi_handle_rsvp', array(__CLASS__, 'handle_rsvp'));
        add_action('wp_ajax_nopriv_wi_handle_rsvp', array(__CLASS__, 'handle_rsvp'));
        add_action('wp_ajax_wi_create_event', array(__CLASS__, 'create_event'));
        add_action('wp_ajax_wi_create_guest', array(__CLASS__, 'create_guest'));
        add_action('wp_ajax_wi_validate_qr_code', array(__CLASS__, 'validate_qr_code'));
    }

    /**
     * Handle RSVP submission
     */
    public static function handle_rsvp() {
        check_ajax_referer('wi_public_nonce', 'nonce');

        $guest_id = isset($_POST['guest_id']) ? intval($_POST['guest_id']) : 0;
        $response = isset($_POST['response']) ? sanitize_text_field($_POST['response']) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';

        if (!$guest_id || !$response) {
            wp_send_json_error(array('message' => __('بيانات غير صالحة', 'wedding-invitations')));
        }

        global $wpdb;
        $guests_table = $wpdb->prefix . 'wi_guests';

        // Prepare data for success response
        $success_data = array(
            'message' => __('تم إرسال ردكم بنجاح', 'wedding-invitations')
        );

        // If attending, generate a QR code
        if ($response === 'attending') {
            $qr_code_hash = wp_generate_uuid4();

            // Update guest with RSVP status and QR code hash
            $updated = $wpdb->update(
                $guests_table,
                array(
                    'rsvp_status' => $response,
                    'qr_code_hash' => $qr_code_hash
                ),
                array('id' => $guest_id),
                array('%s', '%s'),
                array('%d')
            );

            if ($updated !== false) {
                // Generate QR Code
                $validation_url = add_query_arg(array('guest_code' => $qr_code_hash), home_url('/validate-guest'));
                $qr_code = \Endroid\QrCode\QrCode::create($validation_url)
                    ->setSize(300)
                    ->setMargin(10);

                $success_data['qr_code_image'] = $qr_code->writeDataUri();
                $success_data['message'] = __('تم تأكيد حضوركم بنجاح! يرجى حفظ رمز QR التالي لاستخدامه عند الدخول.', 'wedding-invitations');
            }

        } else {
            // Just update RSVP status for non-attending guests
            $updated = $wpdb->update(
                $guests_table,
                array('rsvp_status' => $response),
                array('id' => $guest_id),
                array('%s'),
                array('%d')
            );
        }

        if ($updated === false) {
             wp_send_json_error(array('message' => __('فشل تحديث الرد', 'wedding-invitations')));
        }

        // Save detailed RSVP response
        $rsvp_table = $wpdb->prefix . 'wi_rsvp_responses';
        $wpdb->insert(
            $rsvp_table,
            array(
                'guest_id' => $guest_id,
                'response' => $response,
                'message' => $message
            ),
            array('%d', '%s', '%s')
        );

        wp_send_json_success($success_data);
    }

    /**
     * Handle event creation
     */
    public static function create_event() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('غير مسموح', 'wedding-invitations')));
        }

        check_ajax_referer('wi_admin_nonce', 'nonce');

        $event_data = array(
            'title' => isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '',
            'description' => isset($_POST['description']) ? wp_kses_post($_POST['description']) : '',
            'event_date' => isset($_POST['event_date']) ? sanitize_text_field($_POST['event_date']) : '',
            'venue' => isset($_POST['venue']) ? sanitize_text_field($_POST['venue']) : ''
        );

        if (empty($event_data['title']) || empty($event_data['event_date'])) {
            wp_send_json_error(array('message' => __('العنوان والتاريخ مطلوبان', 'wedding-invitations')));
        }

        $event_id = WI_Database::create_event($event_data);

        if ($event_id) {
            wp_send_json_success(array(
                'message' => __('تم إنشاء المناسبة بنجاح', 'wedding-invitations'),
                'event_id' => $event_id
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('فشل في إنشاء المناسبة', 'wedding-invitations')
            ));
        }
    }

    /**
     * Handle guest creation
     */
    public static function create_guest() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('غير مسموح', 'wedding-invitations')));
        }

        check_ajax_referer('wi_admin_nonce', 'nonce');

        $guest_data = array(
            'event_id' => isset($_POST['event_id']) ? intval($_POST['event_id']) : 0,
            'name' => isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '',
            'email' => isset($_POST['email']) ? sanitize_email($_POST['email']) : '',
            'phone' => isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : ''
        );

        if (empty($guest_data['event_id']) || empty($guest_data['name'])) {
            wp_send_json_error(array('message' => __('معرف المناسبة واسم الضيف مطلوبان', 'wedding-invitations')));
        }

        $guest_id = WI_Database::create_guest($guest_data);

        if ($guest_id) {
            wp_send_json_success(array(
                'message' => __('تم إضافة المدعو بنجاح', 'wedding-invitations'),
                'guest_id' => $guest_id
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('فشل في إضافة المدعو', 'wedding-invitations')
            ));
        }
    }

    /**
     * Handle QR code validation
     */
    public static function validate_qr_code() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('غير مسموح لك بإجراء هذه العملية.', 'wedding-invitations')));
        }

        check_ajax_referer('wi_qr_scanner_nonce', 'nonce');

        $hash = isset($_POST['qr_code_hash']) ? sanitize_text_field($_POST['qr_code_hash']) : '';

        if (empty($hash)) {
            wp_send_json_error(array('message' => __('لم يتم استلام رمز QR.', 'wedding-invitations')));
        }

        global $wpdb;
        $guests_table = $wpdb->prefix . 'wi_guests';

        $guest = $wpdb->get_row($wpdb->prepare(
            "SELECT id, name, checkin_status FROM $guests_table WHERE qr_code_hash = %s",
            $hash
        ));

        if (!$guest) {
            wp_send_json_error(array('message' => __('رمز QR غير صالح أو غير موجود.', 'wedding-invitations')));
        }

        if ($guest->checkin_status === 'checked_in') {
            wp_send_json_error(array(
                'message' => sprintf(
                    __('تم استخدام هذا الرمز بالفعل لتسجيل دخول الضيف: %s', 'wedding-invitations'),
                    $guest->name
                )
            ));
        }

        // If we reach here, the code is valid and unused. Mark as checked-in.
        $updated = $wpdb->update(
            $guests_table,
            array('checkin_status' => 'checked_in'),
            array('id' => $guest->id),
            array('%s'),
            array('%d')
        );

        if ($updated) {
            wp_send_json_success(array(
                'message' => sprintf(
                    __('تم التحقق بنجاح. مرحبًا بك، %s!', 'wedding-invitations'),
                    $guest->name
                )
            ));
        } else {
            wp_send_json_error(array('message' => __('فشل في تحديث حالة الضيف. يرجى المحاولة مرة أخرى.', 'wedding-invitations')));
        }
    }
}
