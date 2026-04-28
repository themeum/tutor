<?php
/**
 * Enrollment Model
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Models;

/**
 * Class EnrollmentModel
 *
 * @since 4.0.0
 */
class EnrollmentModel {
	/**
	 * Enrollment status constants
	 *
	 * @var string
	 */
	const STATUS_COMPLETED = 'completed';
	const STATUS_PENDING   = 'pending';
	const STATUS_CANCEL    = 'cancel';
}
