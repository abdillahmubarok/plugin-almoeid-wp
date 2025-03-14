<?php
/**
 * ALMOE ID OAuth Plugin Uninstall
 *
 * Cleanup plugin data when it is uninstalled.
 *
 * @package ALMOE_ID_OAuth
 */

// If uninstall not called from WordPress, exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
$options = array(
    'almoe_id_oauth_enabled',
    'almoe_id_oauth_client_id',
    'almoe_id_oauth_client_secret',
    'almoe_id_oauth_auth_endpoint',
    'almoe_id_oauth_token_endpoint',
    'almoe_id_oauth_userinfo_endpoint',
    'almoe_id_oauth_discovery_url',
    'almoe_id_oauth_auto_register',
    'almoe_id_oauth_default_role',
    'almoe_id_oauth_button_text',
    'almoe_id_oauth_just_activated',
);

foreach ($options as $option) {
    delete_option($option);
}

// Clean up user meta
delete_metadata('user', 0, 'almoe_id_oauth_id', '', true);
delete_metadata('user', 0, 'almoe_id_oauth_profile', '', true);
delete_metadata('user', 0, 'almoe_id_oauth_last_login', '', true);

// Remove scheduled tasks
wp_clear_scheduled_hook('almoe_id_oauth_cleanup_logs');

// Drop logs table
global $wpdb;
$table_name = $wpdb->prefix . 'almoe_id_oauth_logs';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Flush rewrite rules after removing our custom endpoint
flush_rewrite_rules();