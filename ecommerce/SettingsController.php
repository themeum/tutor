<?php
/**
 * Manage Checkout
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

use Tutor\Traits\JsonResponse;

class SettingsController {
	use JsonResponse;

	/**
	 * Constructor.
	 *
	 * Initializes the Checkout class, sets the page title, and optionally registers
	 * hooks for handling AJAX requests related to cart data, bulk actions, cart updates,
	 * and cart deletions.
	 *
	 * @param bool $register_hooks Whether to register hooks for handling requests. Default is true.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function __construct( $register_hooks = true ) {
		if ( $register_hooks ) {
			add_action( 'wp_ajax_tutor_get_tax_settings', array( $this, 'get_tax_settings' ) );
		}
	}

	/**
	 * Get the tax settings from the tutor options.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function get_tax_settings() {
		$tax_settings = tutor_utils()->get_option( 'ecommerce_tax' );

		if ( ! empty( $tax_settings ) && is_string( $tax_settings ) ) {
			$tax_settings = json_decode( $tax_settings );
		}

		if ( ! empty( $tax_settings->active_country ) ) {
			$tax_settings->active_country = null;
		}

		$this->json_response( __( 'Success', 'tutor' ), $tax_settings );
	}
}
