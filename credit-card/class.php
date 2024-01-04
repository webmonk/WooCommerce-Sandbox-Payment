<?php
/**
 * Credit Card Sandbox Payment Gateway.
 *
 * @class       WC_Gateway_WspgCreditCard
 * @extends     WC_Payment_Gateway_CC
 * @version     1.0.1
 * @package     credit-card/class.php
 * @author      Isaac Oyelowo
 */

if (!defined('ABSPATH')) {
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
        $this->id = 'sandbox_creditcard';
        $this->has_fields = true;
        $this->method_title = __('Sandbox Credit Card', 'woocommerce');
        $this->method_description = $this->description;
        $this->icon = $this->get_icon();

        // Load the form fields.
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        $this->title = $this->settings['title'];
        $this->description = $this->settings['description'];

        if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        } else {
            add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
        }
    }

    function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable Sandbox Credit Card Payment.', 'woocommerce'),
                'default' => 'no'
            ),

            'title' => array(
                'title' => __('Title:', 'woocommerce'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                'default' => __('Sandbox Credit Card', 'woocommerce')
            ),

            'description' => array(
                'title' => __('Description:', 'woocommerce'),
                'type' => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                'default' => __('Pay securely using credit card', 'woocommerce')
            ),
        );
    }

    public function admin_options() {
        echo '<h3>' . esc_html__('Credit Card Sandbox Payment Gateway', 'woocommerce') . '</h3>';
        echo '<table class="form-table">';
        // Generate the HTML For the settings form.
        $this->generate_settings_html();
        echo '</table>';
    }

    public function get_icon() {
        $card_types = array('mastercard', 'maestro', 'jcb', 'laser', 'american-express', 'diners-club', 'discover');
        $icon = '';
        foreach ($card_types as $card_type) {
            $card_icon = plugins_url('assets/images/credit-cards/' . sanitize_title($card_type) . '.png', dirname(__FILE__));
            $icon .= '<img src="' . esc_url($card_icon) . '" alt="' . esc_attr($card_type) . '" />';
        }
        return $icon;
    }

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);

        if (
            !isset($_POST[$this->id . '-card-number']) ||
            empty($_POST[$this->id . '-card-number']) ||
            !isset($_POST[$this->id . '-card-expiry']) ||
            empty($_POST[$this->id . '-card-expiry']) ||
            !isset($_POST[$this->id . '-card-cvc']) ||
            empty($_POST[$this->id . '-card-cvc'])
        ) {
            $this->error_notice(__('All credit card fields must be filled.', 'woocommerce'));
            return;
        }

        if (!$this->luhn_check(sanitize_text_field($_POST[$this->id . '-card-number']))) {
            $this->error_notice(__('Credit card is not valid.', 'woocommerce'));
            return;
        }

        if (!$this->is_valid_expiry_date(sanitize_text_field($_POST[$this->id . '-card-expiry']))) {
            $this->error_notice(__('Expiry date is not valid.', 'woocommerce'));
            return;
        }

        if (!$this->is_valid_cvv(sanitize_text_field($_POST[$this->id . '-card-cvc']))) {
            $this->error_notice(__('Cvv must be 3 or 4 numbers.', 'woocommerce'));
            return;
        }

        if (preg_replace('/\D/', '', sanitize_text_field($_POST[$this->id . '-card-number'])) == '4929000000022') {
            $order->update_status('failed', _x('Payment failed', 'Sandbox credit card payment', 'woocommerce'));
        } else {
            // Add the Success Message
            $order->add_order_note(_x('Sandbox credit card payment completed', 'woocommerce'));

            // Mark as Payment Complete
            $order->payment_complete();
        }

        // Empty the cart
        WC()->cart->empty_cart();

        // Return to the Success Page
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order),
        );
    }

    private function error_notice($message) {
        wc_add_notice(esc_html($message), 'error');
    }

    public function luhn_check($number) {
        $number = preg_replace('/\D/', '', $number);
        $number_length = strlen($number);
        $parity = $number_length % 2;
        $total = 0;

        for ($i = 0; $i < $number_length; $i++) {
            $digit = $number[$i];

            if ($i % 2 == $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $total += $digit;
        }

        return $total % 10 == 0;
    }

    public function is_valid_expiry_date($ccexp_expiry) {
        $month = $year = '';
        $month = substr($ccexp_expiry, 0, 2);
        $year = substr($ccexp_expiry, 5, 7);
        $year = '20' . $year;

        if ($month > 12) {
            return false;
        }

        if (date('Y-m-d', strtotime($year . '-' . $month . '-01')) > date('Y-m-d')) {
            return true;
        }

        return false;
    }

    public function is_valid_cvv($ccexp_expiry) {
        if (is_numeric($ccexp_expiry) && (strlen($ccexp_expiry) === 3 || strlen($ccexp_expiry) === 4)) {
            return true;
        }
        return false;
    }
}
