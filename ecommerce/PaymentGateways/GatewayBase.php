<?php
/**
 * GatewayBase class for implementing common functionalities
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\PaymentGateways;

use Ollyo\PaymentHub\PaymentHub;
use stdClass;

/**
 * Payment gateway base class
 */
abstract class GatewayBase {

	/**
	 * Payment object of PaymentHub
	 *
	 * @var object
	 */
	public $payment = null;

	/**
	 * Root directory name
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	abstract public function get_root_dir_name():string;

	/**
	 * Payment class from payment hub
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	abstract public function get_payment_class():string;

	/**
	 * Config class
	 *
	 * @var string
	 *
	 * @since 3.0.0
	 */
	abstract public function get_config_class():string;

	/**
	 * Include autoload file & init payment object
	 *
	 * @since 3.0.0
	 *
	 * @throws \Exception Throw exception if error occur.
	 *
	 * @return void
	 */
	public function __construct() {
		$autoload_file = tutor()->path . 'ecommerce/PaymentGateways/' . $this->get_root_dir_name() . '/vendor/autoload.php';

		if ( file_exists( $autoload_file ) ) {
			require_once $autoload_file;
		}

		$this->payment = ( new PaymentHub( $this->get_payment_class(), $this->get_config_class() ) )->make();

		if ( ! $this->payment ) {
			throw new \Exception( 'Failed to initialize payment for gateway: ' . static::class );
		}
	}

	/**
	 * Setup payment data & redirect to the gateway
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $data Data to be sent to the gateway.
	 *
	 * @throws \Exception Throw exception if error occur.
	 * @throws \Throwable Throw a throwable if error occur inside payment hub.
	 *
	 * @return void
	 */
	public function setup_payment_and_redirect( $data ) {
		if ( ! $this->payment ) {
			throw new \Exception( 'Payment object is not initialized.' );
		}

		try {
			$this->payment->setData( $data );

			$this->payment->createPayment();
		} catch ( \Throwable $th ) {
			throw $th;
		}
	}

	/**
	 * Generate webhook data of the payment and return
	 *
	 * @since 3.0.0
	 *
	 * @throws \Exception Throw exception if error occur.
	 * @throws \Throwable Throw a throwable if error occur inside payment hub.
	 *
	 * @return object
	 */
	public function get_webhook_data() {
		if ( ! $this->payment ) {
			throw new \Exception( 'Payment object is not initialized.' );
		}

		$obj = new stdClass();

		try {
			$webhook = $this->payment->createWebhook();

			$obj->webhook_url = $webhook->url;
			$obj->secret      = $webhook->secret;

			return $obj;
		} catch ( \Throwable $th ) {
			throw $th;
		}
	}

	/**
	 * Generate webhook data of the payment and return
	 *
	 * @since 3.0.0
	 *
	 * @throws \Exception Throw exception if error occur.
	 * @throws \Throwable Throw a throwable if error occur inside payment hub.
	 *
	 * @return mixed
	 */
	public function verify_webhook_signature( $webhook_data ) {
		if ( ! $this->payment ) {
			throw new \Exception( 'Payment object is not initialized.' );
		}

		return $this->payment->verifyAndCreateOrderData( $webhook_data );
	}
}
