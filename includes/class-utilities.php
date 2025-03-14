<?php
/**
 * Utilities Module
 *
 * @package ALMOE_ID_OAuth
 */

namespace ALMOE_ID_OAuth;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Utilities class
 * Helper functions and security tools
 */
class Utilities {
    /**
     * Initialize utilities
     */
    public static function init() {
        // Add security headers
        add_action('send_headers', array(__CLASS__, 'add_security_headers'));
        
        // Log authentication events
        add_action('almoe_id_oauth_login_success', array(__CLASS__, 'log_login_success'), 10, 2);
        add_action('almoe_id_oauth_login_failure', array(__CLASS__, 'log_login_failure'), 10, 2);
        
        // Clean expired logs
        add_action('wp_scheduled_delete', array(__CLASS__, 'clean_expired_logs'));
    }

    /**
     * Add security headers to responses
     */
    public static function add_security_headers() {
        // Only add these headers if the auth is enabled
        if (get_option('almoe_id_oauth_enabled', '1') !== '1') {
            return;
        }
        
        // Set security headers
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Add Content-Security-Policy if we're on an OAuth related page
        if (isset($_GET['almoe_id_oauth_callback']) || is_page('almoe-id-callback')) {
            $csp = "default-src 'self'; ";
            $csp .= "script-src 'self' 'unsafe-inline'; ";
            $csp .= "style-src 'self' 'unsafe-inline'; ";
            $csp .= "img-src 'self' data:; ";
            $csp .= "connect-src 'self' " . esc_url(get_option('almoe_id_oauth_auth_endpoint', 'https://id.masjidalmubarokah.com')) . "; ";
            
            header('Content-Security-Policy: ' . $csp);
        }
    }

    /**
     * Sanitize and validate a URL with specific allowed hosts
     *
     * @param string $url URL to validate
     * @return string|bool Sanitized URL or false if not valid
     */
    public static function validate_url($url, $allowed_hosts = array()) {
        // First do a standard sanitization
        $clean_url = esc_url_raw($url, array('https'));
        
        // If the sanitization stripped the URL, it wasn't valid
        if (empty($clean_url)) {
            return false;
        }
        
        // If no specific hosts are provided, just return the sanitized URL
        if (empty($allowed_hosts)) {
            return $clean_url;
        }
        
        // Check if the URL's host is in the allowed list
        $url_parts = parse_url($clean_url);
        
        if (!isset($url_parts['host']) || !in_array($url_parts['host'], $allowed_hosts, true)) {
            return false;
        }
        
        return $clean_url;
    }

    /**
     * Log successful login attempts
     *
     * @param int $user_id User ID
     * @param array $user_info User info from ALMOE ID
     */
    public static function log_login_success($user_id, $user_info) {
        global $wpdb;
        
        // Prepare log data
        $log_data = array(
            'user_id' => $user_id,
            'event_type' => 'login_success',
            'event_data' => wp_json_encode(array(
                'almoe_id' => isset($user_info['id']) ? $user_info['id'] : '',
                'email' => isset($user_info['email']) ? $user_info['email'] : '',
                'ip' => self::get_client_ip(),
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            )),
            'created_at' => current_time('mysql'),
        );
        
        // Insert log entry
        self::insert_log($log_data);
    }

    /**
     * Log failed login attempts
     *
     * @param string $error Error message
     * @param array $data Additional data about the failure
     */
    public static function log_login_failure($error, $data = array()) {
        global $wpdb;
        
        // Prepare log data
        $log_data = array(
            'user_id' => 0, // No user for failed logins
            'event_type' => 'login_failure',
            'event_data' => wp_json_encode(array(
                'error' => $error,
                'data' => $data,
                'ip' => self::get_client_ip(),
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            )),
            'created_at' => current_time('mysql'),
        );
        
        // Insert log entry
        self::insert_log($log_data);
    }

    /**
     * Insert log entry into database
     *
     * @param array $log_data Log data to insert
     * @return int|bool Log ID on success, false on failure
     */
    private static function insert_log($log_data) {
        global $wpdb;
        
        // Make sure log table exists
        self::create_logs_table_if_not_exists();
        
        // Insert log into database
        $result = $wpdb->insert(
            $wpdb->prefix . 'almoe_id_oauth_logs',
            $log_data,
            array('%d', '%s', '%s', '%s')
        );
        
        if (!$result) {
            error_log('ALMOE ID OAuth: Failed to insert log entry - ' . $wpdb->last_error);
            return false;
        }
        
        return $wpdb->insert_id;
    }

    /**
     * Create logs table if it doesn't exist
     */
    private static function create_logs_table_if_not_exists() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'almoe_id_oauth_logs';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE $table_name (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned NOT NULL DEFAULT 0,
                event_type varchar(50) NOT NULL,
                event_data longtext NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY user_id (user_id),
                KEY event_type (event_type),
                KEY created_at (created_at)
            ) $charset_collate;";
            
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }
    }

    /**
     * Clean expired logs
     */
    public static function clean_expired_logs() {
        global $wpdb;
        
        // Get log retention days (default 30 days)
        $retention_days = apply_filters('almoe_id_oauth_log_retention_days', 30);
        
        // Calculate cutoff date
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-$retention_days days"));
        
        // Delete old logs
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}almoe_id_oauth_logs WHERE created_at < %s",
                $cutoff_date
            )
        );
    }

    /**
     * Get client IP address
     *
     * @return string Client IP address
     */
    public static function get_client_ip() {
        // Check for proxy server
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }

    /**
     * Generate a secure random token
     *
     * @param int $length Length of the token
     * @return string Random token
     */
    public static function generate_random_token($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Get plugin logs
     *
     * @param array $args Query arguments
     * @return array Logs
     */
    public static function get_logs($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'number' => 50,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'user_id' => '',
            'event_type' => '',
            'date_from' => '',
            'date_to' => '',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Build query
        $table = $wpdb->prefix . 'almoe_id_oauth_logs';
        $where = array();
        $values = array();
        
        // User ID filter
        if (!empty($args['user_id'])) {
            $where[] = 'user_id = %d';
            $values[] = intval($args['user_id']);
        }
        
        // Event type filter
        if (!empty($args['event_type'])) {
            $where[] = 'event_type = %s';
            $values[] = $args['event_type'];
        }
        
        // Date range filter
        if (!empty($args['date_from'])) {
            $where[] = 'created_at >= %s';
            $values[] = $args['date_from'] . ' 00:00:00';
        }
        
        if (!empty($args['date_to'])) {
            $where[] = 'created_at <= %s';
            $values[] = $args['date_to'] . ' 23:59:59';
        }
        
        // Build WHERE clause
        $where_clause = '';
        if (!empty($where)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where);
        }
        
        // Build ORDER BY clause
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']) ?: 'created_at DESC';
        
        // Build LIMIT clause
        $limit = '';
        if (!empty($args['number']) && $args['number'] > 0) {
            $limit = $wpdb->prepare('LIMIT %d, %d', $args['offset'], $args['number']);
        }
        
        // Build final query
        $query = "SELECT * FROM $table $where_clause ORDER BY $orderby $limit";
        
        // Prepare the query with values
        if (!empty($values)) {
            $query = $wpdb->prepare($query, $values);
        }
        
        // Execute and get logs
        $logs = $wpdb->get_results($query, ARRAY_A);
        
        // Parse JSON data
        foreach ($logs as &$log) {
            $log['event_data'] = json_decode($log['event_data'], true);
        }
        
        return $logs;
    }

    /**
     * Count total logs
     *
     * @param array $args Query arguments
     * @return int Total logs count
     */
    public static function count_logs($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'user_id' => '',
            'event_type' => '',
            'date_from' => '',
            'date_to' => '',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Build query
        $table = $wpdb->prefix . 'almoe_id_oauth_logs';
        $where = array();
        $values = array();
        
        // User ID filter
        if (!empty($args['user_id'])) {
            $where[] = 'user_id = %d';
            $values[] = intval($args['user_id']);
        }
        
        // Event type filter
        if (!empty($args['event_type'])) {
            $where[] = 'event_type = %s';
            $values[] = $args['event_type'];
        }
        
        // Date range filter
        if (!empty($args['date_from'])) {
            $where[] = 'created_at >= %s';
            $values[] = $args['date_from'] . ' 00:00:00';
        }
        
        if (!empty($args['date_to'])) {
            $where[] = 'created_at <= %s';
            $values[] = $args['date_to'] . ' 23:59:59';
        }
        
        // Build WHERE clause
        $where_clause = '';
        if (!empty($where)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where);
        }
        
        // Build final query
        $query = "SELECT COUNT(*) FROM $table $where_clause";
        
        // Prepare the query with values
        if (!empty($values)) {
            $query = $wpdb->prepare($query, $values);
        }
        
        // Execute and get count
        return (int) $wpdb->get_var($query);
    }
}