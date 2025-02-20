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
    <div class="wrap">
        <h1><?php _e('Order Tracking', 'kommo'); ?></h1>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Date</th>

                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo esc_html($order->order_id); ?></td>
                    <td><?php echo esc_html($order->customer_name); ?><br>
                        <?php echo esc_html($order->customer_email); ?></td>
                    <td><?php echo wc_price($order->order_total); ?></td>
                    <td><?php echo esc_html($order->payment_method); ?></td>
                    <td><?php echo esc_html($order->order_status); ?></td>
                    <td><?php echo esc_html($order->created_at); ?></td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php
        echo paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => $total_pages,
            'current' => $paged
        ));
        ?>
    </div>

</div>