<?php
/**
 * Authentication Module - Name Preservation Fix
 *
 * @package ALMOE_ID_OAuth
 */

namespace ALMOE_ID_OAuth;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Authentication class
 * Handles OAuth flow and user authentication
 */
class Auth {
    /**
     * The single instance of the class
     *
     * @var Auth
     */
    protected static $_instance = null;
    
    /**
     * Transient names
     */
    const STATE_PREFIX = 'almoe_id_oauth_state_';
    const CODE_VERIFIER_PREFIX = 'almoe_id_oauth_cv_';
    
    /**
     * Transient expiration time (10 minutes)
     */
    const EXPIRATION = 600;
    
    /**
     * Track if auth URL is already generated in this request
     * 
     * @var string|null
     */
    private $current_auth_url = null;
    private $current_state = null;
    
    /**
     * Debug levels
     * 0 = off, 1 = basic, 2 = verbose
     */
    const DEBUG_LEVEL = 0; // Temporary increase debug level

    /**
     * Get the single instance
     *
     * @return Auth
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
        // Nothing needed here for now
    }

    /**
     * Get OAuth authorization URL
     *
     * @return string Authorization URL
     */
    public function get_authorization_url() {
        // Return cached URL if already generated during this request
        if ($this->current_auth_url !== null) {
            return $this->current_auth_url;
        }
        
        // Get ALMOE ID server settings
        $client_id = get_option('almoe_id_oauth_client_id');
        $auth_endpoint = get_option('almoe_id_oauth_auth_endpoint', 'https://id.masjidalmubarokah.com/oauth/authorize');
        
        // Generate state and code verifier
        $state = $this->generate_state();
        $code_verifier = $this->generate_code_verifier();
        $code_challenge = $this->generate_code_challenge($code_verifier);
        
        // Store code verifier with state as key for later retrieval
        $this->store_data_for_state($state, $code_verifier);
        
        // Remember current state
        $this->current_state = $state;
        
        // Set redirect URI (must match one registered in ALMOE ID server)
        $redirect_uri = site_url('almoe-id-callback');
        
        $this->log_message("Authorization URL generated. State: $state", 1);
        
        // Build authorization URL with PKCE
        $this->current_auth_url = add_query_arg(array(
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => 'view-user',
            'state' => $state,
            'code_challenge' => $code_challenge,
            'code_challenge_method' => 'S256',
        ), $auth_endpoint);
        
        return $this->current_auth_url;
    }
    
    // [Other methods remain unchanged]
    
    /**
 * Authenticate or create WordPress user based on ALMOE ID data
 *
 * @param array $user_info User info from ALMOE ID server
 * @return int|WP_Error User ID or error
 */
public function authenticate_user($user_info) {
    // Log complete user info for debugging
    $this->log_message("Raw user info from ALMOE ID: " . print_r($user_info, true), 2);
    
    // Check if we have required user data
    if (!isset($user_info['email']) || !isset($user_info['id'])) {
        $this->log_message("Error: Missing required user data", 1);
        return new \WP_Error('missing_data', __('Incomplete user data received from ALMOE ID server.', 'almoe-id-oauth'));
    }
    
    $email = sanitize_email($user_info['email']);
    $almoe_id = sanitize_text_field($user_info['id']);
    
    // Prepare name data
    $display_name = '';
    $first_name = '';
    $last_name = '';
    
    // Get display name
    if (!empty($user_info['name'])) {
        $display_name = sanitize_text_field($user_info['name']);
        $this->log_message("Name from ALMOE ID: $display_name", 1);
    }
    

    // Get first name and last name
    if (!empty($user_info['given_name'])) {
        $first_name = sanitize_text_field($user_info['given_name']);
    }
    if (!empty($user_info['family_name'])) {
        $last_name = sanitize_text_field($user_info['family_name']);
    }
    
    // If no given_name/family_name, extract from full name
    if (empty($first_name) && empty($last_name) && !empty($display_name)) {
        $name_parts = explode(' ', $display_name);
        if (count($name_parts) > 1) {
            $first_name = $name_parts[0];
            $last_name = implode(' ', array_slice($name_parts, 1));
            $this->log_message("Split name into first_name: $first_name, last_name: $last_name", 1);
        } else {
            $first_name = $display_name;
            $this->log_message("Using full name as first_name: $first_name", 1);
        }
    }
    
    // Check if user already exists by ALMOE ID
    $users = get_users(array(
        'meta_key' => 'almoe_id_oauth_id',
        'meta_value' => $almoe_id,
        'number' => 1,
    ));
    
    if (!empty($users)) {
        // User exists, update if needed and return user ID
        $this->log_message("Existing user found by ALMOE ID: " . $users[0]->ID, 1);
        
        $update_data = array('ID' => $users[0]->ID);
        $should_update = false;
        
        // Update display name if different
        if (!empty($display_name) && $users[0]->display_name !== $display_name) {
            $update_data['display_name'] = $display_name;
            $should_update = true;
        }
        
        // Update first name if it's empty or different
        if (!empty($first_name) && (empty($users[0]->first_name) || $users[0]->first_name !== $first_name)) {
            $update_data['first_name'] = $first_name;
            $should_update = true;
        }
        
        // Update last name if it's empty or different
        if (!empty($last_name) && (empty($users[0]->last_name) || $users[0]->last_name !== $last_name)) {
            $update_data['last_name'] = $last_name;
            $should_update = true;
        }
        
        // Update user if needed
        if ($should_update) {
            $this->log_message("Updating existing user information", 1);
            wp_update_user($update_data);
        }
        
        return $users[0]->ID;
    }
    
    // Check if user exists by email
    $user = get_user_by('email', $email);
    
    if ($user) {
        // Link existing user with ALMOE ID
        $this->log_message("Existing user found by email: " . $user->ID, 1);
        update_user_meta($user->ID, 'almoe_id_oauth_id', $almoe_id);
        
        // Update user data if needed
        $update_data = array('ID' => $user->ID);
        $should_update = false;
        
        // Update display name if different
        if (!empty($display_name) && $user->display_name !== $display_name) {
            $update_data['display_name'] = $display_name;
            $should_update = true;
        }
        
        // Update first name if it's empty or different
        if (!empty($first_name) && (empty($user->first_name) || $user->first_name !== $first_name)) {
            $update_data['first_name'] = $first_name;
            $should_update = true;
        }
        
        // Update last name if it's empty or different
        if (!empty($last_name) && (empty($user->last_name) || $user->last_name !== $last_name)) {
            $update_data['last_name'] = $last_name;
            $should_update = true;
        }
        
        // Update user if needed
        if ($should_update) {
            $this->log_message("Updating existing user information", 1);
            wp_update_user($update_data);
        }
        
        return $user->ID;
    }
    
    // No existing user, create a new one if auto-registration is enabled
    $auto_register = get_option('almoe_id_oauth_auto_register', '1');
    
    if ($auto_register !== '1') {
        $this->log_message("Auto-registration is disabled", 1);
        return new \WP_Error('registration_disabled', __('Auto-registration is disabled. Please contact the administrator.', 'almoe-id-oauth'));
    }
    
    // Create username from available data
    $username = $this->generate_unique_username($user_info);
    
    $this->log_message("Creating new user with username: $username", 1);
    
    // Prepare user data
    $user_data = array(
        'user_login' => $username,
        'user_pass' => wp_generate_password(24),
        'user_email' => $email,
        'user_registered' => current_time('mysql'),
        'role' => get_option('almoe_id_oauth_default_role', 'subscriber'),
    );
    
    // Add display name if available
    if (!empty($display_name)) {
        $user_data['display_name'] = $display_name;
    }
    
    // Add first name and last name if available
    if (!empty($first_name)) {
        $user_data['first_name'] = $first_name;
    }
    if (!empty($last_name)) {
        $user_data['last_name'] = $last_name;
    }
    
    // Log user data before insertion
    $this->log_message("User data for insertion: " . print_r($user_data, true), 2);
    
    // Create new user
    $user_id = wp_insert_user($user_data);
    
    if (is_wp_error($user_id)) {
        $this->log_message("Error creating user: " . $user_id->get_error_message(), 1);
        return $user_id;
    }
    
    // Store ALMOE ID with user
    update_user_meta($user_id, 'almoe_id_oauth_id', $almoe_id);
    
    // Store additional metadata
    if (!empty($user_info['profile_picture'])) {
        update_user_meta($user_id, 'almoe_id_oauth_profile_picture', esc_url_raw($user_info['profile_picture']));
    }
    
    if (!empty($user_info['username'])) {
        update_user_meta($user_id, 'almoe_id_oauth_username', sanitize_text_field($user_info['username']));
    }
    
    // Store raw user data for debugging if needed
    if (self::DEBUG_LEVEL >= 2) {
        update_user_meta($user_id, 'almoe_id_oauth_raw_data', wp_json_encode($user_info));
    }
    
    // Fire action for other plugins/themes
    do_action('almoe_id_oauth_user_created', $user_id, $user_info);
    
    $this->log_message("New user created with ID: $user_id", 1);
    return $user_id;
}

/**
 * Generate a unique username from user info
 *
 * @param array $user_info User info from ALMOE ID
 * @return string Unique username
 */
private function generate_unique_username($user_info) {
    $username = '';
    
    // Try different sources for username in order of preference
    if (!empty($user_info['username'])) {
        $username = sanitize_user($user_info['username'], true);
        $this->log_message("Using ALMOE ID username field: $username", 1);
    } elseif (!empty($user_info['name'])) {
        $username = sanitize_user($user_info['name'], true);
        $this->log_message("Using name as username: $username", 1);
    } else {
        // Use email prefix as fallback
        $email = $user_info['email'];
        $username = sanitize_user(substr($email, 0, strpos($email, '@')), true);
        $this->log_message("Using email prefix as username: $username", 1);
    }
    
    // Remove invalid characters
    $username = preg_replace('/[^a-z0-9_\-\.]/i', '', $username);
    
    // Ensure we have something
    if (empty($username)) {
        $username = 'user_' . wp_rand(1000, 9999);
        $this->log_message("Generated random username: $username", 1);
    }
    
    // Ensure username is unique
    $base_username = $username;
    $suffix = 1;
    
    while (username_exists($username)) {
        $username = $base_username . $suffix;
        $suffix++;
    }
    
    return $username;
}
    
    /**
     * Log debug messages if debug mode is enabled
     *
     * @param string $message Message to log
     * @param int $level Debug level required to show this message
     */
    private function log_message($message, $level = 1) {
        // Only log if debug level is high enough
        if (self::DEBUG_LEVEL >= $level) {
            error_log('ALMOE ID OAuth: ' . $message);
        }
    }
    
    // [Other methods remain unchanged - code verifier, state management, etc.]

    /**
     * Generate a random state parameter for CSRF protection
     *
     * @return string Random state string
     */
    private function generate_state() {
        return bin2hex(random_bytes(16));
    }
    
    /**
     * Store data associated with a state
     * 
     * @param string $state State value
     * @param string $code_verifier Code verifier to store
     */
    private function store_data_for_state($state, $code_verifier) {
        // Use long-term transient to store code verifier indexed by state
        set_transient(self::CODE_VERIFIER_PREFIX . $state, $code_verifier, self::EXPIRATION);
        $this->log_message("Stored code verifier for state: $state", 1);
    }
    
    /**
     * Verify the state parameter to prevent CSRF attacks
     *
     * @param string $state State parameter from OAuth callback
     * @return bool Whether state is valid
     */
    public function verify_state($state) {
        $this->log_message("Verifying state: $state", 1);
        
        // For state to be valid, we just need to have a code verifier stored for it
        $code_verifier = get_transient(self::CODE_VERIFIER_PREFIX . $state);
        
        $valid = $code_verifier !== false;
        $this->log_message("State validation result: " . ($valid ? 'valid' : 'invalid'), 1);
        
        // Don't delete the code verifier yet - we'll need it for the token request
        
        return $valid;
    }
    
    /**
     * Generate PKCE code verifier
     *
     * @return string Code verifier
     */
    private function generate_code_verifier() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Generate PKCE code challenge from verifier
     *
     * @param string $code_verifier Code verifier
     * @return string Code challenge
     */
    private function generate_code_challenge($code_verifier) {
        return rtrim(strtr(base64_encode(hash('sha256', $code_verifier, true)), '+/', '-_'), '=');
    }
    
    /**
     * Get code verifier for state
     *
     * @param string $state State parameter
     * @return string|bool Code verifier or false if not found
     */
    private function get_code_verifier_for_state($state) {
        $code_verifier = get_transient(self::CODE_VERIFIER_PREFIX . $state);
        
        if ($code_verifier !== false) {
            $this->log_message("Code verifier found for state: $state", 1);
            // Delete the transient since we won't need it again
            delete_transient(self::CODE_VERIFIER_PREFIX . $state);
        } else {
            $this->log_message("Code verifier not found for state: $state", 1);
        }
        
        return $code_verifier;
    }
    
    /**
     * Exchange authorization code for access token
     *
     * @param string $code Authorization code from OAuth server
     * @param string $state State parameter from callback
     * @return array|bool Token data or false on failure
     */
    public function get_token($code, $state) {
        $this->log_message("Getting token with code and state: $state", 1);
        
        // Get stored code verifier for this state
        $code_verifier = $this->get_code_verifier_for_state($state);
        if (!$code_verifier) {
            $this->log_message("Error: Code verifier not found for state: $state", 1);
            return false;
        }
        
        // Get ALMOE ID server settings
        $client_id = get_option('almoe_id_oauth_client_id');
        $client_secret = get_option('almoe_id_oauth_client_secret');
        $token_endpoint = get_option('almoe_id_oauth_token_endpoint', 'https://id.masjidalmubarokah.com/oauth/token');
        
        // Set redirect URI (must match the one used in authorization request)
        $redirect_uri = site_url('almoe-id-callback');
        
        $this->log_message("Making token request to: $token_endpoint", 1);
        
        // Request access token
        $response = wp_remote_post($token_endpoint, array(
            'body' => array(
                'grant_type' => 'authorization_code',
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri' => $redirect_uri,
                'code' => $code,
                'code_verifier' => $code_verifier,
            ),
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            $this->log_message("Token request error: " . $response->get_error_message(), 1);
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        $this->log_message("Token response code: $status_code", 1);
        
        // Log token response body at debug level 2 only (to avoid exposing sensitive data)
        if (self::DEBUG_LEVEL >= 2) {
            $this->log_message("Token response body: $body", 2);
        }
        
        if ($status_code !== 200) {
            return false;
        }
        
        $token_data = json_decode($body, true);
        
        if (!is_array($token_data) || !isset($token_data['access_token'])) {
            $this->log_message("Invalid token data format", 1);
            return false;
        }
        
        $this->log_message("Token successfully retrieved", 1);
        return $token_data;
    }
    
    /**
     * Clear all stored auth data
     * Useful for troubleshooting
     */
    public function clear_all_auth_data() {
        global $wpdb;
        
        // Find and delete all relevant transients
        $wpdb->query("
            DELETE FROM $wpdb->options 
            WHERE option_name LIKE '_transient_" . self::CODE_VERIFIER_PREFIX . "%' 
            OR option_name LIKE '_transient_timeout_" . self::CODE_VERIFIER_PREFIX . "%'
        ");
        
        $this->log_message("Cleared all stored auth data", 1);
    }
}