<?php
/**
 * Admin functionality class
 */

if (!defined('ABSPATH')) {
    exit;
}

class WI_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
    }

    public function add_admin_menu() {
        add_menu_page(
            __('دعوات الزفاف', WEDDING_INVITATIONS_TEXT_DOMAIN),
            __('دعوات الزفاف', WEDDING_INVITATIONS_TEXT_DOMAIN),
            'manage_options',
            'wedding-invitations',
            array($this, 'admin_page_dashboard'),
            'dashicons-heart',
            26
        );

        add_submenu_page(
            'wedding-invitations',
            __('المناسبات', WEDDING_INVITATIONS_TEXT_DOMAIN),
            __('المناسبات', WEDDING_INVITATIONS_TEXT_DOMAIN),
            'manage_options',
            'wi-events',
            array($this, 'admin_page_events')
        );

        add_submenu_page(
            'wedding-invitations',
            __('المدعوين', WEDDING_INVITATIONS_TEXT_DOMAIN),
            __('المدعوين', WEDDING_INVITATIONS_TEXT_DOMAIN),
            'manage_options',
            'wi-guests',
            array($this, 'admin_page_guests')
        );
    }

    public function admin_init() {
        // Register settings
        register_setting('wi_settings', 'wi_email_settings');
        register_setting('wi_settings', 'wi_invitation_settings');
    }

    public function admin_page_dashboard() {
        include WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/admin-dashboard.php';
    }

    public function admin_page_events() {
        include WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/admin-events.php';
    }

    public function admin_page_guests() {
        include WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/admin-guests.php';
    }
}

new WI_Admin();
