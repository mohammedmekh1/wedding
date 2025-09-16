<?php
/**
 * Public functionality class
 */

if (!defined('ABSPATH')) {
    exit;
}

class WI_Public {

    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function init() {
        add_shortcode('wedding_invitation', array($this, 'display_invitation_shortcode'));
        add_shortcode('rsvp_form', array($this, 'display_rsvp_form_shortcode'));
    }

    public function enqueue_scripts() {
        wp_enqueue_style('wi-public', WEDDING_INVITATIONS_PLUGIN_URL . 'public/css/style.css');
        wp_enqueue_script('wi-public', WEDDING_INVITATIONS_PLUGIN_URL . 'public/js/script.js', array('jquery'));

        wp_localize_script('wi-public', 'wi_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wi_public_nonce')
        ));
    }

    public function display_invitation_shortcode($atts) {
        $atts = shortcode_atts(array(
            'code' => '',
            'template' => 'default'
        ), $atts);

        if (empty($atts['code'])) {
            return __('كود الدعوة مطلوب', WEDDING_INVITATIONS_TEXT_DOMAIN);
        }

        ob_start();
        include WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/invitation-page.php';
        return ob_get_clean();
    }

    public function display_rsvp_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'guest_id' => 0
        ), $atts);

        ob_start();
        ?>
        <div class="wi-rsvp-form">
            <form id="wi-rsvp-form" method="post">
                <h3><?php _e('الرد على الدعوة', WEDDING_INVITATIONS_TEXT_DOMAIN); ?></h3>

                <p>
                    <label>
                        <input type="radio" name="rsvp_response" value="attending" required>
                        <?php _e('سأحضر', WEDDING_INVITATIONS_TEXT_DOMAIN); ?>
                    </label>
                </p>

                <p>
                    <label>
                        <input type="radio" name="rsvp_response" value="not_attending" required>
                        <?php _e('لن أتمكن من الحضور', WEDDING_INVITATIONS_TEXT_DOMAIN); ?>
                    </label>
                </p>

                <p>
                    <label><?php _e('رسالة (اختيارية)', WEDDING_INVITATIONS_TEXT_DOMAIN); ?></label>
                    <textarea name="rsvp_message" rows="3"></textarea>
                </p>

                <p>
                    <button type="submit"><?php _e('إرسال الرد', WEDDING_INVITATIONS_TEXT_DOMAIN); ?></button>
                </p>

                <?php wp_nonce_field('wi_rsvp_submit', 'wi_rsvp_nonce'); ?>
                <input type="hidden" name="guest_id" value="<?php echo intval($atts['guest_id']); ?>">
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}

new WI_Public();
