<?php
/**
 * Payment gateway factory class
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\PaymentGateways;

/**
 * Create object of a payment gateway
 */
abstract class GatewayFactory {

	/**
	 * Create an instance of the specified payment gateway.
	 *
	 * @param string $gateway The fully qualified class name of the gateway.
	 * @return GatewayBase
	 * @throws \InvalidArgumentException If the class does not exist or is not an instance of GatewayBase.
	 */
	public static function create( string $gateway ): GatewayBase {
		if ( ! class_exists( $gateway ) ) {
			throw new \InvalidArgumentException( "Gateway class {$gateway} does not exist." );
		}

		$obj = new $gateway();

		// Ensure the object is an instance of GatewayBase.
		if ( ! $obj instanceof GatewayBase ) {
			throw new \InvalidArgumentException( "Gateway {$gateway} must be an instance of GatewayBase." );
		}

		return $obj;
	}
}

