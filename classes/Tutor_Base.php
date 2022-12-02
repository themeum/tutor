<?php
/**
 * Tutor base class
 *
 * @package Tutor\Base
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Tutor base class
 *
 * @since 1.0.0
 */
class Tutor_Base {

	/**
	 * Course post type
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $course_post_type;

	/**
	 * Lesson post type
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $lesson_post_type;

	/**
	 * Lesson permalink
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $lesson_base_permalink;

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->course_post_type = tutor()->course_post_type;
		$this->lesson_post_type = tutor()->lesson_post_type;

		// Lesson Permalink.
		$this->lesson_base_permalink = tutor_utils()->get_option( 'lesson_permalink_base' );
		if ( ! $this->lesson_base_permalink ) {
			$this->lesson_base_permalink = $this->lesson_post_type;
		}

	}

}
