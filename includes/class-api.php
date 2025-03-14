<?php
/**
 * API Module
 *
 * @package ALMOE_ID_OAuth
 */

namespace ALMOE_ID_OAuth;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * API class
 * Handles communication with ALMOE ID API
 */
class Api {
    /**
     * The single instance of the class
     *
     * @var Api
     */
    protected static $_instance = null;
    
    /**
     * Debug levels
     * 0 = off, 1 = basic, 2 = verbose
     */
    const DEBUG_LEVEL = 0;

    /**
     * Get the single instance
     *
     * @return Api
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
     * Get user information from ALMOE ID server
     *
     * @param string $access_token Access token from OAuth flow
     * @return array|bool User info or false on failure
     */
    public function get_user_info($access_token) {
        // Only log first few characters of token for security
        $token_prefix = substr($access_token, 0, 8) . '...';
        $this->log_message("Getting user info with access token: $token_prefix", 1);
        
        // Get ALMOE ID server userinfo endpoint
        $userinfo_endpoint = get_option('almoe_id_oauth_userinfo_endpoint', 'https://id.masjidalmubarokah.com/api/user');
        
        $this->log_message("User info endpoint: $userinfo_endpoint", 1);
        
        // Request user info
        $response = wp_remote_get($userinfo_endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Accept' => 'application/json',
            ),
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            $this->log_message("User info request error: " . $response->get_error_message(), 1);
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        $this->log_message("User info response code: $status_code", 1);
        
        // Log full response only at debug level 2
        if (self::DEBUG_LEVEL >= 2) {
            $this->log_message("User info response body: $body", 2);
        }
        
        if ($status_code !== 200) {
            return false;
        }
        
        $user_info = json_decode($body, true);
        
        if (!is_array($user_info)) {
            $this->log_message("Invalid user info response format", 1);
            return false;
        }
        
        // Ensure we have at least the minimal required user info
        if (empty($user_info['id']) || empty($user_info['email'])) {
            $this->log_message("Missing critical user fields in response", 1);
            
            // If 'id' is missing, check if it's nested in a 'user' object
            if (isset($user_info['user']) && is_array($user_info['user'])) {
                $this->log_message("Found nested user object, trying to extract data", 1);
                $user_info = $user_info['user'];
            }
            
            // Check again for required fields
            if (empty($user_info['id']) || empty($user_info['email'])) {
                $this->log_message("Still missing critical user fields after extraction attempt", 1);
                return false;
            }
        }
        
        $this->log_message("User info retrieved successfully", 1);
        return $user_info;
    }

    /**
     * Validate client credentials against ALMOE ID server
     *
     * @param string $client_id Client ID to validate
     * @param string $client_secret Client secret to validate
     * @return bool Whether credentials are valid
     */
    public function validate_client_credentials($client_id, $client_secret) {
        $this->log_message("Validating client credentials", 1);
        
        // Get ALMOE ID server token endpoint for validation
        $token_endpoint = get_option('almoe_id_oauth_token_endpoint', 'https://id.masjidalmubarokah.com/oauth/token');
        
        // Request token with client credentials grant
        // This is just to check if credentials are valid
        $response = wp_remote_post($token_endpoint, array(
            'body' => array(
                'grant_type' => 'client_credentials',
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'scope' => '',
            ),
            'timeout' => 30,
        ));
        
        // Check if request was successful
        if (is_wp_error($response)) {
            $this->log_message('Credentials validation failed: ' . $response->get_error_message(), 1);
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        
        $this->log_message("Credentials validation response code: $response_code", 1);
        
        // A successful response will have a 200 status code and contain an access_token
        $is_valid = $response_code === 200 && isset($response_body['access_token']);
        
        if (!$is_valid) {
            $this->log_message('Invalid client credentials', 1);
        }
        
        $this->log_message("Credentials validation result: " . ($is_valid ? 'valid' : 'invalid'), 1);
        return $is_valid;
    }

    /**
     * Get server information from ALMOE ID discovery endpoint
     *
     * @return array|bool Server info or false on failure
     */
    public function get_server_info() {
        // Try to get discovery URL from options
        $discovery_url = get_option('almoe_id_oauth_discovery_url', 'https://id.masjidalmubarokah.com/.well-known/openid-configuration');
        
        $this->log_message("Getting server info from discovery URL: $discovery_url", 1);
        
        // Request discovery information
        $response = wp_remote_get($discovery_url, array(
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            $this->log_message('Discovery request failed: ' . $response->get_error_message(), 1);
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        $this->log_message("Discovery response code: $status_code", 1);
        
        if ($status_code !== 200) {
            $this->log_message('Invalid discovery response code', 1);
            return false;
        }
        
        $server_info = json_decode($body, true);
        
        if (!is_array($server_info)) {
            $this->log_message('Invalid discovery response format', 1);
            return false;
        }
        
        $this->log_message("Server info retrieved successfully", 1);
        return $server_info;
    }

    /**
     * Auto-configure plugin using discovery endpoint
     *
     * @return bool Whether auto-configuration was successful
     */
    public function auto_configure() {
        $server_info = $this->get_server_info();
        
        if (!$server_info) {
            return false;
        }
        
        // Map discovery values to plugin options
        $endpoint_mappings = array(
            'authorization_endpoint' => 'almoe_id_oauth_auth_endpoint',
            'token_endpoint' => 'almoe_id_oauth_token_endpoint',
            'userinfo_endpoint' => 'almoe_id_oauth_userinfo_endpoint',
            'jwks_uri' => 'almoe_id_oauth_jwks_uri',
            'end_session_endpoint' => 'almoe_id_oauth_logout_endpoint',
        );
        
        foreach ($endpoint_mappings as $info_key => $option_key) {
            if (isset($server_info[$info_key])) {
                update_option($option_key, esc_url_raw($server_info[$info_key]));
                $this->log_message("Auto-configured option $option_key: " . $server_info[$info_key], 1);
            }
        }
        
        return true;
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
            error_log('ALMOE ID OAuth API: ' . $message);
        }
    }
}