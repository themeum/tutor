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
	 * Get country rate.
	 *
	 * @since 3.0.0
	 *
	 * @param string $country   the country code for which the tax rate needs to be found.
	 * @param string $state state name.
	 *
	 * @return float tax rate value.
	 */
	public static function get_country_state_tax_rate( $country, $state = null ) {
		$zero_tax = 0.0;

		if ( empty( $country ) ) {
			return $zero_tax;
		}

		$country_info = self::get_country_info( $country );
		if ( ! $country_info ) {
			return $zero_tax;
		}

		$country_code      = $country_info['numeric_code'] ?? '';
		$country_rate_data = null;

		$tax_settings = self::get_settings();
		if ( empty( $tax_settings->rates ) ) {
			return $zero_tax;
		}

		foreach ( $tax_settings->rates as $rate ) {
			if ( $rate->country === $country_code ) {
				$country_rate_data = $rate;
				break;
			}
		}

		if ( empty( $country_rate_data ) ) {
			return $zero_tax;
		}

		if ( $country_rate_data->is_same_rate ) {
			return floatval( $country_rate_data->rate );
		} else {
			// Get state rate.
			$state_info = self::get_state_info( $country_info['states'], $state );
			if ( empty( $state_info ) ) {
				return $zero_tax;
			}

			$state_rate = null;
			foreach ( $country_rate_data->states as $item ) {
				if ( $item->id === $state_info['id'] ) {
					$state_rate = floatval( $item->rate );
					break;
				}
			}

			return $state_rate;
		}

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

	/**
	 * Get state info of a country.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $states list of states of a country.
	 * @param string $state_name name of state.
	 *
	 * @return array|null
	 */
	public static function get_state_info( $states, $state_name ) {
		foreach ( $states as $state ) {
			if ( strtolower( $state['name'] ) === strtolower( $state_name ) ) {
				return $state;
			}
		}
	}
}
