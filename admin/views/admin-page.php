<?php
if (!defined('ABSPATH')) {
    exit;
}

// Handle messages
if (isset($_GET['settings-updated'])) {
    add_settings_error(
        'kommo_messages',
        'kommo_message',
        __('Settings Saved', 'kommo'),
        'updated'
    );
}

// Get logs from database
global $wpdb;
$table_name = $wpdb->prefix . 'kommo_logs';
$logs = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT 100");
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php
    // Show error/update messages
    settings_errors('kommo_messages');
    ?>

    <!-- Settings Form -->
    <div class="card">
        <h2><?php _e('API Settings', 'kommo'); ?></h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('kommo_settings');
            do_settings_sections('kommo_settings');
            ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="kommo_api_key"><?php _e('API Key', 'kommo'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="kommo_api_key" name="kommo_api_key"
                            value="<?php echo esc_attr(get_option('kommo_api_key')); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="kommo_account_domain"><?php _e('Account Domain', 'kommo'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="kommo_account_domain" name="kommo_account_domain"
                            value="<?php echo esc_attr(get_option('kommo_account_domain')); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="card" style="margin-top: 20px;">
        <h2><?php _e('Recent Logs', 'kommo'); ?></h2>
        <?php if ($logs && !empty($logs)) : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('ID', 'kommo'); ?></th>
                    <th><?php _e('Event Type', 'kommo'); ?></th>
                    <th><?php _e('Message', 'kommo'); ?></th>
                    <th><?php _e('Date', 'kommo'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log) : ?>
                <tr>
                    <td><?php echo esc_html($log->id); ?></td>
                    <td><?php echo esc_html($log->event_type); ?></td>
                    <td><?php echo esc_html($log->message); ?></td>
                    <td><?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->created_at))); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else : ?>
        <p><?php _e('No logs found.', 'kommo'); ?></p>
        <?php endif; ?>
    </div>

    <style>
    .card {
        background: #fff;
        border: 1px solid #c3c4c7;
        border-radius: 4px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
    }

    .card h2 {
        margin-top: 0;
        padding-bottom: 12px;
        border-bottom: 1px solid #c3c4c7;
    }
    </style>
</div>