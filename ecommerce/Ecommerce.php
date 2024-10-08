<?php
/**
 * Main class to handle tutor native e-commerce.
 *
 * @package Tutor\Ecommerce
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

use TUTOR\Course;
use Tutor\Ecommerce\PaymentHandler;
use TUTOR\Input;
use Tutor\PaymentGateways\Configs\PaypalConfig;
use Tutor\PaymentGateways\GatewayFactory;
use Tutor\PaymentGateways\PaypalGateway;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ecommerce
 *
 * @since 3.0.0
 */
class Ecommerce {

	/**
	 * Native ecommerce engin
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const MONETIZE_BY = 'tutor';

	/**
	 * Construct function to initialize e-commerce classes
	 *
	 * @since 3.0.0
	 *
	 * @param bool $register_hooks register hooks.
	 */
	public function __construct( $register_hooks = true ) {

		if ( ! $register_hooks ) {
			return;
		}

		add_filter( 'tutor_monetization_options', array( $this, 'add_monetization_option' ) );

		new Settings();
		// Include currency file.
		require_once tutor()->path . 'ecommerce/currency.php';

		if ( ! tutor_utils()->is_monetize_by_tutor() ) {
			return;
		}

		add_action( 'save_post_' . tutor()->course_post_type, array( $this, 'save_price' ), 10, 2 );

		new AdminMenu();
		new CartController();
		new CheckoutController();
		new OrderController();
		new OrderActivitiesController();
		new CouponController();
		new BillingController();
		new HooksHandler();
		new EmailController();
		new PaymentHandler();
	}

	/**
	 * Save course price and course sale price.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $post_ID course ID.
	 * @param mixed $post    course details.
	 *
	 * @return void
	 */
	public function save_price( $post_ID, $post ) {
		if ( ! tutor_utils()->is_monetize_by_tutor() ) {
			return;
		}

		$course_price = Input::post( 'course_price', 0, Input::TYPE_NUMERIC );
		$sale_price   = Input::post( 'course_sale_price', 0, Input::TYPE_NUMERIC );

		if ( $course_price ) {
			update_post_meta( $post_ID, Course::COURSE_PRICE_META, $course_price );
		}

		if ( Input::has( 'course_sale_price' ) ) {
			update_post_meta( $post_ID, Course::COURSE_SALE_PRICE_META, $sale_price );
		}
	}

	/**
	 * Add monetization option.
	 *
	 * @since 3.0.0
	 *
	 * @param array $arr options.
	 *
	 * @return array
	 */
	public function add_monetization_option( $arr ) {
		$arr[ self::MONETIZE_BY ] = __( 'Native', 'tutor' );

		return $arr;
	}

	/**
	 * Create & return payment gateway object
	 *
	 * Return instance expose setup_payment_and_redirect & get_webhook_data
	 * methods for creating payments.
	 *
	 * @since 3.0.0
	 *
	 * @param string $gateway Reference class of gateway.
	 *
	 * @throws \Exception Throw exception if error occur.
	 *
	 * @return GatewayBase
	 */
	public static function get_payment_gateway_object( string $gateway ) {
		try {
			return GatewayFactory::create( $gateway );
		} catch ( \Throwable $th ) {
			throw new \Exception( $th->getMessage() );
		}
	}

	/**
	 * Get payment gateways with reference class
	 *
	 * @since 3.0.0
	 *
	 * @param string $gateway gateway name.
	 *
	 * @return array|null
	 */
	public static function payment_gateways_with_ref( $gateway = null ) {
		$arr = array(
			'paypal' => array(
				'gateway_class' => PaypalGateway::class,
				'config_class'  => PaypalConfig::class,
			),
		);

		return is_null( $gateway ) ? $arr : $arr[ $gateway ] ?? null;
	}
}
