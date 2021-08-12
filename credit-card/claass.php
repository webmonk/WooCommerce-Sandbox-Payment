<?php
/**
 * Credit Card Sandbox Payment Gateway.
 *
 *
 *
 * @class 		WC_Gateway_WspgCreditCard
 * @extends		WC_Payment_Gateway_CC
 * @version		1.0.1
 * @package		credit-card/class.php
 * @author 		Isaac Oyelowo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Gateway_WspgCreditCard Class.
 */

class WC_Gateway_WspgCreditCard extends WC_Payment_Gateway_CC {


	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'sandbox_creditcard';
		$this->has_fields         = yes;
		$this->method_title       = __( 'Sandbox Credit Card', 'woocommerce' );
		$this->method_description = '';
		$this->icon = plugins_url( 'credit-card/icon.png', dirname(__FILE__) ) ;

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
        $this->init_settings();

        $this->title = $this->settings['title'];
        $this->description = $this->settings['description'];
        if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options' ) );
        } else {
            add_action( 'woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options' ) );
        }
	}

    function init_form_fields(){
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable Anypay Payment Plugin.', 'woocommerce'),
                'default' => 'no'),

            'title' => array(
                'title' => __('Title:', 'woocommerce'),
                'type'=> 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                'default' => __('Anypay', 'woocommerce')),
            
            'description' => array(
                'title' => __('Description:', 'woocommerce'),
                'type' => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                'default' => __('Pay securely using Anypay gateway', 'woocommerce')
            ),
        );
    }

    public function admin_options() {

        echo '<h3>'.__('Credit Card Sandbox Payment Gateway', 'woocommerce').'</h3>';
        echo '<table class="form-table">';
        // Generate the HTML For the settings form.
        $this->generate_settings_html();
        //wp_remote_post('https://api.anypay.global/access_tokens');
        echo '</table>';
    }

    public function process_payment( $order_id ) {

    }
}