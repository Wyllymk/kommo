<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <form method="post" action="options.php">
        <?php
        settings_fields('kommo_settings');
        do_settings_sections('kommo_settings');
        ?>

        <table class="form-table">
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