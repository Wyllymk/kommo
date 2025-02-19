<?php
/**
 * Plugin Name:     Kommo
 * Plugin URI:      https://github.com/WyllyMk/Kommo
 * Description:     Integrate your WordPress site with Kommo CRM
 * Author:          WyllyMk
 * Author URI:      https://wilsondevops.com/
 * Text Domain:     kommo
 * Domain Path:     /languages
 * Version:         0.1.0
 * Requires PHP:    7.4
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:      https://github.com/WyllyMk/Kommo
 *
 * @package         Kommo
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Prevent duplicate plugin loading
if (defined('KOMMO_VERSION')) {
    return;
}

// Define plugin constants with unique names
define('KOMMO_VERSION', '0.1.0');
define('KOMMO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KOMMO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KOMMO_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('KOMMO_MINIMUM_PHP_VERSION', '7.4');
define('KOMMO_MINIMUM_WP_VERSION', '5.0');

// Check PHP Version
if (version_compare(PHP_VERSION, KOMMO_MINIMUM_PHP_VERSION, '<')) {
    add_action('admin_notices', function() {
        $message = sprintf(
            /* translators: %s: PHP version */
            esc_html__('Kommo requires PHP version %s or higher.', 'kommo'),
            KOMMO_MINIMUM_PHP_VERSION
        );
        printf('<div class="notice notice-error"><p>%s</p></div>', $message);
    });
    return;
}

// Check WordPress Version
if (version_compare($GLOBALS['wp_version'], KOMMO_MINIMUM_WP_VERSION, '<')) {
    add_action('admin_notices', function() {
        $message = sprintf(
            /* translators: %s: WordPress version */
            esc_html__('Kommo requires WordPress version %s or higher.', 'kommo'),
            KOMMO_MINIMUM_WP_VERSION
        );
        printf('<div class="notice notice-error"><p>%s</p></div>', $message);
    });
    return;
}

// Check if another plugin with the same namespace exists
if (class_exists('WyllyMk\KommoCRM\\Plugin')) {
    add_action('admin_notices', function() {
        $message = __('Another plugin is using the Kommo namespace. Please deactivate it before activating this plugin.', 'kommo');
        printf('<div class="notice notice-error"><p>%s</p></div>', esc_html($message));
    });
    return;
}

// Composer autoloader with error handling
if (file_exists(KOMMO_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once KOMMO_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    add_action('admin_notices', function() {
        $message = __('Kommo plugin requires Composer dependencies to be installed. Please run composer install in the plugin directory.', 'kommo');
        printf('<div class="notice notice-error"><p>%s</p></div>', esc_html($message));
    });
    return;
}

// Initialize the plugin with error handling
add_action('plugins_loaded', function() {
    try {
        if (!class_exists('WyllyMk\KommoCRM\\Plugin')) {
            throw new \Exception('Plugin class not found. Please check autoloader configuration.');
        }

        // Check for required PHP extensions
        $required_extensions = ['json', 'curl'];
        foreach ($required_extensions as $ext) {
            if (!extension_loaded($ext)) {
                throw new \Exception(sprintf('Required PHP extension %s is missing.', $ext));
            }
        }

        $plugin = \WyllyMk\KommoCRM\Plugin::getInstance();
        $plugin->init();

    } catch (\Exception $e) {
        add_action('admin_notices', function() use ($e) {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html($e->getMessage())
            );
        });
        
        // Log error if possible
        if (class_exists('WyllyMk\KommoCRM\\Logger')) {
            $logger = \WyllyMk\KommoCRM\Logger::getInstance();
            $logger->log('error', $e->getMessage());
        }

        // Deactivate plugin on critical errors
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        deactivate_plugins(KOMMO_PLUGIN_BASENAME);
    }
});

// Register activation hook with error handling
register_activation_hook(__FILE__, function() {
    try {
        \WyllyMk\KommoCRM\Plugin::activate();
    } catch (\Exception $e) {
        wp_die(
            esc_html($e->getMessage()),
            'Plugin Activation Error',
            ['back_link' => true]
        );
    }
});

// Register deactivation hook with error handling
register_deactivation_hook(__FILE__, function() {
    try {
        \WyllyMk\KommoCRM\Plugin::deactivate();
    } catch (\Exception $e) {
        error_log('Kommo deactivation error: ' . $e->getMessage());
    }
});