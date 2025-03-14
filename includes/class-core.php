<?php
/**
 * Core plugin class
 *
 * @package ALMOE_ID_OAuth
 */

namespace ALMOE_ID_OAuth;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The main plugin class
 */
class Core {
    /**
     * The single instance of the class
     *
     * @var Core
     */
    protected static $_instance = null;

    /**
     * The admin module instance
     *
     * @var Admin
     */
    public $admin;

    /**
     * The authentication module instance
     *
     * @var Auth
     */
    public $auth;

    /**
     * The API module instance
     *
     * @var Api
     */
    public $api;
    
    /**
     * Flag to track if rewrite rules have been added
     * 
     * @var bool
     */
    private static $rewrite_rules_added = false;

    /**
     * Debug mode
     * 0 = off, 1 = basic, 2 = verbose
     */
    const DEBUG_LEVEL = 0;

    /**
     * Main Plugin Instance
     *
     * Ensures only one instance of the plugin is loaded
     *
     * @return Core Main instance
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
    public function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Initialize plugin hooks
     */
    private function init_hooks() {
        // Register scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'register_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'register_admin_scripts'));
        
        // Add login button to WordPress login form
        add_action('login_form', array($this, 'add_login_button'));
        
        // Enqueue styles for login page
        add_action('login_enqueue_scripts', function() {
            wp_enqueue_style('almoe-id-oauth');
            wp_enqueue_script('almoe-id-oauth');
        });
        
        // Handle OAuth callback - separate approach for non-rewrite rule situations
        add_action('template_redirect', array($this, 'check_for_oauth_callback'), 5);
        
        // Add custom endpoint for OAuth callback - only once
        add_action('init', array($this, 'add_rewrite_rules'), 1);
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('parse_request', array($this, 'handle_custom_endpoints'), 1);
        
        // Add admin notice for permalink structure
        add_action('admin_notices', array($this, 'check_permalink_structure'));
        
        // Add action for logging
        add_action('almoe_id_oauth_log', array($this, 'log_message'), 10, 2);
    }

    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Load required classes
        $this->admin = Admin::get_instance();
        $this->auth = Auth::get_instance();
        $this->api = Api::get_instance();
        
        // Initialize shortcodes
        if (class_exists('\\ALMOE_ID_OAuth\\Shortcodes')) {
            Shortcodes::get_instance();
        }
        
        // Initialize utility functions
        if (class_exists('\\ALMOE_ID_OAuth\\Utilities')) {
            Utilities::init();
        }
    }

    /**
     * Register frontend scripts and styles
     */
    public function register_scripts() {
        // Register and enqueue frontend CSS
        wp_register_style(
            'almoe-id-oauth',
            ALMOE_ID_OAUTH_URL . 'public/css/almoe-id-oauth.css',
            array(),
            ALMOE_ID_OAUTH_VERSION
        );
        
        // Register and enqueue frontend JS
        wp_register_script(
            'almoe-id-oauth',
            ALMOE_ID_OAUTH_URL . 'public/js/almoe-id-oauth.js',
            array('jquery'),
            ALMOE_ID_OAUTH_VERSION,
            true
        );
        
        // Localize script with plugin data
        wp_localize_script('almoe-id-oauth', 'almoeIdOauth', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('almoe-id-oauth-nonce'),
            'redirectUrl' => site_url('almoe-id-callback'),
            'loginUrl' => wp_login_url(),
        ));
    }

    /**
     * Register admin scripts and styles
     */
    public function register_admin_scripts() {
        // Register and enqueue admin CSS
        wp_register_style(
            'almoe-id-oauth-admin',
            ALMOE_ID_OAUTH_URL . 'admin/css/almoe-id-oauth-admin.css',
            array(),
            ALMOE_ID_OAUTH_VERSION
        );
        
        // Register and enqueue admin JS
        wp_register_script(
            'almoe-id-oauth-admin',
            ALMOE_ID_OAUTH_URL . 'admin/js/almoe-id-oauth-admin.js',
            array('jquery'),
            ALMOE_ID_OAUTH_VERSION,
            true
        );
        
        // Localize script with admin data
        wp_localize_script('almoe-id-oauth-admin', 'almoeIdOauthAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('almoe-id-oauth-admin-nonce'),
        ));
    }

    /**
     * Add ALMOE ID login button to WordPress login form
     */
    public function add_login_button() {
        static $button_added = false;
        
        // Prevent multiple button additions
        if ($button_added) {
            return;
        }
        
        $this->log_message('Adding login button to form', 1);
        
        // Only add button if the feature is enabled in settings
        if (get_option('almoe_id_oauth_enabled', '1') !== '1') {
            return;
        }
        
        // Enqueue frontend styles for the login button
        wp_enqueue_style('almoe-id-oauth');
        wp_enqueue_script('almoe-id-oauth');
        
        // Get authorization URL
        $auth_url = $this->auth->get_authorization_url();
        
        // Include the login button template
        if (file_exists(ALMOE_ID_OAUTH_PATH . 'public/partials/login-button.php')) {
            include_once ALMOE_ID_OAUTH_PATH . 'public/partials/login-button.php';
        } else {
            $this->log_message("ERROR: Login button template not found at " . ALMOE_ID_OAUTH_PATH . 'public/partials/login-button.php', 1);
            
            // Fallback button if template is missing
            echo '<div style="margin: 20px 0; text-align: center;">';
            echo '<a href="' . esc_url($auth_url) . '" style="display: inline-block; padding: 10px 15px; background: linear-gradient(135deg, #0E5C67 0%, #0e6730 100%); color: white; text-decoration: none; border-radius: 4px;">';
            echo esc_html(get_option('almoe_id_oauth_button_text', __('Login with ALMOE ID', 'almoe-id-oauth')));
            echo '</a>';
            echo '</div>';
        }
        
        $button_added = true;
    }

    /**
     * Check permalink structure and show admin notice if not using pretty permalinks
     */
    public function check_permalink_structure() {
        if (!get_option('permalink_structure') && current_user_can('manage_options')) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php _e('ALMOE ID OAuth requires pretty permalinks to be enabled for the callback URL to work properly. Please go to', 'almoe-id-oauth'); ?>
                <a href="<?php echo admin_url('options-permalink.php'); ?>"><?php _e('Permalink Settings', 'almoe-id-oauth'); ?></a>
                <?php _e('and select any option other than "Plain".', 'almoe-id-oauth'); ?></p>
            </div>
            <?php
        }
    }

    /**
     * Set up rewrite rules for custom endpoints
     */
    public function add_rewrite_rules() {
        // Only add rewrite rules once per request
        if (self::$rewrite_rules_added) {
            return;
        }
        
        // Add rewrite endpoint and rule
        add_rewrite_endpoint('almoe-id-callback', EP_ROOT);
        
        add_rewrite_rule(
            '^almoe-id-callback/?$',
            'index.php?almoe_id_oauth_callback=1',
            'top'
        );
        
        $this->log_message("Rewrite rules added for almoe-id-callback", 1);
        
        // Mark as added
        self::$rewrite_rules_added = true;
    }

    /**
     * Add custom query vars
     *
     * @param array $query_vars WordPress query vars
     * @return array Modified query vars
     */
    public function add_query_vars($query_vars) {
        $query_vars[] = 'almoe_id_oauth_callback';
        return $query_vars;
    }

    /**
     * Check for OAuth callback in template_redirect
     * This works even if rewrite rules are not working
     */
    public function check_for_oauth_callback() {
        // Check if the current URL path is the callback path
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $callback_path = parse_url(site_url('almoe-id-callback'), PHP_URL_PATH);
        
        if (strpos($request_uri, $callback_path) !== false) {
            $this->log_message("OAuth callback detected via template_redirect: $request_uri", 1);
            $this->handle_oauth_callback();
            exit;
        }
    }

    /**
     * Handle custom endpoints via parse_request
     *
     * @param object $wp WordPress request object
     */
    public function handle_custom_endpoints($wp) {
        if (!empty($wp->query_vars['almoe_id_oauth_callback']) || 
            (isset($wp->request) && $wp->request == 'almoe-id-callback')) {
            $this->log_message("OAuth callback detected via parse_request", 1);
            $this->handle_oauth_callback();
            exit;
        }
    }

    /**
     * Handle OAuth callback
     */
    public function handle_oauth_callback() {
        $this->log_message("OAuth callback handler started", 1);
        
        // Check for required parameters
        if (!isset($_GET['code'])) {
            $this->log_message("Error: Missing 'code' parameter", 1);
            if (isset($_GET['error'])) {
                $this->log_message("OAuth Error: " . $_GET['error'] . " - " . (isset($_GET['error_description']) ? $_GET['error_description'] : 'No description'), 1);
                wp_die(
                    sprintf(
                        __('Authentication Error: %s. Please try again.', 'almoe-id-oauth'),
                        isset($_GET['error_description']) ? esc_html($_GET['error_description']) : esc_html($_GET['error'])
                    ),
                    __('Authentication Error', 'almoe-id-oauth')
                );
            } else {
                wp_die(__('Invalid authentication attempt. Missing authorization code.', 'almoe-id-oauth'), __('Authentication Error', 'almoe-id-oauth'));
            }
            return;
        }
        
        $code = $_GET['code'];
        
        // Check for state parameter
        if (!isset($_GET['state'])) {
            $this->log_message("Error: Missing 'state' parameter", 1);
            wp_die(__('Invalid authentication attempt. Missing state parameter.', 'almoe-id-oauth'), __('Authentication Error', 'almoe-id-oauth'));
            return;
        }
        
        $state = $_GET['state'];
        $this->log_message("Received code and state: $state", 1);
        
        // Verify state to prevent CSRF
        if (!$this->auth->verify_state($state)) {
            $this->log_message("State verification failed for state: $state", 1);
            wp_die(__('Invalid authentication attempt. Please try again.', 'almoe-id-oauth'), __('Authentication Error', 'almoe-id-oauth'));
            return;
        }
        
        $this->log_message("State verification passed. Exchanging code for token...", 1);
        
        // Exchange authorization code for access token
        $token_data = $this->auth->get_token($code, $state);
        
        if (!$token_data || isset($token_data['error'])) {
            $error_msg = isset($token_data['error_description']) ? $token_data['error_description'] : 'Unknown error';
            $this->log_message("Token exchange failed: $error_msg", 1);
            wp_die(__('Failed to authenticate with ALMOE ID. Please try again.', 'almoe-id-oauth'), __('Authentication Error', 'almoe-id-oauth'));
            return;
        }
        
        $this->log_message("Token received successfully. Getting user info...", 1);
        
        // Get user info from ALMOE ID server
        $user_info = $this->api->get_user_info($token_data['access_token']);
        
        if (!$user_info || isset($user_info['error'])) {
            $error_msg = isset($user_info['error_description']) ? $user_info['error_description'] : 'Unknown error';
            $this->log_message("User info retrieval failed: $error_msg", 1);
            wp_die(__('Failed to retrieve user information. Please try again.', 'almoe-id-oauth'), __('Authentication Error', 'almoe-id-oauth'));
            return;
        }
        
        $this->log_message("User info received. Authenticating user...", 1);
        
        // Authenticate user in WordPress
        $user_id = $this->auth->authenticate_user($user_info);
        
        if (is_wp_error($user_id)) {
            $this->log_message("User authentication failed: " . $user_id->get_error_message(), 1);
            wp_die($user_id->get_error_message(), __('Authentication Error', 'almoe-id-oauth'));
            return;
        }
        
        $this->log_message("User authenticated successfully with ID: $user_id. Setting auth cookie...", 1);
        
        // Log the user in
        wp_set_auth_cookie($user_id, true);
        
        // Update last login time
        update_user_meta($user_id, 'almoe_id_oauth_last_login', current_time('mysql'));
        
        // Log successful login
        if (function_exists('do_action')) {
            do_action('almoe_id_oauth_login_success', $user_id, $user_info);
        }
        
        $this->log_message("Login completed. Redirecting user...", 1);
        
        // Redirect to the appropriate page after login
        $redirect_to = isset($_GET['redirect_to']) ? esc_url_raw($_GET['redirect_to']) : admin_url();
        wp_safe_redirect($redirect_to);
        exit;
    }

    /**
     * Log messages based on debug level
     *
     * @param string $message Message to log
     * @param int $level Debug level required to show this message
     */
    public function log_message($message, $level = 1) {
        // Only log if debug level is high enough
        if (self::DEBUG_LEVEL >= $level) {
            error_log('ALMOE ID OAuth Core: ' . $message);
        }
    }
}