<?php

namespace WyllyMk\KommoCRM;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Plugin Class
 * 
 * Handles the initialization and core functionality of the plugin
 * 
 * @package WyllyMk\KommoCRM
 * @since 1.0.0
 */
class Plugin {
    /**
     * @var Plugin|null Singleton instance
     */
    private static $instance = null;

    /**
     * @var Updater Instance of the GitHub updater
     */
    private $updater;

    /**
     * @var OrderTracker Instance of the order tracking system
     */
    private $order_tracker;

    /**
     * @var Admin Instance of the admin interface handler
     */
    private $admin;

    /**
     * Get singleton instance of the plugin
     * 
     * @return Plugin
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the plugin
     * 
     * Sets up all necessary hooks and initializes components
     * 
     * @return void
     */
    public function init() {
        // Initialize OrderTracker first as other components depend on it
        $this->order_tracker = OrderTracker::getInstance();

        // Initialize Admin interface
        $this->init_admin();

        // Initialize GitHub updater
        add_action('init', [$this, 'init_github_updater']);

        // Register WooCommerce hooks
        $this->register_woocommerce_hooks();        
    }    

    /**
     * Initialize the admin interface
     * 
     * @return void
     */
    private function init_admin() {
        if (is_admin()) {
            $this->admin = new Admin($this->order_tracker);
            $this->admin->init();
        }
    }

    /**
     * Register WooCommerce related hooks
     * 
     * @return void
     */
    private function register_woocommerce_hooks() {
        // Track new orders
        add_action('woocommerce_checkout_order_processed', function($order_id) {
            $this->order_tracker->track_order($order_id);
        });

        // Track order status changes
        add_action('woocommerce_order_status_changed', function($order_id) {
            $this->order_tracker->track_order($order_id);
        }, 10, 1);
    }

    /**
     * Initialize the GitHub updater
     * 
     * @return void
     */
    public function init_github_updater() {
        if (!is_admin()) {
            return;
        }

        if (!defined('WP_GITHUB_FORCE_UPDATE')) {
            define('WP_GITHUB_FORCE_UPDATE', true);
        }

        $config = array(
            'slug' => KOMMO_PLUGIN_BASENAME,
            'proper_folder_name' => 'kommo',
            'api_url' => 'https://api.github.com/repos/WyllyMk/kommo',
            'raw_url' => 'https://raw.github.com/WyllyMk/kommo/main',
            'github_url' => 'https://github.com/WyllyMk/kommo',
            'zip_url' => 'https://github.com/WyllyMk/kommo/archive/main.zip',
            'sslverify' => true,
            'requires' => '5.0',
            'tested' => '6.4',
            'readme' => 'README.txt',
            'access_token' => '',
        );

        try {
            $this->updater = new Updater($config);
        } catch (\Exception $e) {
            // Log error silently
            error_log('Kommo updater initialization failed: ' . $e->getMessage());
        }
    }    


    /**
     * Plugin activation hook callback
     * 
     * Creates necessary database tables and sets default options
     * 
     * @return void
     */
    public static function activate() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'kommo_order_tracking';
        $charset_collate = $wpdb->get_charset_collate();
    
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            customer_id bigint(20) NOT NULL,
            customer_email varchar(100) NOT NULL,
            customer_name varchar(100) NOT NULL,
            order_total decimal(10,2) NOT NULL,
            payment_method varchar(100) NOT NULL,
            order_status varchar(50) NOT NULL,
            created_at datetime NOT NULL,
            products longtext NOT NULL,
            coupons longtext,
            billing_address longtext NOT NULL,
            shipping_address longtext NOT NULL,
            PRIMARY KEY  (id),
            KEY order_id (order_id),
            KEY customer_id (customer_id)
        ) $charset_collate;";
    
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Set default options
        add_option('kommo_api_key', '');
        add_option('kommo_account_domain', '');
        add_option('kommo_api_endpoint', '');

        // Clear any existing rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation hook callback
     * 
     * Cleans up scheduled events and logs
     * 
     * @return void
     */
    public static function deactivate() {
        // Remove scheduled events
        wp_clear_scheduled_hook('kommo_cleanup_logs');

        // Clear rewrite rules
        flush_rewrite_rules();
    }
}