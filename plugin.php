<?php
/**
 * Plugin Name: Sandbox Payment Gateway for WooCommerce
 * Plugin URI: http://codemypain.com
 * Description: WooCommerce plugin to test the checkout with fake payments
 * Version: 1.0.1
 * Author: Isaac Oyelowo
 * Author URI: http://isaacoyelowo.dev
 * Requires at least: 3.0
 * Tested up to: 5.8
 */

/*
#begin plugin
*/

function wspg_woocommerce() {

	require_once dirname(__FILE__) . '/credit-card/class.php';
	require_once dirname(__FILE__) . '/ach/class.php';
}

function wspg_add_gateway( $methods ) {

	$methods[] = 'WC_Gateway_WspgCreditCard';
	$methods[] = 'WC_Gateway_WpsgAch';
	return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'wspg_add_gateway' );
add_action( 'plugins_loaded', 'wspg_woocommerce', 0 );
?>