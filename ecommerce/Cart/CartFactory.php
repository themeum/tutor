<?php
/**
 * Cart factory for creating cart object
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.5.0
 */

namespace Tutor\Ecommerce\Cart;

use Tutor\Ecommerce\Cart\Contracts\CartInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CartFactory class
 *
 * @since 3.5.0
 */
class CartFactory {

	/**
	 * Create a cart object
	 *
	 * @since 3.5.0
	 *
	 * @throws \Exception If monetization engine is not valid.
	 *
	 * @param string $monetization_engine Monetization engine.
	 *
	 * @return CartInterface
	 */
	public static function create_cart( $monetization_engine ) : CartInterface {
		switch ( $monetization_engine ) {
			case 'wc':
				return new WooCart();
			case 'tutor':
				return new NativeCart();
			case 'edd':
				return new EddCart();
			default:
				throw new \Exception( 'Invalid monetization engine' );
		}
	}
}
