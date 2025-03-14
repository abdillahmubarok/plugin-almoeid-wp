<?php
/**
 * Plugin Activator
 *
 * @package ALMOE_ID_OAuth
 */

namespace ALMOE_ID_OAuth;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fired during plugin activation
 */
class Activator {
    /**
     * Activate the plugin
     *
     * Creates necessary database tables and sets up initial options
     */
    public static function activate() {
        // Create logs table if it doesn't exist
        self::create_logs_table();
        
        // Add rewrite rules for OAuth callback
        self::add_rewrite_rules();
        
        // Set default options if they don't exist
        self::set_default_options();
        
        // Schedule cleanup tasks
        self::schedule_cleanup_tasks();
        
        // Set activation flag for welcome message
        update_option('almoe_id_oauth_just_activated', '1');
    }

    /**
     * Create logs table
     */
    private static function create_logs_table() {
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
     * Add rewrite rules
     */
    private static function add_rewrite_rules() {
        add_rewrite_rule(
            '^almoe-id-callback/?$',
            'index.php?almoe_id_oauth_callback=1',
            'top'
        );
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Set default options
     */
    private static function set_default_options() {
        // General settings
        add_option('almoe_id_oauth_enabled', '1');
        add_option('almoe_id_oauth_auto_register', '1');
        add_option('almoe_id_oauth_default_role', 'subscriber');
        
        // Appearance settings
        add_option('almoe_id_oauth_button_text', __('Login with ALMOE ID', 'almoe-id-oauth'));
        
        // Connection settings
        if (!get_option('almoe_id_oauth_auth_endpoint')) {
            add_option('almoe_id_oauth_auth_endpoint', 'https://id.masjidalmubarokah.com/oauth/authorize');
        }
        
        if (!get_option('almoe_id_oauth_token_endpoint')) {
            add_option('almoe_id_oauth_token_endpoint', 'https://id.masjidalmubarokah.com/oauth/token');
        }
        
        if (!get_option('almoe_id_oauth_userinfo_endpoint')) {
            add_option('almoe_id_oauth_userinfo_endpoint', 'https://id.masjidalmubarokah.com/api/user');
        }
        
        if (!get_option('almoe_id_oauth_discovery_url')) {
            add_option('almoe_id_oauth_discovery_url', 'https://id.masjidalmubarokah.com/.well-known/openid-configuration');
        }
    }

    /**
     * Schedule cleanup tasks
     */
    private static function schedule_cleanup_tasks() {
        // Schedule event to clean up old logs
        if (!wp_next_scheduled('almoe_id_oauth_cleanup_logs')) {
            wp_schedule_event(time(), 'daily', 'almoe_id_oauth_cleanup_logs');
        }
    }
}