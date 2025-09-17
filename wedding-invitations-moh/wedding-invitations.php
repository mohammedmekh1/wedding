<?php
/**
 * Plugin Name: إدارة دعوات الزفاف
 * Plugin URI: https://example.com/wedding-invitations
 * Description: نظام شامل لإدارة دعوات الزفاف والمناسبات - إدارة المدعوين، إرسال الدعوات، ومتابعة الردود مع واجهة حديثة وسهلة الاستخدام
 * Version: 1.0.0
 * Author: فريق التطوير
 * Author URI: https://example.com
 * Text Domain: wedding-invitations
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

// Define plugin constants
define('WEDDING_INVITATIONS_VERSION', '1.0.0');
define('WEDDING_INVITATIONS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WEDDING_INVITATIONS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WEDDING_INVITATIONS_PLUGIN_FILE', __FILE__);
define('WEDDING_INVITATIONS_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WEDDING_INVITATIONS_TEXT_DOMAIN', 'wedding-invitations');

// Include the Composer autoloader
if (file_exists(WEDDING_INVITATIONS_PLUGIN_PATH . 'vendor/autoload.php')) {
    require_once WEDDING_INVITATIONS_PLUGIN_PATH . 'vendor/autoload.php';
}

/**
 * Main Wedding Invitations Plugin Class
 */
class Wedding_Invitations_Plugin {

    /**
     * Plugin instance
     * @var Wedding_Invitations_Plugin
     */
    private static $instance = null;

    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain(
            WEDDING_INVITATIONS_TEXT_DOMAIN, 
            false, 
            dirname(WEDDING_INVITATIONS_PLUGIN_BASENAME) . '/languages/'
        );

        // Initialize components
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Core includes
        require_once WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/class-database.php';
        require_once WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/class-ajax-handler.php';

        // Admin components
        if (is_admin()) {
            require_once WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/admin/class-menu.php';
            require_once WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/admin/class-settings.php';
        }

        // Public components
        // Note: shortcodes can also be used in admin area, so we load it everywhere.
        require_once WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/public/class-shortcodes.php';

        if (!is_admin()) {
            require_once WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/public/class-frontend.php';
        }
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Admin hooks
        if (is_admin()) {
            WI_Admin_Menu::init();
            WI_Admin_Settings::init();
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        }

        // Public hooks
        if (!is_admin()) {
            WI_Frontend::init();
        }

        // AJAX hooks
        WI_Ajax_Handler::init();

        // Shortcodes
        WI_Shortcodes::init();
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'wedding-invitations') === false) {
            return;
        }

        wp_enqueue_style(
            'wedding-invitations-admin',
            WEDDING_INVITATIONS_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            WEDDING_INVITATIONS_VERSION
        );

        wp_enqueue_script(
            'wedding-invitations-admin',
            WEDDING_INVITATIONS_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery'),
            WEDDING_INVITATIONS_VERSION,
            true
        );

        // Localize script
        wp_localize_script('wedding-invitations-admin', 'wi_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wi_admin_nonce'),
            'strings' => array(
                'confirm_delete' => __('هل أنت متأكد من حذف هذا العنصر؟', WEDDING_INVITATIONS_TEXT_DOMAIN),
                'save_success' => __('تم الحفظ بنجاح', WEDDING_INVITATIONS_TEXT_DOMAIN),
                'error_occurred' => __('حدث خطأ، يرجى المحاولة مرة أخرى', WEDDING_INVITATIONS_TEXT_DOMAIN)
            )
        ));
    }


    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        $this->create_database_tables();

        // Create default options
        $this->create_default_options();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up temporary data
        wp_clear_scheduled_hook('wi_cleanup_temp_data');

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin uninstall
     */
    public static function uninstall() {
        // Remove database tables
        self::drop_database_tables();

        // Remove options
        self::remove_plugin_options();

        // Remove uploaded files
        self::cleanup_uploaded_files();
    }

    /**
     * Create database tables
     */
    private function create_database_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Events table
        $events_table = $wpdb->prefix . 'wi_events';
        $events_sql = "CREATE TABLE $events_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text,
            event_date datetime NOT NULL,
            venue varchar(255),
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status enum('active','inactive','completed') DEFAULT 'active',
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Guests table
        $guests_table = $wpdb->prefix . 'wi_guests';
        $guests_sql = "CREATE TABLE $guests_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            event_id mediumint(9) NOT NULL,
            name varchar(255) NOT NULL,
            email varchar(255),
            phone varchar(50),
            invitation_code varchar(100) UNIQUE,
            rsvp_status enum('pending','attending','not_attending') DEFAULT 'pending',
            plus_one tinyint(1) DEFAULT 0,
            notes text,
            qr_code_hash varchar(40) DEFAULT NULL,
            checkin_status varchar(20) NOT NULL DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY event_id (event_id),
            UNIQUE KEY invitation_code (invitation_code),
            UNIQUE KEY qr_code_hash (qr_code_hash)
        ) $charset_collate;";

        // RSVP responses table
        $rsvp_table = $wpdb->prefix . 'wi_rsvp_responses';
        $rsvp_sql = "CREATE TABLE $rsvp_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            guest_id mediumint(9) NOT NULL,
            response enum('attending','not_attending') NOT NULL,
            message text,
            dietary_requirements text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY guest_id (guest_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($events_sql);
        dbDelta($guests_sql);
        dbDelta($rsvp_sql);
    }

    /**
     * Create default options
     */
    private function create_default_options() {
        $default_options = array(
            'wi_email_settings' => array(
                'from_name' => get_bloginfo('name'),
                'from_email' => get_option('admin_email'),
                'reply_to' => get_option('admin_email')
            ),
            'wi_invitation_settings' => array(
                'default_message' => __('نتشرف بحضوركم لحفل زفافنا', WEDDING_INVITATIONS_TEXT_DOMAIN),
                'enable_plus_one' => true,
                'require_rsvp' => true
            )
        );

        foreach ($default_options as $option_name => $option_value) {
            add_option($option_name, $option_value);
        }
    }

    /**
     * Drop database tables
     */
    private static function drop_database_tables() {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . 'wi_events',
            $wpdb->prefix . 'wi_guests', 
            $wpdb->prefix . 'wi_rsvp_responses'
        );

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }

    /**
     * Remove plugin options
     */
    private static function remove_plugin_options() {
        $options = array(
            'wi_email_settings',
            'wi_invitation_settings',
            'wi_db_version'
        );

        foreach ($options as $option) {
            delete_option($option);
        }
    }

    /**
     * Cleanup uploaded files
     */
    private static function cleanup_uploaded_files() {
        $upload_dir = wp_upload_dir();
        $plugin_upload_dir = $upload_dir['basedir'] . '/wedding-invitations/';

        if (is_dir($plugin_upload_dir)) {
            $files = glob($plugin_upload_dir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($plugin_upload_dir);
        }
    }
}

// Initialize plugin
add_action('plugins_loaded', array('Wedding_Invitations_Plugin', 'get_instance'));

// Compatibility check
register_activation_hook(__FILE__, 'wi_check_requirements');

function wi_check_requirements() {
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('هذه الإضافة تتطلب PHP 7.4 أو أحدث. النسخة الحالية: ' . PHP_VERSION, WEDDING_INVITATIONS_TEXT_DOMAIN));
    }

    if (version_compare(get_bloginfo('version'), '5.0', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('هذه الإضافة تتطلب WordPress 5.0 أو أحدث.', WEDDING_INVITATIONS_TEXT_DOMAIN));
    }
}
