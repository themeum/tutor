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

		new OrderController();
		new CouponController();
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
		$arr['tutor'] = __( 'Tutor', 'tutor' );

		return $arr;
	}
}
