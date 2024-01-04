<?php
/**
 * Plugin Name: Sandbox Payment Gateway for WooCommerce
 * Plugin URI: http://codemypain.com
 * Description: WooCommerce plugin to test the checkout with fake payments
 * Version: 1.0.3
 * Author: Isaac Oyelowo
 * Author URI: http://isaacoyelowo.dev
 * Requires at least: 3.0
 * Tested up to: 5.8
 */

/*
#begin plugin
*/

function wspg_load_payment_gateways() {
    require_once dirname(__FILE__) . '/credit-card/class.php';
    require_once dirname(__FILE__) . '/ach/class.php';
}

function wspg_add_payment_gateways($methods) {
    $methods[] = 'WC_Gateway_WspgCreditCard';
    $methods[] = 'WC_Gateway_WpsgAch';
    return $methods;
}

function wspg_init_payment_gateways() {
    wspg_load_payment_gateways();
    add_filter('woocommerce_payment_gateways', 'wspg_add_payment_gateways');
}

// Hook the initialization function to plugins_loaded with a priority of 0
add_action('plugins_loaded', 'wspg_init_payment_gateways', 0);
?>