<?php

class QPay_Ajax
{
    public static function register()
    {
        add_action('wp_ajax_qpay_create_invoice', [self::class, 'create_invoice']);
        add_action('wp_ajax_nopriv_qpay_create_invoice', [self::class, 'create_invoice']);
        add_action('wp_ajax_qpay_check_payment', [self::class, 'check_payment']);
        add_action('wp_ajax_nopriv_qpay_check_payment', [self::class, 'check_payment']);
    }

    public static function create_invoice()
    {
        check_ajax_referer('qpay_nonce', 'nonce');

        $amount = (float) sanitize_text_field($_POST['amount'] ?? '0');
        $description = sanitize_text_field($_POST['description'] ?? 'Payment');

        if ($amount <= 0) wp_send_json_error('Invalid amount');

        $api = new QPay_API();
        $invoice = $api->create_invoice([
            'invoice_code' => get_option('qpay_invoice_code'),
            'sender_invoice_no' => 'WP-' . time(),
            'invoice_description' => $description,
            'amount' => $amount,
            'callback_url' => get_option('qpay_callback_url', rest_url('qpay/v1/webhook')),
        ]);

        if (!$invoice || empty($invoice['invoice_id'])) {
            wp_send_json_error('Invoice creation failed');
        }

        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'qpay_payments', [
            'invoice_id' => $invoice['invoice_id'],
            'amount' => $amount,
            'description' => $description,
            'status' => 'pending',
        ]);

        wp_send_json_success($invoice);
    }

    public static function check_payment()
    {
        check_ajax_referer('qpay_nonce', 'nonce');

        $invoice_id = sanitize_text_field($_POST['invoice_id'] ?? '');
        if (!$invoice_id) wp_send_json_error('Missing invoice_id');

        $api = new QPay_API();
        $result = $api->check_payment($invoice_id);

        if ($result && !empty($result['rows'])) {
            global $wpdb;
            $wpdb->update($wpdb->prefix . 'qpay_payments', ['status' => 'paid'], ['invoice_id' => $invoice_id]);
            wp_send_json_success(['status' => 'paid']);
        }
        wp_send_json_success(['status' => 'unpaid']);
    }
}
