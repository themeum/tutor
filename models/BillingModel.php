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
use Tutor\Core\Input;
use Tutor\Ecommerce\Settings;
use Tutor\Helpers\QueryHelper;

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
	 * Fields a partial update is allowed to change
	 *
	 * @since 4.2.2
	 *
	 * @var array
	 */
	private $partial_update_fields = array(
		'billing_country',
		'billing_state',
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
	 * Get validation rules
	 *
	 * @since 4.3.0
	 *
	 * @return array Validation rules.
	 */
	public function get_validation_rules() {
		/**
		 * The `billing_state` field is excluded because not all countries have states.
		 * It is checked by validate_billing_location().
		 */
		$rules = array(
			'billing_first_name' => 'required',
			'billing_last_name'  => 'required',
			'billing_email'      => 'required|email',
		);

		foreach ( array( 'billing_phone', 'billing_zip_code', 'billing_address', 'billing_country', 'billing_city' ) as $field ) {
			$rules[ $field ] = Settings::is_billing_field_required( $field ) ? 'required' : '';
		}

		return $rules;
	}

	/**
	 * Get the billing fields that are required by the current settings.
	 *
	 * @since 4.3.0
	 *
	 * @return string[]
	 */
	public function get_active_required_fields() {
		return array_values(
			array_filter(
				$this->required_fields,
				function ( $field ) {
					return Settings::is_billing_field_required( $field );
				}
			)
		);
	}

	/**
	 * Validate the billing country and state.
	 *
	 * Prevents tax reduction abuse by rejecting unknown countries and states
	 * that don't belong to the given country.
	 *
	 * @since 4.3.0
	 *
	 * @param string $country Billing country name.
	 * @param string $state   Billing state name.
	 *
	 * @return string[] List of error messages, empty when valid.
	 */
	public function validate_billing_location( string $country, string $state ): array {
		// An optional country may be left empty, but then a state makes no sense.
		if ( '' === $country && ! Settings::is_billing_field_required( 'billing_country' ) ) {
			return '' === $state ? array() : array( __( 'Invalid state', 'tutor' ) );
		}

		$country_info = tutor_get_country_info_by_name( $country );

		if ( ! $country_info ) {
			return array( __( 'Invalid country', 'tutor' ) );
		}

		$state_names = array_column( $country_info['states'] ?? array(), 'name' );

		// An optional state may be left empty.
		if ( '' === $state && ! Settings::is_billing_field_required( 'billing_state' ) ) {
			return array();
		}

		// Countries without states don't require one.
		if ( ! empty( $state_names ) && ! in_array( $state, $state_names, true ) ) {
			return array( __( 'Invalid state', 'tutor' ) );
		}

		return array();
	}

	/**
	 * Insert billing info
	 *
	 * @param array $data Bulling info data.
	 *
	 * @return int The ID of the inserted row on success, or 0 on failure.
	 */
	public function insert( $data ) {
		global $wpdb;

		return QueryHelper::insert(
			"{$wpdb->prefix}tutor_customers",
			$data,
		);
	}

	/**
	 * Update billing info
	 *
	 * @since 4.2.2 New Tutor\Core\DB used instead of the deprecated one.
	 *
	 * @param array $data Bulling info data.
	 * @param array $where Where condition.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function update( $data, $where ) {

		$data = Input::sanitize_array( $data );

		try {
			$updated = DB::table( 'tutor_customers' );
			foreach ( $where as $key => $value ) {
				$updated->where( $key, $value );
			}

			$result = $updated->update( $data );
			if ( ! $result ) {
				tutor_log( __( 'Failed to update user profile.', 'tutor' ) );
				return false;
			}

			return true;
		} catch ( \Throwable $e ) {
			tutor_log( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Get billing info
	 *
	 * @param int $user_id User ID.
	 *
	 * @return object|false The billing info as an object if found, or false if not found.
	 */
	public function get_info( $user_id ) {
		global $wpdb;

		$billing_info = QueryHelper::get_row(
			"{$wpdb->prefix}tutor_customers",
			array(
				'user_id' => $user_id,
			),
			'id'
		);
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
		foreach ( $this->get_active_required_fields() as $field ) {
			if ( 'billing_state' === $field && ! $this->country_has_states( $data['billing_country'] ?? '' ) ) {
				continue;
			}

			if ( empty( $data[ $field ] ) ) {
				return false;
			}
		}

		$format_errors = self::validate_field_formats( $data );
		if ( ! empty( $format_errors ) ) {
			return false;
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

	/**
	 * Check whether a country has states to choose from.
	 *
	 * @since 4.2.2
	 *
	 * @param string $country Country name.
	 *
	 * @return bool
	 */
	public function country_has_states( string $country ) {
		$country_info = tutor_get_country_info_by_name( $country );

		return ! empty( $country_info['states'] );
	}

	/**
	 * Get the fields a partial update is allowed to change.
	 *
	 * @since 4.2.2
	 *
	 * @return array
	 */
	public function get_partial_update_fields() {
		return $this->partial_update_fields;
	}

	/**
	 * Check billing values against their allowed formats.
	 *
	 * @since 4.3.0
	 *
	 * @param array $data Un-slashed billing data keyed by field name.
	 *
	 * @return array<string, string[]> Errors keyed by field name.
	 */
	public static function validate_field_formats( array $data ): array {
		$errors = array();

		foreach ( self::get_field_formats() as $field => $format ) {
			$value = trim( (string) ( $data[ $field ] ?? '' ) );
			if ( '' === $value ) {
				continue;
			}

			if ( ! preg_match( '~^(?:' . $format['pattern'] . ')$~u', $value ) ) {
				$errors[ $field ][] = $format['message'];
			}
		}

		return $errors;
	}

	/**
	 * Allowed character formats for billing fields.
	 *
	 * @since 4.3.0
	 *
	 * @return array<string, array{pattern: string, message: string}>
	 */
	public static function get_field_formats(): array {
		// Matches one or more letters (any language), combining marks, spaces, apostrophes, dots, or hyphens.
		$name_pattern = "[\\p{L}\\p{M}\\s'.\\-]+";

		$formats = array(
			'billing_first_name' => array(
				'pattern' => $name_pattern,
				'message' => __( 'First name can only contain letters, spaces, apostrophes, dots and hyphens.', 'tutor' ),
			),
			'billing_last_name'  => array(
				'pattern' => $name_pattern,
				'message' => __( 'Last name can only contain letters, spaces, apostrophes, dots and hyphens.', 'tutor' ),
			),
			'billing_phone'      => array(
				'pattern' => '\\+?[0-9\\s\\(\\)\\-]{6,20}',
				'message' => __( 'Phone can only contain digits, spaces, parentheses, hyphens and a leading +.', 'tutor' ),
			),
			'billing_city'       => array(
				'pattern' => "[\\p{L}\\p{M}\\p{N}\\s'.\\-]+",
				'message' => __( 'City can only contain letters, spaces, apostrophes, dots, numbers and hyphens.', 'tutor' ),
			),
			'billing_zip_code'   => array(
				'pattern' => '[A-Za-z0-9\\s\\-]{2,12}',
				'message' => __( 'Postcode/ZIP can only contain letters, digits, spaces and hyphens.', 'tutor' ),
			),
			'billing_address'    => array(
				'pattern' => "[\\p{L}\\p{M}\\p{N}\\s,.#&'\\/\\(\\)\\-]+",
				'message' => __( 'Address can only contain letters, digits, spaces and , . # / \' ( ) -', 'tutor' ),
			),
		);

		/**
		 * Filters the allowed character formats for billing fields.
		 *
		 * @since 4.3.0
		 *
		 * @param array $formats Field name => array( 'pattern' => string, 'message' => string ).
		 */
		return apply_filters( 'tutor_billing_field_formats', $formats );
	}
}
