<?php
/**
 * Tax calculation class for tutor monetization.
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

use Tutor\Traits\JsonResponse;

/**
 * Class Tax
 *
 * @since 3.0.0
 */
class Tax {
	use JsonResponse;

	/**
	 * Register hooks and dependencies.
	 *
	 * @since 3.0.0
	 *
	 * @param boolean $register_hooks hook register or not.
	 */
	public function __construct( $register_hooks = true ) {
		if ( ! $register_hooks ) {
			return;
		}

		add_filter( 'tutor_option_input', array( $this, 'format_tax_data_before_save' ) );
		add_action( 'wp_ajax_tutor_get_tax_settings', array( $this, 'ajax_get_tax_settings' ) );
	}

	/**
	 * Format ecommerce tax setting data before save it to tutor settings.
	 *
	 * @param array $option option.
	 *
	 * @return array
	 */
	public function format_tax_data_before_save( $option ) {
		if ( ! empty( $option['ecommerce_tax'] ) ) {
			$option['ecommerce_tax'] = wp_unslash( $option['ecommerce_tax'] );
		}

		return $option;
	}

	/**
	 * Get the tax settings from the tutor options.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function ajax_get_tax_settings() {
		$tax_settings = self::get_settings();

		if ( ! empty( $tax_settings->active_country ) ) {
			$tax_settings->active_country = null;
		}

		$this->json_response( __( 'Success', 'tutor' ), $tax_settings );
	}

	/**
	 * Get tax settings.
	 *
	 * @since 3.0.0
	 *
	 * @return object
	 */
	public static function get_settings() {
		$tax_settings = tutor_utils()->get_option( 'ecommerce_tax' );

		if ( ! empty( $tax_settings ) && is_string( $tax_settings ) ) {
			$tax_settings = json_decode( $tax_settings );
		}

		return $tax_settings;
	}

	/**
	 * Get tax settings key data.
	 *
	 * @since 3.0.0
	 *
	 * @param string $key key.
	 * @param mixed  $default default value.
	 *
	 * @return mixed
	 */
	public static function get_setting( $key, $default = false ) {
		$tax_settings = self::get_settings();

		if ( ! empty( $tax_settings->$key ) ) {
			return $tax_settings->$key;
		}

		return $default;
	}

	/**
	 * Get tax rate.
	 *
	 * @since 3.0.0
	 *
	 * @param string $country country.
	 * @param string $state state.
	 * @param object $item item.
	 *
	 * @return number
	 */
	public static function get_tax_rate( $country, $state = null, $item = null ) {
		$zero_tax           = 0.0;
		$tax_rate           = 0.0;
		$is_product_taxable = isset( $item->is_product_taxable ) ? (bool) $item->is_product_taxable : true;

		if ( empty( $country ) || ! $is_product_taxable ) {
			return $zero_tax;
		}

		$country_rate_data = self::get_country_rate( $country );

		if ( empty( $country_rate_data ) ) {
			return $zero_tax;
		}

		// Find the tax rate from country and state.

		return $tax_rate;
	}


	/**
	 * Get country rate.
	 *
	 * @since 3.0.0
	 *
	 * @param string $country   the country code for which the tax rate needs to be found.
	 *
	 * @return object|null      the tax rate data for the country, or null if not found.
	 */
	public static function get_country_rate( $country ) {
		$country_info = self::get_country_info( $country );
		if ( ! $country_info ) {
			return null;
		}

		$country_code = $country_info['numeric_code'] ?? '';
		$rate_data    = null;

		$tax_settings = self::get_settings();
		if ( empty( $tax_settings->tax_rates ) ) {
			return null;
		}

		foreach ( $tax_settings->rates as $rate ) {
			if ( $rate->country === $country_code ) {
				$rate_data = $rate;
				break;
			}
		}

		return $rate_data;
	}

	/**
	 * Get country info by name.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name name of country.
	 *
	 * @return array|null
	 */
	public static function get_country_info( $name ) {
		$countries = tutor_get_country_list();
		foreach ( $countries as $country ) {
			if ( strtolower( $country['name'] ) === strtolower( $name ) ) {
				return $country;
			}
		}
	}
}
