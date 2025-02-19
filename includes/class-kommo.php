<?php

namespace Kommo;

if (!defined('ABSPATH')) {
    exit;
}

class Kommo {
    /**
     * @var Kommo_API
     */
    private $api;

    /**
     * Initialize the plugin
     */
    public function init() {
        // Initialize API
        $this->api = new Kommo_API();

        // Add menu items
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Add settings
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Kommo CRM', 'kommo'),
            __('Kommo CRM', 'kommo'),
            'manage_options',
            'kommo',
            [$this, 'render_admin_page'],
            'dashicons-businessman'
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('kommo_settings', 'kommo_api_key');
        register_setting('kommo_settings', 'kommo_account_domain');
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        require_once KOMMO_PLUGIN_DIR . 'admin/views/admin-page.php';
    }
}