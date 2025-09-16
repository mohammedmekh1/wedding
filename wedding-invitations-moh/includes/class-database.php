<?php
/**
 * Database operations class
 */

if (!defined('ABSPATH')) {
    exit;
}

class WI_Database {

    public static function get_events($args = array()) {
        global $wpdb;

        $defaults = array(
            'status' => 'active',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 20
        );

        $args = wp_parse_args($args, $defaults);

        $where = "WHERE 1=1";
        if ($args['status']) {
            $where .= $wpdb->prepare(" AND status = %s", $args['status']);
        }

        $order = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        $limit = $args['limit'] ? "LIMIT " . intval($args['limit']) : "";

        $table = $wpdb->prefix . 'wi_events';
        $sql = "SELECT * FROM $table $where ORDER BY $order $limit";

        return $wpdb->get_results($sql);
    }

    public static function get_guests($event_id, $args = array()) {
        global $wpdb;

        $table = $wpdb->prefix . 'wi_guests';
        $where = $wpdb->prepare("WHERE event_id = %d", $event_id);

        if (isset($args['rsvp_status'])) {
            $where .= $wpdb->prepare(" AND rsvp_status = %s", $args['rsvp_status']);
        }

        $sql = "SELECT * FROM $table $where ORDER BY created_at DESC";
        return $wpdb->get_results($sql);
    }

    public static function create_event($data) {
        global $wpdb;

        $table = $wpdb->prefix . 'wi_events';

        $result = $wpdb->insert(
            $table,
            array(
                'title' => sanitize_text_field($data['title']),
                'description' => wp_kses_post($data['description']),
                'event_date' => $data['event_date'],
                'venue' => sanitize_text_field($data['venue']),
                'created_by' => get_current_user_id()
            ),
            array('%s', '%s', '%s', '%s', '%d')
        );

        return $result ? $wpdb->insert_id : false;
    }

    public static function create_guest($data) {
        global $wpdb;

        $table = $wpdb->prefix . 'wi_guests';

        $invitation_code = wp_generate_uuid4();

        $result = $wpdb->insert(
            $table,
            array(
                'event_id' => intval($data['event_id']),
                'name' => sanitize_text_field($data['name']),
                'email' => sanitize_email($data['email']),
                'phone' => sanitize_text_field($data['phone']),
                'invitation_code' => $invitation_code
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );

        return $result ? $wpdb->insert_id : false;
    }
}
