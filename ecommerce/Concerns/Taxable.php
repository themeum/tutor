<?php

/**
 * Taxable trait for handling tax calculations.
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce\Concerns;

use Tutor\Ecommerce\Constants\CountryCodes;
use Tutor\Ecommerce\Supports\Arr;
use Tutor\Ecommerce\Supports\Settings;


trait Taxable {
	public function get_tax_rate( $country, $state = null, $item = null ) {
		$category_id        = ! empty( $item->catid ) ? $item->catid : null;
		$is_product_taxable = isset( $item->is_product_taxable ) ? (bool) $item->is_product_taxable : true;
		$tax_rates          = settings::get_tax_settings();

		$zero_tax = 0.0;

		if ( empty( $country ) || empty( $tax_rates->rates ) ) {
			return $zero_tax;
		}

		// check if the country is eu
		$is_eu_country = $this->is_eu_country( $country );
		$rate_data     = $this->find_country_rate( $tax_rates, $country, $is_eu_country );

		if ( empty( $rate_data ) ) {
			return $zero_tax;
		}

		$tax_rate = $this->calculate_tax_rate( $rate_data, $country, $state, $category_id );

		if ( ! $is_product_taxable ) {
			return 0;
		}

		return $tax_rate;
	}

	/**
	 * get the product tax rate.
	 *
	 * @param  object $item single product item
	 * @return mixed
	 */
	public function get_product_tax_rate( $item ) {
		// @todo: need to get the country and state from the logged in user's country and state, or some other address.
		// need to discuss about it.
		$country = null;
		$state   = null;

		$tax_rate = $this->get_tax_rate( $country, $state, $item );

		return $tax_rate->product_tax_rate;
	}

	/**
	 * apply the tax value on product price
	 *
	 * @param  mixed $price product price
	 * @param  mixed $tax_rate tax rate
	 * @return mixed
	 */
	public function apply_tax_on_product_price( $price, $tax_rate ) {
		return $price + ( ( $price * $tax_rate ) / 100 );
	}

	/**
	 * get taxable amount of a product
	 *
	 * @param  mixed $price product price
	 * @param  mixed $tax_rate tax rate
	 * @return mixed
	 */
	public function get_taxable_amount( $price, $tax_rate ) {
		return ( $price * $tax_rate ) / 100;
	}

	/**
	 * modify the product object with formatted tax value.
	 *
	 * @param  object $item product entries
	 *
	 * @return object
	 */
	public function modify_product_price_with_tax( $item ) {
		$item->taxable_price               = ( $item->has_sale && $item->discount_value ) ? $this->apply_tax_on_product_price( $item->discounted_price, $item->tax_rate ) : $this->apply_tax_on_product_price( $item->regular_price, $item->tax_rate );
		$item->taxable_price_with_currency = tutor_get_formatted_price( $item->taxable_price );

		return $item;
	}

		/**
		 * modify the product object with formatted tax value.
		 *
		 * @param  object $item product entries
		 *
		 * @return object
		 */
	public function modify_product_min_price_with_tax( $item ) {
		$item->taxable_min_price               = ( $item->has_sale && $item->discount_value ) ? $this->apply_tax_on_product_price( $item->discounted_min_price, $item->tax_rate ) : $this->apply_tax_on_product_price( $item->min_price, $item->tax_rate );
		$item->taxable_min_price_with_currency = tutor_get_formatted_price( $item->taxable_min_price );

		return $item;
	}

	/**
	 * checks if a given country code belongs to an eu country.
	 *
	 * this function checks if the provided country code (in numeric format)
	 * corresponds to a country that is part of the european union.
	 *
	 * @param string $country_code the numeric iso 3166-1 country code to check.
	 *
	 * @return bool returns true if the country code belongs to an eu country, false otherwise.
	 */
	public function is_eu_country( $country_code ) {
		$eu_country_codes = array(
			CountryCodes::AUSTRIA,
			CountryCodes::BELGIUM,
			CountryCodes::BULGARIA,
			CountryCodes::CROATIA,
			CountryCodes::CYPRUS,
			CountryCodes::CZECH_REPUBLIC,
			CountryCodes::DENMARK,
			CountryCodes::ESTONIA,
			CountryCodes::FINLAND,
			CountryCodes::FRANCE,
			CountryCodes::GERMANY,
			CountryCodes::GREECE,
			CountryCodes::HUNGARY,
			CountryCodes::IRELAND,
			CountryCodes::ITALY,
			CountryCodes::LATVIA,
			CountryCodes::LITHUANIA,
			CountryCodes::LUXEMBOURG,
			CountryCodes::MALTA,
			CountryCodes::NETHERLANDS,
			CountryCodes::POLAND,
			CountryCodes::PORTUGAL,
			CountryCodes::ROMANIA,
			CountryCodes::SLOVAKIA,
			CountryCodes::SLOVENIA,
			CountryCodes::SPAIN,
			CountryCodes::SWEDEN,
		);

		return in_array( $country_code, $eu_country_codes, true );
	}


	/**
	 * finds the tax rate data for a given country, checking if it's an eu or non-eu country.
	 *
	 * @param object $tax_rates  the collection of tax rate data.
	 * @param string $country   the country code for which the tax rate needs to be found.
	 * @param bool   $is_eu_country      whether the country is part of the eu.
	 *
	 * @return object|null      the tax rate data for the country, or null if not found.
	 */
	public function find_country_rate( $tax_rates, $country, $is_eu_country ) {
		return arr::make( $tax_rates->rates )->find(
			function ( $item ) use ( $country, $is_eu_country ) {
				return $is_eu_country ? $item->country === CountryCodes::EUROPEAN_UNION : $item->country === $country;
			}
		);
	}

	/**
	 * calculates the applicable tax rate and shipping tax based on country, state, and product category.
	 *
	 * @param object      $rate_data  the tax rate data for a country or state.
	 * @param string      $country   the country code for which the tax rate is being calculated.
	 * @param string|null $state optional state or region code (if applicable).
	 * @param string|null $category optional product category to check for tax overrides.
	 *
	 * @return object           an object containing the calculated tax rate, shipping tax, and apply_on_shipping flag.
	 */
	public function calculate_tax_rate( $rate_data, $country, $state, $category_id ) {
		$zero_tax = 0.0;

		$location = ! empty( $state ) ? $state : $country;

		if ( $this->is_eu_country( $country ) ) {
			$location = $country;
		}

		// handle same rate case
		if ( $rate_data->is_same_rate || empty( $rate_data->states ) ) {
			return $this->get_rate_data( $rate_data, $category_id );
		}

		if ( empty( $location ) ) {
			return $zero_tax;
		}

		// handle different state-specific rates
		if ( ! empty( $rate_data->states ) ) {
			if ( $this->is_eu_country( $location ) && $this->is_micro_business_vat( $rate_data ) ) {
				return $this->get_rate_data( $rate_data->states[0], $category_id );
			}

			$state_rate_data = Arr::make( $rate_data->states )->find(
				function ( $item ) use ( $location ) {
					return (string) $item->id === (string) $location;
				}
			);

			if ( empty( $state_rate_data ) ) {
				return $zero_tax;
			}

			return $this->get_rate_data( $state_rate_data, $category_id );
		}

		return $zero_tax;
	}

	/**
	 * handle the case when the rate is the same.
	 */
	public function get_rate_data( $rate_data, $category_id ) {
		$tax_rate = floatval( $rate_data->rate ?? 0 );

		if ( ! empty( $rate_data->override_values ) ) {
			$product_override = arr::make( $rate_data->override_values )->find(
				function ( $item ) use ( $category_id ) {
					return $item->override_on === 'products' && $item->category === $category_id;
				}
			);

			if ( ! empty( $product_override ) ) {
				$tax_rate = (float) $product_override->rate;
			}
		}

		return $tax_rate;
	}

	/**
	 * check if vat type is one-stop.
	 *
	 * @return bool
	 */
	public function is_one_stop_vat( $rate_data ) {
		return isset( $rate_data->vat_registration_type ) && $rate_data->vat_registration_type === 'one-stop';
	}

	/**
	 * check if vat type is micro-business.
	 *
	 * @return bool
	 */
	public function is_micro_business_vat( $rate_data ) {
		return isset( $rate_data->vat_registration_type ) && $rate_data->vat_registration_type === 'micro-business';
	}
}
