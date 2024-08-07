<?php
/**
 * Base Controller
 *
 * @package Tutor\BaseController
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base Controller
 */
abstract class BaseController {

	/**
	 * Get model object
	 *
	 * @since 3.0.0
	 * @return object
	 */
	abstract public function get_model();

	/**
	 * Get allowed field and optionally set empty values
	 * for the required fields
	 *
	 * @since 3.0.0
	 *
	 * @param array   $params Request params.
	 * @param boolean $set_required To set required fields empty values.
	 *
	 * @return array
	 */
	public function get_allowed_fields( array $params, $set_required = true ) {
		$fillable        = $this->get_model()->get_fillable_fields();
		$required_fields = $this->get_model()->get_required_fields();

		$allowed_params = array_intersect_key( $params, array_flip( $fillable ) );

		// Set empty value if set_required is true.
		if ( $set_required ) {
			$this->setup_required_fields( $allowed_params, $required_fields );
		}

		return $allowed_params;
	}

	/**
	 * Setup required fields on the request params
	 *
	 * @since 3.0.0
	 *
	 * @param array $params Reference request params.
	 * @param array $required_fields Required fields.
	 *
	 * @return void
	 */
	public function setup_required_fields( array &$params, $required_fields ) {
		foreach ( $required_fields as $field ) {
			if ( ! isset( $params[ $field ] ) ) {
				$params[ $field ] = '';
			}
		}
	}
}
