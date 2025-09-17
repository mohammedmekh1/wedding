<?php
/**
 * Admin Settings Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class WI_Admin_Settings {

    /**
     * Initialize the settings
     */
    public static function init() {
        add_action('admin_init', array(__CLASS__, 'register_settings'));
    }

    /**
     * Register plugin settings
     */
    public static function register_settings() {
        register_setting('wi_settings_group', 'wi_email_settings');
        register_setting('wi_settings_group', 'wi_invitation_settings');
        register_setting('wi_settings_group', 'wi_general_settings');
    }
}
