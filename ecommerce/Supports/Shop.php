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

use Tutor\Ecommerce\OptionKeys;
use Tutor\Ecommerce\Supports\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Shop {
	public static function as_negative( string $price ) {
		return '−&thinsp;' . $price;
	}

	/**
	 * add the object properties with currency format.
	 * will search a key by the provided keys array and if exists and the value is numeric then
	 * create a new key with the _with_currency suffix and format the numeric value with currency symbol.
	 *
	 * @param object $data
	 * @param array  $keys
	 *
	 * @return object
	 */
	public static function format_with_currency( object $data, array $keys = array() ) {
		if ( empty( $keys ) ) {
			$keys = array_keys( (array) $data );
		}

		foreach ( $keys as $key ) {
			if ( ! isset( $data->$key ) || ! is_numeric( $data->$key ) ) {
				continue;
			}

			$new_key        = $key . '_with_currency';
			$data->$new_key = tutor_get_formatted_price( $data->$key );
		}

		return $data;
	}

	public static function calculate_taxable_amount( $price, $tax_rate ) {
		$price    = $price ?? 0;
		$tax_rate = $tax_rate ?? 0;

		return ( $price * $tax_rate ) / 100;
	}

	public static function add_taxable_prices( $final_price, $tax_rate ) {
		if ( empty( $final_price ) || ! is_object( $final_price ) ) {
			return $final_price;
		}

		$keys   = array( 'item_price', 'discounted_price' );
		$suffix = '_with_tax';
		$price  = clone $final_price;

		foreach ( $keys as $key ) {
			$new_key                   = $key . $suffix;
			$with_currency_key         = $new_key . '_with_currency';
			$price->$new_key           = $final_price->$key + self::calculate_taxable_amount( $final_price->$key, $tax_rate );
			$price->$with_currency_key = tutor_get_formatted_price( $price->$new_key );
		}

		return $price;
	}

	public static function recalculate_coupon_discount( $final_price, $is_coupon_applied ) {
		if ( empty( $final_price ) || ! is_object( $final_price ) ) {
			return $final_price;
		}

		$final_price->unit_discounted_price  = $is_coupon_applied ? $final_price->unit_item_price - $final_price->unit_discount_value : 0;
		$final_price->total_discounted_price = $is_coupon_applied ? $final_price->total_item_price - $final_price->total_discount_value : 0;

		$final_price = self::format_with_currency( $final_price, array( 'unit_discounted_price', 'total_discounted_price' ) );

		return $final_price;
	}

	public static function is_tax_enabled() {
		$tax_settings = settings::get_tax_settings();

		// if the tax is included to the product price then we do not need to calculate the tax
		return 0 === (int) $tax_settings->is_tax_included_in_price;
	}

	/**
	 * determines whether the price should be shown with tax.
	 *
	 * this function retrieves the tax settings from the settings_helper and
	 * checks if the tax is not included in the price and if the price should
	 * be displayed with tax. if the settings retrieval fails, it returns false.
	 *
	 * @return bool returns true if the price should be displayed with tax and
	 *              tax is not included in the price. otherwise, returns false.
	 */
	public static function is_price_displayed_with_tax() {
		$tax_settings        = settings::get_tax_settings();
		$show_price_with_tax = (bool) $tax_settings->show_price_with_tax;

		// return true if tax is not included in price but should be shown
		return ( self::is_tax_enabled() && $show_price_with_tax );
	}

	/**
	 * @todo remove this after make the system compatible as shipping tax is not required here.
	 *
	 * @return boolean
	 */
	public static function is_shipping_tax_enabled() {
		return false;
	}

	/**
	 * check if the coupon is enabled throughout the system.
	 *
	 * @todo need to update this from the settings if any. for now this is true for b/c
	 *
	 * @return boolean
	 */
	public static function is_coupon_enabled() {
		return tutor_utils()->get_option( OptionKeys::IS_COUPON_APPLICABLE );
	}

	/**
	 * the default price object structure.
	 *
	 * @param object $item the cart item.
	 *
	 * @since 3.0.0
	 *
	 * @return object
	 */
	public static function default_price( $item ) {
		return (object) array(
			'item_price'                => $item->item_price,
			'discount_value'            => 0,
			'discounted_price'          => 0,
			'item_price_with_tax'       => 0,
			'discounted_price_with_tax' => 0,
		);
	}

	public static function calculate_percentage( $price, $rate ) {
		return floatval( $price * $rate ) / 100;
	}

	public static function calculate_discount_value( $type, $value, $price ) {
		return 'percent' === $type
			? self::calculate_percentage( $price, $value )
			: (float) $value;
	}

	public static function calculate_discounted_price( $type, $value, $price ) {
		$discounted_price = self::calculate_discount_value( $type, $value, $price );
		return $price - $discounted_price;
	}
}
