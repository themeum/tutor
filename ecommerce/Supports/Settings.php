<?php

/**
 * Settings support class.
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce\Supports;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Settings {
	// @TODO: this is a temporary method
	public static function get_tax_settings() {
		return (object) array(
			'rates'                    => array(
				(object) array(
					'country'      => '248',
					'is_same_rate' => false,
					'rate'         => 20,
					'states'       => array(),
				),
				(object) array(
					'country'      => '050',
					'is_same_rate' => true,
					'rate'         => 15,
					'states'       => array(),
				),
				(object) array(
					'country'               => '000',
					'is_same_rate'          => false,
					'rate'                  => 0,
					'states'                => array(
						(object) array(
							'id'                => '040',
							'rate'              => 20,
							'apply_on_shipping' => false,
						),
					),
					'vat_registration_type' => 'micro-business',
				),
			),
			'apply_tax_on'             => 'product',
			'is_tax_included_in_price' => 0,
			'show_price_with_tax'      => true,
			'charge_tax_on_shipping'   => true,
		);
	}
}
