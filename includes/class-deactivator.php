<?php
/**
 * Plugin Deactivator
 *
 * @package ALMOE_ID_OAuth
 */

namespace ALMOE_ID_OAuth;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fired during plugin deactivation
 */
class Deactivator {
    /**
     * Deactivate the plugin
     *
     * Cleans up scheduled tasks and temporary data
     */
    public static function deactivate() {
        // Remove scheduled tasks
        self::remove_scheduled_tasks();
        
        // Clear rewrite rules
        flush_rewrite_rules();
        
        // Remove temporary options
        delete_option('almoe_id_oauth_just_activated');
    }

    /**
     * Remove scheduled tasks
     */
    private static function remove_scheduled_tasks() {
        // Clear the scheduled event for cleaning up logs
        wp_clear_scheduled_hook('almoe_id_oauth_cleanup_logs');
    }
}