<?php

namespace WyllyMk\KommoCRM;

/**
 * Class Admin
 * Handles all admin-related functionality for order tracking
 * 
 * @package WyllyMk\KommoCRM
 * @since 1.0.0
 */
class Admin {
    /**
     * @var OrderTracker Instance of the OrderTracker class
     */
    private $order_tracker;

    /**
     * Admin constructor.
     * 
     * @param OrderTracker $order_tracker Instance of OrderTracker
     */
    public function __construct(OrderTracker $order_tracker) {
        $this->order_tracker = $order_tracker;
    }

    /**
     * Initialize admin hooks and callbacks
     * 
     * @return void
     */
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_filter('plugin_action_links_' . KOMMO_PLUGIN_BASENAME, array($this, 'add_settings_link'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        // Register settings
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Add settings link to the plugins page
     * 
     * @param array $links Array of plugin action links
     * @return array Modified array of plugin action links
     */
    public function add_settings_link($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=order-tracking'),
            esc_html__('Settings', 'kommo')
        );
        array_unshift($links, $settings_link);
        return $links;
    }

    
    /**
     * Register plugin settings
     * 
     * @return void
     */
    public function register_settings() {
        register_setting('kommo_settings', 'kommo_api_key', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ));
        
        register_setting('kommo_settings', 'kommo_account_domain', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ));
    }

    /**
     * Register the admin menu page for Order Tracking
     * 
     * @return void
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Order Tracking', 'kommo'),
            __('Order Tracking', 'kommo'),
            'manage_woocommerce',
            'order-tracking',
            array($this, 'render_admin_page'),
            'dashicons-chart-area',
            56 // After WooCommerce menu item
        );
    }

    /**
     * Enqueue admin scripts and styles
     * 
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_order-tracking' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'kommo-admin-styles',
            KOMMO_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            KOMMO_VERSION
        );

        wp_enqueue_script(
            'kommo-admin-script',
            KOMMO_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            KOMMO_VERSION,
            true
        );

        wp_localize_script('kommo-admin-script', 'kommoAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kommo_admin_nonce')
        ));
    }

    /**
     * Render the admin page content
     * 
     * @return void
     */
    public function render_admin_page() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'kommo'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'kommo_order_tracking';
        
        // Handle filters
        $filters = $this->get_current_filters();
        
        // Get paginated results
        $current_page = max(1, get_query_var('paged', 1));
        $per_page = apply_filters('kommo_orders_per_page', 20);
        $offset = ($current_page - 1) * $per_page;

        // Prepare the query
        $query = $this->build_orders_query($filters, $per_page, $offset);
        $orders = $wpdb->get_results($query['query']);
        $total_items = $wpdb->get_var($query['count_query']);
        $total_pages = ceil($total_items / $per_page);

        // Include the admin template
        include KOMMO_PLUGIN_DIR . 'admin/views/admin-page.php';
    }

    /**
     * Get current filter values from URL parameters
     * 
     * @return array Array of current filters
     */
    private function get_current_filters() {
        return array(
            'date_from' => sanitize_text_field($_GET['date_from'] ?? ''),
            'date_to' => sanitize_text_field($_GET['date_to'] ?? ''),
            'search' => sanitize_text_field($_GET['search'] ?? ''),
            'status' => sanitize_text_field($_GET['status'] ?? '')
        );
    }

    /**
     * Build the SQL query for orders based on filters
     * 
     * @param array $filters Array of filter values
     * @param int $limit Number of records per page
     * @param int $offset Offset for pagination
     * @return array Array containing main query and count query
     */
    private function build_orders_query($filters, $limit, $offset) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kommo_order_tracking';
        
        $where = array('1=1');
        $args = array();

        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= %s';
            $args[] = $filters['date_from'] . ' 00:00:00';
        }

        // Add more filter conditions here...

        $where_clause = implode(' AND ', $where);
        $query = $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            array_merge($args, array($limit, $offset))
        );

        $count_query = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}",
            $args
        );

        return array(
            'query' => $query,
            'count_query' => $count_query
        );
    }
}