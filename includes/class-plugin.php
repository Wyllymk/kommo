<?php

namespace WyllyMk\KommoCRM;

if (!defined('ABSPATH')) {
    exit;
}

class Plugin {
    private static $instance = null;
    private $updater;
    private $logger;

    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init() {
        // Initialize logger
        $this->logger = Logger::getInstance();

        // Initialize updater
        add_action('init', [$this, 'init_github_updater']);

        // Initialize admin menu
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_init', [$this, 'registerSettings']);

        // Add settings link to plugins page
        add_filter('plugin_action_links_' . KOMMO_PLUGIN_BASENAME, [$this, 'addSettingsLink']);

        // Schedule log cleanup
        if (!wp_next_scheduled('kommo_cleanup_logs')) {
            wp_schedule_event(time(), 'daily', 'kommo_cleanup_logs');
        }
        add_action('kommo_cleanup_logs', [$this, 'cleanup_old_logs']);
    }

    /**
     * Add settings link to plugin listing
     *
     * @param array $links Existing plugin action links
     * @return array Modified plugin action links
     */
    public function addSettingsLink($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=kommo-settings'),
            __('Settings', 'kommo')
        );
        array_unshift($links, $settings_link);
        return $links;
    }

    public function log_settings_update($old_value) {
        $this->logger->log('settings_update', 'Plugin settings were updated');
    }

    public function cleanup_old_logs() {
        $this->logger->clear_old_logs(30); // Keep 30 days of logs
    }

    /**
     * Initialize the GitHub updater
     */
    public function init_github_updater() {
        if (!is_admin()) {
            return;
        }

        // Define update constant (optional)
        if (!defined('WP_GITHUB_FORCE_UPDATE')) {
            define('WP_GITHUB_FORCE_UPDATE', true);
        }

        $config = array(
            'slug' => KOMMO_PLUGIN_BASENAME,
            'proper_folder_name' => 'Kommo',
            'api_url' => 'https://api.github.com/repos/WyllyMk/Kommo',
            'raw_url' => 'https://raw.github.com/WyllyMk/Kommo/main',
            'github_url' => 'https://github.com/WyllyMk/Kommo',
            'zip_url' => 'https://github.com/WyllyMk/Kommo/archive/main.zip',
            'sslverify' => true,
            'requires' => '5.0',
            'tested' => '6.4',
            'readme' => 'README.md',
            'access_token' => '', // Add your GitHub access token here if needed
        );

        try {
            $this->updater = new Updater($config);
            
            // Log successful updater initialization
            $this->logger->log('updater', 'GitHub updater initialized successfully');
        } catch (\Exception $e) {
            // Log error if updater fails to initialize
            $this->logger->log('error', 'Failed to initialize GitHub updater: ' . $e->getMessage());
        }
    }    

    public function addAdminMenu() {
        add_menu_page(
            __('Kommo CRM', 'kommo'),
            __('Kommo CRM', 'kommo'),
            'manage_options',
            'kommo-settings',
            [$this, 'renderSettingsPage'],
            'dashicons-businessman'
        );
    }

    public function registerSettings() {
        register_setting('kommo_settings', 'kommo_api_key');
        register_setting('kommo_settings', 'kommo_account_domain');
    }

    public function renderSettingsPage() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'kommo'));
        }

        require_once KOMMO_PLUGIN_DIR . 'admin/views/admin-page.php';
    }

    public static function activate() {
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

        // Log activation
        $logger = Logger::getInstance();
        $logger->log('activation', 'Plugin activated');
    }

    public static function deactivate() {
        // Remove scheduled events
        wp_clear_scheduled_hook('kommo_cleanup_logs');

        // Log deactivation
        $logger = Logger::getInstance();
        $logger->log('deactivation', 'Plugin deactivated');

        flush_rewrite_rules();
    }
}