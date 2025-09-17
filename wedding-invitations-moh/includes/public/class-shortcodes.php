<?php
/**
 * Shortcodes Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class WI_Shortcodes {

    /**
     * Initialize shortcodes
     */
    public static function init() {
        add_shortcode('wedding_invitation', array(__CLASS__, 'display_invitation'));
        add_shortcode('rsvp_form', array(__CLASS__, 'display_rsvp_form'));
    }

    /**
     * Display the invitation via shortcode
     */
    public static function display_invitation($atts) {
        $atts = shortcode_atts(array(
            'code' => '',
            'template' => 'default'
        ), $atts, 'wedding_invitation');

        if (empty($atts['code'])) {
            return __('كود الدعوة مطلوب', 'wedding-invitations');
        }

        // Pass attributes to the template
        $template_path = WEDDING_INVITATIONS_PLUGIN_PATH . 'includes/public/views/invitation.php';

        ob_start();
        if (file_exists($template_path)) {
            // Make $atts available in the template
            extract($atts);
            include $template_path;
        } else {
            echo "Invitation template not found.";
        }
        return ob_get_clean();
    }

    /**
     * Display the RSVP form via shortcode
     */
    public static function display_rsvp_form($atts) {
        $atts = shortcode_atts(array(
            'guest_id' => 0
        ), $atts, 'rsvp_form');

        ob_start();
        ?>
        <div class="wi-rsvp-form">
            <form id="wi-rsvp-form-<?php echo esc_attr($atts['guest_id']); ?>" method="post">
                <h3><?php _e('الرد على الدعوة', 'wedding-invitations'); ?></h3>

                <p>
                    <label>
                        <input type="radio" name="rsvp_response" value="attending" required>
                        <?php _e('سأحضر', 'wedding-invitations'); ?>
                    </label>
                </p>

                <p>
                    <label>
                        <input type="radio" name="rsvp_response" value="not_attending" required>
                        <?php _e('لن أتمكن من الحضور', 'wedding-invitations'); ?>
                    </label>
                </p>

                <p>
                    <label for="rsvp_message_<?php echo esc_attr($atts['guest_id']); ?>"><?php _e('رسالة (اختيارية)', 'wedding-invitations'); ?></label>
                    <textarea id="rsvp_message_<?php echo esc_attr($atts['guest_id']); ?>" name="rsvp_message" rows="3"></textarea>
                </p>

                <p>
                    <button type="submit"><?php _e('إرسال الرد', 'wedding-invitations'); ?></button>
                </p>

                <?php wp_nonce_field('wi_rsvp_submit', 'wi_rsvp_nonce'); ?>
                <input type="hidden" name="guest_id" value="<?php echo intval($atts['guest_id']); ?>">
                <input type="hidden" name="action" value="wi_handle_rsvp">
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}
