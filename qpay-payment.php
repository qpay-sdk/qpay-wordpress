<?php
/**
 * Plugin Name: QPay Payment Forms
 * Description: QPay V2 payment forms for WordPress (no WooCommerce required)
 * Version: 1.0.0
 * Author: QPay SDK
 * License: MIT
 */

if (!defined('ABSPATH')) exit;

define('QPAY_WP_VERSION', '1.0.0');
define('QPAY_WP_PATH', plugin_dir_path(__FILE__));
define('QPAY_WP_URL', plugin_dir_url(__FILE__));

require_once QPAY_WP_PATH . 'includes/class-qpay-api.php';
require_once QPAY_WP_PATH . 'includes/class-qpay-admin.php';
require_once QPAY_WP_PATH . 'includes/class-qpay-shortcode.php';
require_once QPAY_WP_PATH . 'includes/class-qpay-ajax.php';

register_activation_hook(__FILE__, 'qpay_wp_activate');

function qpay_wp_activate()
{
    global $wpdb;
    $table = $wpdb->prefix . 'qpay_payments';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        invoice_id varchar(255) NOT NULL,
        amount decimal(12,2) NOT NULL DEFAULT 0,
        description varchar(500) DEFAULT '',
        status varchar(32) NOT NULL DEFAULT 'pending',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY invoice_id (invoice_id),
        KEY status (status)
    ) $charset;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// Init
add_action('init', function () {
    QPay_Shortcode::register();
});

add_action('admin_menu', function () {
    QPay_Admin::register_menu();
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('qpay-form', QPAY_WP_URL . 'public/css/qpay-form.css', [], QPAY_WP_VERSION);
    wp_enqueue_script('qpay-form', QPAY_WP_URL . 'public/js/qpay-form.js', [], QPAY_WP_VERSION, true);
    wp_localize_script('qpay-form', 'qpayAjax', [
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('qpay_nonce'),
    ]);
});

// AJAX
QPay_Ajax::register();

// REST API webhook
add_action('rest_api_init', function () {
    register_rest_route('qpay/v1', '/webhook', [
        'methods' => 'POST',
        'callback' => function ($request) {
            $invoice_id = $request->get_param('invoice_id');
            if (!$invoice_id) return new WP_REST_Response(['error' => 'Missing invoice_id'], 400);

            $api = new QPay_API();
            $result = $api->check_payment($invoice_id);
            if ($result && !empty($result['rows'])) {
                global $wpdb;
                $wpdb->update($wpdb->prefix . 'qpay_payments', ['status' => 'paid'], ['invoice_id' => $invoice_id]);
                return new WP_REST_Response(['status' => 'paid']);
            }
            return new WP_REST_Response(['status' => 'unpaid']);
        },
        'permission_callback' => '__return_true',
    ]);
});
