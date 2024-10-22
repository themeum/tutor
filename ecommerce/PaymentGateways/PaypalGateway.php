<?php
/**
 * Payment gateway concrete class
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\PaymentGateways;

use Ollyo\PaymentHub\Payments\Paypal\Paypal;
use Tutor\PaymentGateways\Configs\PaypalConfig;

/**
 * Paypal payment gateway class
 */
class PaypalGateway extends GatewayBase {

	/**
	 * Payment gateway root dir name
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $dir_name = 'Paypal';

	/**
	 * Payment gateway config class
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $config_class = PaypalConfig::class;

	/**
	 * Payment core class
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $payment_class = Paypal::class;

	/**
	 * Root dir name of payment gateway src
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_root_dir_name():string {
		return $this->dir_name;
	}

	/**
	 * Payment class from payment hub
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_payment_class():string {
		return $this->payment_class;
	}

	/**
	 * Payment config class
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_config_class():string {
		return $this->config_class;
	}

	/**
	 * Return autoload file
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_autoload_file() {
		return tutor()->path . 'ecommerce/PaymentGateways/Paypal/vendor/autoload.php';
	}
}

