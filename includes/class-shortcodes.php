<?php
/**
 * Shortcodes Module
 *
 * @package ALMOE_ID_OAuth
 */

namespace ALMOE_ID_OAuth;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcodes class
 * Handles all plugin shortcodes for displaying elements on frontend
 */
class Shortcodes {
    /**
     * The single instance of the class
     *
     * @var Shortcodes
     */
    protected static $_instance = null;

    /**
     * Get the single instance
     *
     * @return Shortcodes
     */
    public static function get_instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->register_shortcodes();
    }

    /**
     * Register shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('almoe_login_button', array($this, 'login_button_shortcode'));
    }

    /**
     * Login button shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string Shortcode output
     */
    public function login_button_shortcode($atts) {
        // Only display button if OAuth is enabled
        if (get_option('almoe_id_oauth_enabled', '1') !== '1') {
            return '';
        }
        
        // Parse shortcode attributes
        $attributes = shortcode_atts(
            array(
                'text' => get_option('almoe_id_oauth_button_text', __('Login with ALMOE ID', 'almoe-id-oauth')),
                'redirect' => '',
                'class' => '',
                'size' => 'normal', // Options: small, normal, large
                'fullwidth' => 'no', // Options: yes, no
                'show_icon' => 'yes', // Options: yes, no
                'show_separator' => 'yes', // Options: yes, no
            ),
            $atts
        );
        
        // Sanitize attributes
        $text = sanitize_text_field($attributes['text']);
        $class = sanitize_html_class($attributes['class']);
        $size = in_array($attributes['size'], array('small', 'normal', 'large'), true) ? $attributes['size'] : 'normal';
        $fullwidth = $attributes['fullwidth'] === 'yes' ? ' style="max-width:100%;"' : '';
        $show_icon = $attributes['show_icon'] !== 'no';
        $show_separator = $attributes['show_separator'] !== 'no';
        
        // Set redirect URL if provided
        $redirect = '';
        if (!empty($attributes['redirect'])) {
            $redirect = esc_url_raw($attributes['redirect']);
        } elseif (is_singular()) {
            // Default redirect to current page
            $redirect = get_permalink();
        }
        
        // Enqueue required styles
        wp_enqueue_style('almoe-id-oauth');
        wp_enqueue_script('almoe-id-oauth');
        
        // Get authorization URL from Auth module
        $auth = Auth::get_instance();
        $auth_url = $auth->get_authorization_url();
        
        // Add redirect parameter if set
        if (!empty($redirect)) {
            $auth_url = add_query_arg('redirect_to', urlencode($redirect), $auth_url);
        }
        
        // Logo path
        $logo_url = ALMOE_ID_OAUTH_URL . 'assets/images/almoe-id-logo.png';
        
        // Generate button HTML
        ob_start();
        ?>
        <div class="almoe-id-login-container">
            <?php if ($show_separator) : ?>
            <div class="almoe-id-login-separator">
                <span><?php esc_html_e('OR', 'almoe-id-oauth'); ?></span>
            </div>
            <?php endif; ?>
            
            <a href="<?php echo esc_url($auth_url); ?>" class="almoe-id-login-button almoe-id-btn-<?php echo esc_attr($size); ?> <?php echo esc_attr($class); ?>"<?php echo $fullwidth; ?>>
                <?php if ($show_icon) : ?>
                <span class="almoe-id-icon">
                    <img src="<?php echo esc_url($logo_url); ?>" alt="ALMOE ID" width="24" height="24">
                </span>
                <?php endif; ?>
                <span class="almoe-id-button-text"><?php echo esc_html($text); ?></span>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }
}