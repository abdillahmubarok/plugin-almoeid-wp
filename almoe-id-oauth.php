<?php
/**
 * Plugin Name: ALMOE ID OAuth
 * Plugin URI: https://masjidalmubarokah.com/
 * Description: Integrasi SSO dengan ALMOE ID - login dan registrasi melalui akun ALMOE ID.
 * Version: 1.0.0
 * Author: Masjid Al-Mubarokah Team
 * Author URI: https://masjidalmubarokah.com/
 * Text Domain: almoe-id-oauth
 * Domain Path: /languages
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Current plugin version.
 */
define('ALMOE_ID_OAUTH_VERSION', '1.0.0');

/**
 * Plugin base file.
 */
define('ALMOE_ID_OAUTH_FILE', __FILE__);

/**
 * Plugin directory path.
 */
define('ALMOE_ID_OAUTH_PATH', plugin_dir_path(__FILE__));

/**
 * Plugin directory URL.
 */
define('ALMOE_ID_OAUTH_URL', plugin_dir_url(__FILE__));

/**
 * Plugin main class autoloader.
 */
spl_autoload_register(function ($class_name) {
    // Check if the class should be loaded by this autoloader
    if (strpos($class_name, 'ALMOE_ID_OAuth\\') !== 0) {
        return;
    }

    // Convert namespace to file path
    $class_path = str_replace('ALMOE_ID_OAuth\\', '', $class_name);
    $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $class_path);
    $file_path = ALMOE_ID_OAUTH_PATH . 'includes' . DIRECTORY_SEPARATOR . 'class-' . strtolower(str_replace('_', '-', $class_path)) . '.php';

    // Load the file if it exists
    if (file_exists($file_path)) {
        require_once $file_path;
    }
});

/**
 * Begin plugin execution
 */
function almoe_id_oauth_init() {
    // Load plugin textdomain
    load_plugin_textdomain('almoe-id-oauth', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Initialize the plugin
    \ALMOE_ID_OAuth\Core::get_instance();
}
add_action('plugins_loaded', 'almoe_id_oauth_init');

/**
 * Code that runs during plugin activation
 */
function activate_almoe_id_oauth() {
    require_once ALMOE_ID_OAUTH_PATH . 'includes/class-activator.php';
    \ALMOE_ID_OAuth\Activator::activate();
}
register_activation_hook(__FILE__, 'activate_almoe_id_oauth');

/**
 * Code that runs during plugin deactivation
 */
function deactivate_almoe_id_oauth() {
    require_once ALMOE_ID_OAUTH_PATH . 'includes/class-deactivator.php';
    \ALMOE_ID_OAuth\Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_almoe_id_oauth');