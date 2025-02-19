<?php

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check for multisite
if (is_multisite()) {
    global $wpdb;
    $blogs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    
    foreach ($blogs as $blog) {
        switch_to_blog($blog);
        kommo_uninstall_single_site();
        restore_current_blog();
    }
} else {
    kommo_uninstall_single_site();
}

/**
 * Uninstall function for single site
 */
function kommo_uninstall_single_site() {
    global $wpdb;
    
    // Remove options
    delete_option('kommo_api_key');
    delete_option('kommo_account_domain');
    delete_option('kommo_settings');
    
    // Remove tables
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}kommo_logs");
    
    // Clear any cached data
    wp_cache_flush();
    
    // Remove any scheduled events
    wp_clear_scheduled_hook('kommo_daily_sync');
    
    // Remove any transients
    delete_transient('kommo_api_cache');
    
    // Optional: Remove any custom post types and their data
    // $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'kommo_custom_type'");
    
    // Optional: Remove any user meta related to the plugin
    // $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'kommo_%'");
}