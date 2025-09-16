<?php
/**
 * AJAX requests handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class WI_Ajax_Handler {

    public function __construct() {
        add_action('wp_ajax_wi_handle_rsvp', array($this, 'handle_rsvp'));
        add_action('wp_ajax_nopriv_wi_handle_rsvp', array($this, 'handle_rsvp'));
        add_action('wp_ajax_wi_create_event', array($this, 'create_event'));
        add_action('wp_ajax_wi_create_guest', array($this, 'create_guest'));
    }

    public function handle_rsvp() {
        if (!wp_verify_nonce($_POST['nonce'], 'wi_public_nonce')) {
            wp_die(__('فشل التحقق من الأمان', WEDDING_INVITATIONS_TEXT_DOMAIN));
        }

        $guest_id = intval($_POST['guest_id']);
        $response = sanitize_text_field($_POST['response']);
        $message = sanitize_textarea_field($_POST['message']);

        global $wpdb;

        // Update guest RSVP status
        $guests_table = $wpdb->prefix . 'wi_guests';
        $wpdb->update(
            $guests_table,
            array('rsvp_status' => $response),
            array('id' => $guest_id),
            array('%s'),
            array('%d')
        );

        // Save RSVP response
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

        wp_send_json_success(array(
            'message' => __('تم إرسال ردكم بنجاح', WEDDING_INVITATIONS_TEXT_DOMAIN)
        ));
    }

    public function create_event() {
        if (!current_user_can('manage_options')) {
            wp_die(__('غير مسموح', WEDDING_INVITATIONS_TEXT_DOMAIN));
        }

        if (!wp_verify_nonce($_POST['nonce'], 'wi_admin_nonce')) {
            wp_die(__('فشل التحقق من الأمان', WEDDING_INVITATIONS_TEXT_DOMAIN));
        }

        $event_data = array(
            'title' => sanitize_text_field($_POST['title']),
            'description' => wp_kses_post($_POST['description']),
            'event_date' => sanitize_text_field($_POST['event_date']),
            'venue' => sanitize_text_field($_POST['venue'])
        );

        $event_id = WI_Database::create_event($event_data);

        if ($event_id) {
            wp_send_json_success(array(
                'message' => __('تم إنشاء المناسبة بنجاح', WEDDING_INVITATIONS_TEXT_DOMAIN),
                'event_id' => $event_id
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('فشل في إنشاء المناسبة', WEDDING_INVITATIONS_TEXT_DOMAIN)
            ));
        }
    }

    public function create_guest() {
        if (!current_user_can('manage_options')) {
            wp_die(__('غير مسموح', WEDDING_INVITATIONS_TEXT_DOMAIN));
        }

        if (!wp_verify_nonce($_POST['nonce'], 'wi_admin_nonce')) {
            wp_die(__('فشل التحقق من الأمان', WEDDING_INVITATIONS_TEXT_DOMAIN));
        }

        $guest_data = array(
            'event_id' => intval($_POST['event_id']),
            'name' => sanitize_text_field($_POST['name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone'])
        );

        $guest_id = WI_Database::create_guest($guest_data);

        if ($guest_id) {
            wp_send_json_success(array(
                'message' => __('تم إضافة المدعو بنجاح', WEDDING_INVITATIONS_TEXT_DOMAIN),
                'guest_id' => $guest_id
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('فشل في إضافة المدعو', WEDDING_INVITATIONS_TEXT_DOMAIN)
            ));
        }
    }
}

new WI_Ajax_Handler();
