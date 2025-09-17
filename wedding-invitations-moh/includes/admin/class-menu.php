<?php
/**
 * Admin Menu Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class WI_Admin_Menu {

    /**
     * Initialize the menu
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
    }

    /**
     * Add admin menu pages
     */
    public static function add_admin_menu() {
        add_menu_page(
            __('دعوات الزفاف', 'wedding-invitations'),
            __('دعوات الزفاف', 'wedding-invitations'),
            'manage_options',
            'wedding-invitations',
            array(__CLASS__, 'render_dashboard_page'),
            'dashicons-heart',
            26
        );

        add_submenu_page(
            'wedding-invitations',
            __('المناسبات', 'wedding-invitations'),
            __('المناسبات', 'wedding-invitations'),
            'manage_options',
            'wi-events',
            array(__CLASS__, 'render_events_page')
        );

        add_submenu_page(
            'wedding-invitations',
            __('المدعوين', 'wedding-invitations'),
            __('المدعوين', 'wedding-invitations'),
            'manage_options',
            'wi-guests',
            array(__CLASS__, 'render_guests_page')
        );

        add_submenu_page(
            'wedding-invitations',
            __('ماسح QR', 'wedding-invitations'),
            __('ماسح QR', 'wedding-invitations'),
            'manage_options',
            'wi-qr-scanner',
            array(__CLASS__, 'render_scanner_page')
        );
    }

    /**
     * Render the dashboard page
     */
    public static function render_dashboard_page() {
        // This will hold the content of admin-dashboard.php
        if (file_exists(WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/admin/views/dashboard.php')) {
            include_once WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/admin/views/dashboard.php';
        } else {
            echo "<h1>Dashboard View Not Found</h1>";
        }
    }

    /**
     * Render the events page
     */
    public static function render_events_page() {
        // This will hold the content of admin-events.php
        if (file_exists(WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/admin/views/events.php')) {
            include_once WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/admin/views/events.php';
        } else {
            echo "<h1>Events View Not Found</h1>";
        }
    }

    /**
     * Render the guests page
     */
    public static function render_guests_page() {
        // This will hold the content of admin-guests.php
        if (file_exists(WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/admin/views/guests.php')) {
            include_once WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/admin/views/guests.php';
        } else {
            echo "<h1>Guests View Not Found</h1>";
        }
    }

    /**
     * Render the scanner page
     */
    public static function render_scanner_page() {
        if (file_exists(WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/admin/views/scanner.php')) {
            include_once WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/admin/views/scanner.php';
        } else {
            echo "<h1>Scanner View Not Found</h1>";
        }
    }
}
