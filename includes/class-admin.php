<?php
/**
 * Admin Module
 *
 * @package ALMOE_ID_OAuth
 */

namespace ALMOE_ID_OAuth;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin class
 * Handles WordPress admin interface and settings
 */
class Admin {
    /**
     * The single instance of the class
     *
     * @var Admin
     */
    protected static $_instance = null;

    /**
     * Get the single instance
     *
     * @return Admin
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
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_almoe_id_oauth_test_connection', array($this, 'ajax_test_connection'));
        add_action('wp_ajax_almoe_id_oauth_auto_configure', array($this, 'ajax_auto_configure'));
    }

    /**
     * Add menu items
     */
    public function add_admin_menu() {
        // Main menu item
        add_menu_page(
            __('ALMOE ID OAuth', 'almoe-id-oauth'),
            __('ALMOE ID OAuth', 'almoe-id-oauth'),
            'manage_options',
            'almoe-id-oauth',
            array($this, 'settings_page'),
            'dashicons-lock',
            81
        );
        
        // Settings submenu
        add_submenu_page(
            'almoe-id-oauth',
            __('Settings', 'almoe-id-oauth'),
            __('Settings', 'almoe-id-oauth'),
            'manage_options',
            'almoe-id-oauth',
            array($this, 'settings_page')
        );
        
        // User Mapping submenu
        add_submenu_page(
            'almoe-id-oauth',
            __('User Mapping', 'almoe-id-oauth'),
            __('User Mapping', 'almoe-id-oauth'),
            'manage_options',
            'almoe-id-oauth-users',
            array($this, 'user_mapping_page')
        );
        
        // Logs submenu
        add_submenu_page(
            'almoe-id-oauth',
            __('Logs', 'almoe-id-oauth'),
            __('Logs', 'almoe-id-oauth'),
            'manage_options',
            'almoe-id-oauth-logs',
            array($this, 'logs_page')
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        // Register settings
        register_setting('almoe_id_oauth_settings', 'almoe_id_oauth_enabled', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '1',
        ));
        
        register_setting('almoe_id_oauth_settings', 'almoe_id_oauth_client_id', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ));
        
        register_setting('almoe_id_oauth_settings', 'almoe_id_oauth_client_secret', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ));
        
        register_setting('almoe_id_oauth_settings', 'almoe_id_oauth_auth_endpoint', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => 'https://id.masjidalmubarokah.com/oauth/authorize',
        ));
        
        register_setting('almoe_id_oauth_settings', 'almoe_id_oauth_token_endpoint', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => 'https://id.masjidalmubarokah.com/oauth/token',
        ));
        
        register_setting('almoe_id_oauth_settings', 'almoe_id_oauth_userinfo_endpoint', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => 'https://id.masjidalmubarokah.com/api/user',
        ));
        
        register_setting('almoe_id_oauth_settings', 'almoe_id_oauth_discovery_url', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => 'https://id.masjidalmubarokah.com/.well-known/openid-configuration',
        ));
        
        register_setting('almoe_id_oauth_settings', 'almoe_id_oauth_auto_register', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '1',
        ));
        
        register_setting('almoe_id_oauth_settings', 'almoe_id_oauth_default_role', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'subscriber',
        ));
        
        register_setting('almoe_id_oauth_settings', 'almoe_id_oauth_button_text', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => __('Login with ALMOE ID', 'almoe-id-oauth'),
        ));
        
        // Add settings sections
        add_settings_section(
            'almoe_id_oauth_general_section',
            __('General Settings', 'almoe-id-oauth'),
            array($this, 'general_section_callback'),
            'almoe_id_oauth_settings'
        );
        
        add_settings_section(
            'almoe_id_oauth_connection_section',
            __('Connection Settings', 'almoe-id-oauth'),
            array($this, 'connection_section_callback'),
            'almoe_id_oauth_settings'
        );
        
        add_settings_section(
            'almoe_id_oauth_user_section',
            __('User Settings', 'almoe-id-oauth'),
            array($this, 'user_section_callback'),
            'almoe_id_oauth_settings'
        );
        
        add_settings_section(
            'almoe_id_oauth_appearance_section',
            __('Appearance Settings', 'almoe-id-oauth'),
            array($this, 'appearance_section_callback'),
            'almoe_id_oauth_settings'
        );
        
        // Add settings fields
        add_settings_field(
            'almoe_id_oauth_enabled',
            __('Enable ALMOE ID Login', 'almoe-id-oauth'),
            array($this, 'enabled_field_callback'),
            'almoe_id_oauth_settings',
            'almoe_id_oauth_general_section'
        );
        
        add_settings_field(
            'almoe_id_oauth_client_id',
            __('Client ID', 'almoe-id-oauth'),
            array($this, 'client_id_field_callback'),
            'almoe_id_oauth_settings',
            'almoe_id_oauth_connection_section'
        );
        
        add_settings_field(
            'almoe_id_oauth_client_secret',
            __('Client Secret', 'almoe-id-oauth'),
            array($this, 'client_secret_field_callback'),
            'almoe_id_oauth_settings',
            'almoe_id_oauth_connection_section'
        );
        
        add_settings_field(
            'almoe_id_oauth_endpoints',
            __('OAuth Endpoints', 'almoe-id-oauth'),
            array($this, 'endpoints_field_callback'),
            'almoe_id_oauth_settings',
            'almoe_id_oauth_connection_section'
        );
        
        add_settings_field(
            'almoe_id_oauth_auto_register',
            __('Auto Register Users', 'almoe-id-oauth'),
            array($this, 'auto_register_field_callback'),
            'almoe_id_oauth_settings',
            'almoe_id_oauth_user_section'
        );
        
        add_settings_field(
            'almoe_id_oauth_default_role',
            __('Default User Role', 'almoe-id-oauth'),
            array($this, 'default_role_field_callback'),
            'almoe_id_oauth_settings',
            'almoe_id_oauth_user_section'
        );
        
        add_settings_field(
            'almoe_id_oauth_button_text',
            __('Button Text', 'almoe-id-oauth'),
            array($this, 'button_text_field_callback'),
            'almoe_id_oauth_settings',
            'almoe_id_oauth_appearance_section'
        );
    }

    /**
     * Enqueue admin styles and scripts
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        // Only load on plugin admin pages
        if (strpos($hook, 'almoe-id-oauth') === false) {
            return;
        }
        
        wp_enqueue_style('almoe-id-oauth-admin');
        wp_enqueue_script('almoe-id-oauth-admin');
    }

    /**
     * Settings page callback
     */
    public function settings_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Show settings saved message if settings were just updated
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'almoe_id_oauth_messages',
                'almoe_id_oauth_message',
                __('Settings Saved', 'almoe-id-oauth'),
                'updated'
            );
        }
        
        // Include settings page template
        include_once ALMOE_ID_OAUTH_PATH . 'admin/partials/settings-page.php';
    }

    /**
     * User mapping page callback
     */
    public function user_mapping_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Include user mapping page template
        include_once ALMOE_ID_OAUTH_PATH . 'admin/partials/user-mapping-page.php';
    }

    /**
     * Logs page callback
     */
    public function logs_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Include logs page template
        include_once ALMOE_ID_OAUTH_PATH . 'admin/partials/logs-page.php';
    }

    /**
     * General section callback
     */
    public function general_section_callback() {
        echo '<p>' . esc_html__('Configure general settings for ALMOE ID OAuth integration.', 'almoe-id-oauth') . '</p>';
    }

    /**
 * Connection section callback
 */
public function connection_section_callback() {
    echo '<p>' . esc_html__('Configure ALMOE ID OAuth server connection settings.', 'almoe-id-oauth') . '</p>';
    echo '<div class="almoe-id-oauth-connection-tools">';
    echo '<button type="button" id="almoe-id-oauth-test-connection" class="button button-secondary">' . esc_html__('Test Connection', 'almoe-id-oauth') . '</button>';
    echo '<div id="almoe-id-oauth-connection-status"></div>';
    echo '</div>';
    echo '<p><em>' . esc_html__('Endpoint URLs are predefined for exclusive integration with ALMOE ID server.', 'almoe-id-oauth') . '</em></p>';
}

    /**
     * User section callback
     */
    public function user_section_callback() {
        echo '<p>' . esc_html__('Configure how users are created and managed.', 'almoe-id-oauth') . '</p>';
    }

    /**
     * Appearance section callback
     */
    public function appearance_section_callback() {
        echo '<p>' . esc_html__('Configure the appearance of the ALMOE ID login button.', 'almoe-id-oauth') . '</p>';
    }

    /**
     * Enable field callback
     */
    public function enabled_field_callback() {
        $enabled = get_option('almoe_id_oauth_enabled', '1');
        ?>
        <label for="almoe_id_oauth_enabled">
            <input type="checkbox" id="almoe_id_oauth_enabled" name="almoe_id_oauth_enabled" value="1" <?php checked('1', $enabled); ?>>
            <?php esc_html_e('Enable ALMOE ID OAuth authentication', 'almoe-id-oauth'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('Enable or disable ALMOE ID authentication for this site.', 'almoe-id-oauth'); ?>
        </p>
        <?php
    }

    /**
     * Client ID field callback
     */
    public function client_id_field_callback() {
        $client_id = get_option('almoe_id_oauth_client_id', '');
        ?>
        <input type="text" id="almoe_id_oauth_client_id" name="almoe_id_oauth_client_id" value="<?php echo esc_attr($client_id); ?>" class="regular-text">
        <p class="description">
            <?php esc_html_e('Client ID provided by ALMOE ID server.', 'almoe-id-oauth'); ?>
        </p>
        <?php
    }

    /**
     * Client Secret field callback
     */
    public function client_secret_field_callback() {
        $client_secret = get_option('almoe_id_oauth_client_secret', '');
        ?>
        <input type="password" id="almoe_id_oauth_client_secret" name="almoe_id_oauth_client_secret" value="<?php echo esc_attr($client_secret); ?>" class="regular-text">
        <p class="description">
            <?php esc_html_e('Client Secret provided by ALMOE ID server.', 'almoe-id-oauth'); ?>
        </p>
        <?php
    }

    /**
 * Endpoints field callback
 */
public function endpoints_field_callback() {
    $auth_endpoint = get_option('almoe_id_oauth_auth_endpoint', 'https://id.masjidalmubarokah.com/oauth/authorize');
    $token_endpoint = get_option('almoe_id_oauth_token_endpoint', 'https://id.masjidalmubarokah.com/oauth/token');
    $userinfo_endpoint = get_option('almoe_id_oauth_userinfo_endpoint', 'https://id.masjidalmubarokah.com/api/user');
    
    ?>
    <div class="almoe-id-oauth-endpoint-field">
        <label for="almoe_id_oauth_auth_endpoint"><?php esc_html_e('Authorization Endpoint', 'almoe-id-oauth'); ?>:</label>
        <input type="url" id="almoe_id_oauth_auth_endpoint" name="almoe_id_oauth_auth_endpoint" value="<?php echo esc_attr($auth_endpoint); ?>" class="regular-text almoe-id-readonly" readonly>
    </div>
    
    <div class="almoe-id-oauth-endpoint-field">
        <label for="almoe_id_oauth_token_endpoint"><?php esc_html_e('Token Endpoint', 'almoe-id-oauth'); ?>:</label>
        <input type="url" id="almoe_id_oauth_token_endpoint" name="almoe_id_oauth_token_endpoint" value="<?php echo esc_attr($token_endpoint); ?>" class="regular-text almoe-id-readonly" readonly>
    </div>
    
    <div class="almoe-id-oauth-endpoint-field">
        <label for="almoe_id_oauth_userinfo_endpoint"><?php esc_html_e('User Info Endpoint', 'almoe-id-oauth'); ?>:</label>
        <input type="url" id="almoe_id_oauth_userinfo_endpoint" name="almoe_id_oauth_userinfo_endpoint" value="<?php echo esc_attr($userinfo_endpoint); ?>" class="regular-text almoe-id-readonly" readonly>
    </div>
    
    
    <p class="description">
        <?php esc_html_e('OAuth endpoints for ALMOE ID server are exclusively configured for your integration.', 'almoe-id-oauth'); ?>
    </p>
    <?php
}

    /**
     * Auto register field callback
     */
    public function auto_register_field_callback() {
        $auto_register = get_option('almoe_id_oauth_auto_register', '1');
        ?>
        <label for="almoe_id_oauth_auto_register">
            <input type="checkbox" id="almoe_id_oauth_auto_register" name="almoe_id_oauth_auto_register" value="1" <?php checked('1', $auto_register); ?>>
            <?php esc_html_e('Automatically register new users', 'almoe-id-oauth'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('If enabled, new users will be automatically registered when they authenticate with ALMOE ID.', 'almoe-id-oauth'); ?>
        </p>
        <?php
    }

    /**
     * Default role field callback
     */
    public function default_role_field_callback() {
        $default_role = get_option('almoe_id_oauth_default_role', 'subscriber');
        $roles = get_editable_roles();
        ?>
        <select id="almoe_id_oauth_default_role" name="almoe_id_oauth_default_role">
            <?php foreach ($roles as $role_id => $role_info) : ?>
                <option value="<?php echo esc_attr($role_id); ?>" <?php selected($default_role, $role_id); ?>>
                    <?php echo esc_html(translate_user_role($role_info['name'])); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php esc_html_e('Default role assigned to new users registered through ALMOE ID OAuth.', 'almoe-id-oauth'); ?>
        </p>
        <?php
    }

    /**
     * Button text field callback
     */
    public function button_text_field_callback() {
        $button_text = get_option('almoe_id_oauth_button_text', __('Login with ALMOE ID', 'almoe-id-oauth'));
        ?>
        <input type="text" id="almoe_id_oauth_button_text" name="almoe_id_oauth_button_text" value="<?php echo esc_attr($button_text); ?>" class="regular-text">
        <p class="description">
            <?php esc_html_e('Text displayed on the ALMOE ID login button.', 'almoe-id-oauth'); ?>
        </p>
        <div class="almoe-id-oauth-button-preview">
            <h4><?php esc_html_e('Button Preview', 'almoe-id-oauth'); ?></h4>
            <div class="almoe-id-login-button">
                <button type="button" id="almoe-id-oauth-button-preview"><?php echo esc_html($button_text); ?></button>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX handler for testing connection
     */
    public function ajax_test_connection() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'almoe-id-oauth')));
        }
        
        // Verify nonce
        check_ajax_referer('almoe-id-oauth-admin-nonce', 'nonce');
        
        // Get client credentials
        $client_id = get_option('almoe_id_oauth_client_id', '');
        $client_secret = get_option('almoe_id_oauth_client_secret', '');
        
        if (empty($client_id) || empty($client_secret)) {
            wp_send_json_error(array('message' => __('Client ID and Client Secret are required.', 'almoe-id-oauth')));
        }
        
        // Validate credentials with ALMOE ID server
        $api = Api::get_instance();
        $is_valid = $api->validate_client_credentials($client_id, $client_secret);
        
        if ($is_valid) {
            wp_send_json_success(array('message' => __('Connection successful! Your client credentials are valid.', 'almoe-id-oauth')));
        } else {
            wp_send_json_error(array('message' => __('Connection failed. Please check your client credentials and server settings.', 'almoe-id-oauth')));
        }
    }

    /**
 * AJAX handler for auto-configuring endpoints
 */
public function ajax_auto_configure() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Permission denied.', 'almoe-id-oauth')));
    }
    
    // Verify nonce
    check_ajax_referer('almoe-id-oauth-admin-nonce', 'nonce');
    
    // Auto-configure using discovery URL
    $api = Api::get_instance();
    $success = $api->auto_configure();
    
    if ($success) {
        // Reload the page to show the updated endpoints
        wp_send_json_success(array(
            'message' => __('Endpoints successfully configured from discovery document.', 'almoe-id-oauth'),
            'reload' => true
        ));
    } else {
        wp_send_json_error(array('message' => __('Failed to auto-configure endpoints. Please check the discovery URL.', 'almoe-id-oauth')));
    }
}
}