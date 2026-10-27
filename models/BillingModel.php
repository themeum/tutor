<?php
/**
 * Billing Model
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Models;

use Tutor\Core\DB;

/**
 * Billing model class for performing billing functionalities
 */
class BillingModel {

	/**
	 * Fillable fields
	 *
	 * @var array
	 */
	private $fillable_fields = array(
		'billing_first_name',
		'billing_last_name',
		'billing_email',
		'billing_phone',
		'billing_zip_code',
		'billing_address',
		'billing_country',
		'billing_state',
		'billing_city',
	);

	/**
	 * Required fields
	 *
	 * @var array
	 */
	private $required_fields = array(
		'billing_first_name',
		'billing_last_name',
		'billing_email',
		'billing_phone',
		'billing_zip_code',
		'billing_address',
		'billing_country',
		'billing_state',
		'billing_city',
	);

	/**
	 * Get fillable fields
	 *
	 * @return array
	 */
	public function get_fillable_fields() {
		return $this->fillable_fields;
	}

	/**
	 * Get required fields
	 *
	 * @return array
	 */
	public function get_required_fields() {
		return $this->required_fields;
	}

	/**
	 * Insert billing info
	 *
	 * @param array $data Bulling info data.
	 *
	 * @return int The ID of the inserted row on success, or 0 on failure.
	 */
	public function insert( $data ) {
		return DB::table( 'tutor_customers' )->insert( $data );
	}

	/**
	 * Update billing info
	 *
	 * @param array $data Bulling info data.
	 * @param array $where Where condition.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function update( $data, $where ) {
		$column = '';
		$value  = null;
		if ( tutor_utils()->count( $where ) ) {
			$column = array_keys( $where )[0];
			$value  = $where[ $column ];
		}

		return DB::table( 'tutor_customers' )->where( $column, $value )->update( $data );
	}

	/**
	 * Get billing info
	 *
	 * @param int $user_id User ID.
	 *
	 * @return object|false The billing info as an object if found, or false if not found.
	 */
	public function get_info( $user_id ) {
		$billing_info = DB::table( 'tutor_customers' )->where( 'user_id', $user_id )->first();
		return $billing_info;
	}

	/**
	 * Check if billing info has all required fields.
	 *
	 * @since 4.0.2
	 *
	 * @param array $data Billing data to check.
	 *
	 * @return bool
	 */
	public function has_complete_billing_info( array $data ) {
		foreach ( $this->get_required_fields() as $field ) {
			if ( empty( $data[ $field ] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get formatted billing display data.
	 *
	 * @since 4.0.2
	 *
	 * @param array $data Billing data.
	 * @param array $country_options Country options list.
	 *
	 * @return array
	 */
	public function get_formatted_billing_display_data( array $data, array $country_options ) {
		$billing_country = $data['billing_country'] ?? '';
		$full_name       = trim( ( $data['billing_first_name'] ?? '' ) . ' ' . ( $data['billing_last_name'] ?? '' ) );
		$address_parts   = array_filter(
			array(
				$data['billing_address'] ?? '',
				$data['billing_city'] ?? '',
				$data['billing_state'] ?? '',
				$data['billing_zip_code'] ?? '',
				$country_options[ $billing_country ] ?? $billing_country,
			)
		);
		$address_line    = implode( ', ', $address_parts );

		return array_filter(
			array(
				'name'    => $full_name,
				'email'   => $data['billing_email'] ?? '',
				'address' => $address_line,
				'phone'   => $data['billing_phone'] ?? '',
			)
		);
	}
}
