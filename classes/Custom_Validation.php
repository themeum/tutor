<?php
/**
 * Reuseable custom validation trait
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR;

/**
 * Custom Valaidation Trait
 *
 * @since 2.0.0
 */
trait Custom_Validation {

	/**
	 * Check whether order value is asc or desc
	 *
	 * @since 2.0.0
	 *
	 * @param string $order sorting order.
	 * @return bool
	 */
	public function validate_order( $order ) {
		return in_array( strtolower( $order ), array( 'asc', 'desc' ) );
	}
}

