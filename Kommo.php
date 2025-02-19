<?php
/**
 * Plugin Name:     Kommo
 * Plugin URI:      https://github.com/WyllyMk/Kommo
 * Description:     Integrate your WordPress site with Kommo CRM to manage leads, contacts, and deals seamlessly. Sync data and automate your sales workflow.
 * Author:          WyllyMk
 * Author URI:      https://wilsondevops.com/
 * Text Domain:     Kommo
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Kommo
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('KOMMO_VERSION', '0.1.0');
define('KOMMO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KOMMO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load required files
require_once KOMMO_PLUGIN_DIR . 'includes/class-kommo.php';
require_once KOMMO_PLUGIN_DIR . 'includes/class-kommo-api.php';

/**
 * Initialize the plugin
 */
function kommo_init() {
    // Initialize main plugin class
    $kommo = new Kommo();
    $kommo->init();
}
add_action('plugins_loaded', 'kommo_init');

/**
 * Activation hook
 */
function kommo_activate() {
    // Add database table creation
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Create logs table
    $table_name = $wpdb->prefix . 'kommo_logs';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        event_type varchar(50) NOT NULL,
        message text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Set default options
    add_option('kommo_api_key', '');
    add_option('kommo_account_domain', '');

    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'kommo_activate');

/**
 * Deactivation hook
 */
function kommo_deactivate() {
    // Clean up plugin data if necessary
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'kommo_deactivate');

/**
 * Uninstall hook
 */
function kommo_uninstall() {
    global $wpdb;
    
    // Remove options
    delete_option('kommo_api_key');
    delete_option('kommo_account_domain');
    delete_option('kommo_settings');
    
    // Remove tables
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}kommo_logs");
}
register_uninstall_hook(__FILE__, 'kommo_uninstall');