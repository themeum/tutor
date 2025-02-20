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
use Tutor\Ecommerce\CheckoutController;

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
		// Getting autoload file from concrete gateway class.
		$autoload_file = $this::get_autoload_file();
		if ( is_array( $autoload_file ) ) {
			foreach ( $autoload_file as $file ) {
				if ( file_exists( $file ) ) {
					require_once $file;
				}
			}
		} else {
			if ( file_exists( $autoload_file ) ) {
				require_once $autoload_file;
			}
		}

		if ( ! class_exists( '\Brick\Money' ) ) {
			$autoload_file = tutor()->path . 'ecommerce/PaymentGateways/Paypal/vendor/autoload.php';
			include $autoload_file;
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
	 * @param mixed $webhook_data Webhook data.
	 * @throws \Exception Throw exception if error occur.
	 *
	 * @return mixed
	 */
	public function verify_webhook_signature( $webhook_data ) {
		if ( ! $this->payment ) {
			throw new \Exception( 'Payment object is not initialized.' );
		}

		return $this->payment->verifyAndCreateOrderData( $webhook_data );
	}

	/**
	 * Make recurring payment against a order
	 *
	 * @since 3.0.0
	 *
	 * @param int $order_id Order ID.
	 *
	 * @throws \Throwable Throw throwable if error occur.
	 * @throws \InvalidArgumentException Throw throwable if error occur.
	 * @throws \RuntimeException Throw throwable if error occur.
	 *
	 * @return void
	 */
	public function make_recurring_payment( int $order_id ) {
		// Check if payment object is initialized.
		if ( ! $this->payment ) {
			throw new \InvalidArgumentException( 'Payment object is not initialized.' );
		}

		// Validate order ID and amount.
		if ( ! $order_id ) {
			throw new \InvalidArgumentException( 'Invalid order ID or amount.' );
		}

		try {
			// Prepare payment data.
			$payment_data = CheckoutController::prepare_recurring_payment_data( $order_id );

			if ( ! $payment_data ) {
				throw new \RuntimeException( 'Failed to prepare recurring payment data.' );
			}

			// Set payment data and initiate recurring payment.
			$this->payment->setData( $payment_data );
			$this->payment->createRecurringPayment();
		} catch ( \Throwable $th ) {
			// Catch and rethrow any exception.
			throw $th;
		}
	}

	/**
	 * Make refund against a order
	 *
	 * @since 3.1.0
	 *
	 * @param object $refund_data Refund data.
	 *
	 * @throws \InvalidArgumentException Throw throwable if error occur.
	 *
	 * @return void
	 */
	public function make_refund( object $refund_data ) {
		// Check if payment object is initialized.
		if ( ! $this->payment ) {
			throw new \InvalidArgumentException( 'Payment object is not initialized.' );
		}

		if ( ! method_exists( $this->payment, 'createRefund' ) ) {
			throw new \InvalidArgumentException( 'Refund from payment gateway is not available.' );
		}

		$this->payment->setData( $refund_data );
		$this->payment->createRefund();
	}

}
