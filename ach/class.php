<?php
/**
 * ACH Sandbox Payment Gateway.
 *
 *
 *
 * @class 		WC_Gateway_WpsgAch
 * @extends		WC_Payment_Gateway_eCheck
 * @version		1.0.1
 * @package		ach/class.php
 * @author 		Isaac Oyelowo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WC_Gateway_WpsgAch Class.
 */

class WC_Gateway_WpsgAch extends WC_Payment_Gateway_eCheck
{
	public function __construct()
	{
		$this->id = 'sandbox_echeck_payment';
		$this->icon = plugin_dir_url( dirname(__FILE__)  ) . 'assets/images/card-echeck.png';
		$this->has_fields  = true;
        $this->method_title     = __( 'Sandbox ACH' , 'woocommerce' );

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		$this->supports = array(
            'products',
            'default_credit_card_form',
        );

		$this->title = $this->settings['title'];
		$this->description = $this->settings['description'];
        
		if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options' ) );
		} else {
			add_action( 'woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options' ) );
		}
	}

	function init_form_fields() {

		$this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable Network Merchants eCheck Payment.', 'woocommerce'),
                'default' => 'no'),
            'title' => array(
                'title' => __('Title:', 'woocommerce'),
                'type'=> 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                'default' => __('eCheck', 'woocommerce')),
            'description' => array(
                'title' => __('Description:', 'woocommerce'),
                'type' => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                'default' => __('Pay securely using eCheck', 'woocommerce')),
        );
	}

	public function admin_options() {

		echo '<h3>'.__('ACH Sandbox Payment Gateway', 'woocommerce').'</h3>';
		echo esc_html( '<table class="form-table">' );
		// Generate the HTML For the settings form.
		$this -> generate_settings_html();
		echo esc_html( '</table>' );

	}

    public function form() {

        $fields = array();

        $default_fields = array(
            'checkname' => '<p class="form-row form-row-wide">
                <label for="' . esc_attr( $this->id ) . '-checkname">' . esc_html__( 'Check Name', 'woocommerce' ) . ' <span class="required">*</span></label>
                <input id="' . esc_attr( $this->id ) . '-checkname" class="input-text wc-echeck-form-routing-number" type="text" autocomplete="off" name="' . esc_attr( $this->id ) . '-checkname" />
            </p>',
            'checkaba' => '<p class="form-row form-row-first">
                <label for="' . esc_attr( $this->id ) . '-checkaba">' . esc_html__( 'Routing number', 'woocommerce' ) . ' <span class="required">*</span></label>
                <input id="' . esc_attr( $this->id ) . '-checkaba" class="input-text wc-echeck-form-routing-number" type="text" maxlength="9" autocomplete="off" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" name="' . esc_attr( $this->id ) . '-checkaba" />
            </p>',
            'checkaccount' => '<p class="form-row form-row-last">
                <label for="' . esc_attr( $this->id ) . '-checkaccount">' . esc_html__( 'Account number', 'woocommerce' ) . ' <span class="required">*</span></label>
                <input id="' . esc_attr( $this->id ) . '-checkaccountr" class="input-text wc-echeck-form-checkaccount" type="text" autocomplete="off" name="' . esc_attr( $this->id ) . '-checkaccount" maxlength="17" />
            </p>',
        );

        $fields = wp_parse_args( $fields, apply_filters( 'woocommerce_echeck_form_fields', $default_fields, $this->id ) );
        ?>

        <fieldset id="<?php echo esc_attr( $this->id ); ?>-cc-form" class='wc-echeck-form wc-payment-form'>
            <?php do_action( 'woocommerce_echeck_form_start', $this->id ); ?>
            <?php
                foreach ( $fields as $field ) {
                    echo esc_html( $field );
                }
            ?>
            <?php do_action( 'woocommerce_echeck_form_end', $this->id ); ?>
            <div class="clear"></div>
        </fieldset><?php
    }

    public function process_payment( $order_id ) {

        $order = wc_get_order( $order_id );

        if( !isset( $_POST[$this->id.'-checkname'] ) || empty( $_POST[$this->id.'-checkname'] ) || !isset( $_POST[$this->id.'-checkaba'] ) || empty( $_POST[$this->id.'-checkaba'] ) || !isset( $_POST[$this->id.'-checkaccount'] ) || empty( $_POST[$this->id.'-checkaccount'] ) ) {
            $this->error_notice('All eCheck fields must be filled.');
            return;
        }

        $order->update_status( 'on-hold', _x( 'eCheck payment complete. Awaiting verification', 'Sandbox ach payment', 'woocommerce' ) );

            // Empty the cart
        WC()->cart->empty_cart();

        // Return to the Success Page
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url( $order )
        );
    }
} 