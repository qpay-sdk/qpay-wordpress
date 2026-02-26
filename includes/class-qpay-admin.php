<?php

class QPay_Admin
{
    public static function register_menu()
    {
        add_options_page('QPay Settings', 'QPay', 'manage_options', 'qpay-settings', [self::class, 'settings_page']);
        add_menu_page('QPay Payments', 'QPay Payments', 'manage_options', 'qpay-payments', [self::class, 'payments_page'], 'dashicons-money-alt', 30);

        add_action('admin_init', function () {
            register_setting('qpay_settings', 'qpay_base_url');
            register_setting('qpay_settings', 'qpay_username');
            register_setting('qpay_settings', 'qpay_password');
            register_setting('qpay_settings', 'qpay_invoice_code');
            register_setting('qpay_settings', 'qpay_callback_url');
        });
    }

    public static function settings_page()
    {
        ?>
        <div class="wrap">
            <h1>QPay Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('qpay_settings'); ?>
                <table class="form-table">
                    <tr><th>API Base URL</th><td><input type="text" name="qpay_base_url" value="<?php echo esc_attr(get_option('qpay_base_url', 'https://merchant.qpay.mn')); ?>" class="regular-text"></td></tr>
                    <tr><th>Username</th><td><input type="text" name="qpay_username" value="<?php echo esc_attr(get_option('qpay_username')); ?>" class="regular-text"></td></tr>
                    <tr><th>Password</th><td><input type="password" name="qpay_password" value="<?php echo esc_attr(get_option('qpay_password')); ?>" class="regular-text"></td></tr>
                    <tr><th>Invoice Code</th><td><input type="text" name="qpay_invoice_code" value="<?php echo esc_attr(get_option('qpay_invoice_code')); ?>" class="regular-text"></td></tr>
                    <tr><th>Callback URL</th><td><input type="text" name="qpay_callback_url" value="<?php echo esc_attr(get_option('qpay_callback_url', rest_url('qpay/v1/webhook'))); ?>" class="regular-text"></td></tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public static function payments_page()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'qpay_payments';
        $payments = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 50");
        ?>
        <div class="wrap">
            <h1>QPay Payments</h1>
            <table class="wp-list-table widefat fixed striped">
                <thead><tr><th>ID</th><th>Invoice ID</th><th>Amount</th><th>Status</th><th>Created</th></tr></thead>
                <tbody>
                <?php foreach ($payments as $p): ?>
                    <tr>
                        <td><?php echo esc_html($p->id); ?></td>
                        <td><?php echo esc_html($p->invoice_id); ?></td>
                        <td><?php echo esc_html(number_format($p->amount, 0)); ?>₮</td>
                        <td><span style="color:<?php echo $p->status === 'paid' ? 'green' : '#666'; ?>"><?php echo esc_html($p->status); ?></span></td>
                        <td><?php echo esc_html($p->created_at); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
