<?php
/**
 * Frontend Scripts and Styles
 */

if (!defined('ABSPATH')) {
    exit;
}

class WI_Frontend {

    /**
     * Initialize frontend hooks
     */
    public static function init() {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
    }

    /**
     * Enqueue scripts and styles
     */
    public static function enqueue_assets() {
        wp_enqueue_style(
            'wedding-invitations-public',
            WEDDING_INVITATIONS_PLUGIN_URL . 'public/css/style.css',
            array(),
            WEDDING_INVITATIONS_VERSION
        );

        wp_enqueue_script(
            'wedding-invitations-public',
            WEDDING_INVITATIONS_PLUGIN_URL . 'public/js/script.js',
            array('jquery'),
            WEDDING_INVITATIONS_VERSION,
            true
        );

        wp_localize_script('wedding-invitations-public', 'wi_public', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wi_public_nonce')
        ));
    }
}
