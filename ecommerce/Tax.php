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
	 * Tax type const.
	 */
	const TYPE_INCLUSIVE = 'inclusive';
	const TYPE_EXCLUSIVE = 'exclusive';

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
		tutor_utils()->checking_nonce();
		tutor_utils()->check_current_user_capability();

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
	 * Calculate tax.
	 *
	 * @since 3.0.0
	 *
	 * @param float $amount  amount.
	 * @param float $rate    tax rate.
	 *
	 * @return float
	 */
	public static function calculate_tax( $amount, $rate ) {
		if ( 0 === $rate ) {
			return $rate;
		}

		if ( self::is_tax_included_in_price() ) {
			// Tax = (Tax Rate X Price) / (1 + Tax Rate).
			$tax = $amount - ( $rate * $amount ) / ( 1 + $rate );
		} else {
			try {
				$tax = $amount * ( $rate / 100 );
			} catch ( \Throwable $th ) {
				$tax = 0.0;
			}
		}

		// Tax amount should not negative value.
		return max( 0, round( $tax, 2 ) );
	}

	/**
	 * Get text rate for a user according to billing country and state.
	 *
	 * @param integer $user_id user id.
	 *
	 * @return float tax rate.
	 */
	public static function get_user_tax_rate( $user_id = 0 ) {
		$billing_info    = ( new BillingController( false ) )->get_billing_info( $user_id );
		$billing_country = $billing_info->billing_country ?? '';
		$billing_state   = $billing_info->billing_state ?? '';

		return self::get_country_state_tax_rate( $billing_country, $billing_state );
	}

	/**
	 * Check site admin configured tax or not.
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public static function is_tax_configured() {
		$tax_settings = self::get_settings();

		return ( is_object( $tax_settings )
				&& isset( $tax_settings->rates )
				&& count( $tax_settings->rates ) );
	}

	/**
	 * Check tax is included in price or not.
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public static function is_tax_included_in_price() {
		return (bool) self::get_setting( 'is_tax_included_in_price' );
	}

	/**
	 * Show price with tax in course list and details.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public static function show_price_with_tax() {
		return (bool) self::get_setting( 'show_price_with_tax' );
	}

	/**
	 * Get tax type.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_tax_type() {
		return self::is_tax_included_in_price() ? self::TYPE_INCLUSIVE : self::TYPE_EXCLUSIVE;
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

		if ( $country_rate_data->is_same_rate || 0 === count( $country_rate_data->states ) ) {
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
