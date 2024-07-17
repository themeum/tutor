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
use TUTOR\Input;

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

		if ( ! tutor_utils()->is_monetize_by_tutor() ) {
			return;
		}

		add_action( 'save_post_' . tutor()->course_post_type, array( $this, 'save_price' ), 10, 2 );

		new OrderController();
		new OrderActivitiesController();
		new CouponController();
		new HooksHandler();
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
		$arr[ self::MONETIZE_BY ] = __( 'Tutor', 'tutor' );

		return $arr;
	}

	/**
	 * Get default automate payment gateways
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_automate_payment_gateways(): array {
		$fields   = self::get_automate_payment_setting_fields();
		$gateways = array(
			'paypal' => array(
				'label'       => $fields[0]['label'],
				'icon'        => tutor()->url . 'assets/images/payment-gateways/paypal.svg',
				'package_url' => '',
				'is_active'   => true,
			),
			'stripe' => array(
				'label'       => $fields[1]['label'],
				'icon'        => tutor()->url . 'assets/images/payment-gateways/stripe.svg',
				'package_url' => '',
				'is_active'   => true,
			),
		);

		return apply_filters( 'tutor_automate_payment_gateways', $gateways );
	}

	/**
	 * Get default automate payment gateways
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_manual_payment_gateways(): array {
		$fields   = self::get_manual_payment_setting_fields();
		$gateways = array(
			array(
				'label'       => $fields[0]['label'],
				'icon'        => tutor()->url . 'assets/images/payment-gateways/bank-transfer.svg',
				'package_url' => '',
				'is_active'   => true,
			),
		);

		return apply_filters( 'tutor_manual_payment_gateways', $gateways );
	}

	/**
	 * Get automate payment setting fields
	 *
	 * @since 3.0.0
	 *
	 * @param bool $is_manual_payment is manual payment.
	 *
	 * @return array
	 */
	public static function get_automate_payment_setting_fields( $is_manual_payment = false ) {
		$fields = array(
			array(
				'key'     => OptionKeys::PAYMENT_METHOD_PAYPAL,
				'type'    => 'toggle_switch',
				'label'   => __( 'Paypal', 'tutor' ),
				'default' => 'off',
				'desc'    => __( 'Enable this to accept payments via PayPal.', 'tutor' ),
			),
			array(
				'key'     => OptionKeys::PAYMENT_METHOD_STRIPE,
				'type'    => 'toggle_switch',
				'label'   => __( 'Stripe', 'tutor' ),
				'default' => 'off',
				'desc'    => __( 'Enable this to accept payments via Stripe.', 'tutor' ),
			),
		);

		return $fields;
	}

	/**
	 * Get manual payment setting fields
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_manual_payment_setting_fields() {
		$fields = array(
			array(
				'key'     => OptionKeys::PAYMENT_METHOD_BANK_TRANSFER,
				'type'    => 'toggle_switch',
				'label'   => __( 'Bank Transfer', 'tutor' ),
				'default' => 'off',
				'desc'    => __( 'Enable this to accept payments via Bank Transfer.', 'tutor' ),
			),
		);

		return $fields;
	}
}
