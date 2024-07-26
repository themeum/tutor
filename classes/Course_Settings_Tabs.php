<?php
/**
 * Manage Course Settings Tab
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Course Settings Tabls Class
 *
 * @since 2.0.0
 */
class Course_Settings_Tabs {
	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'tutor_save_course', array( $this, 'save_course' ), 10, 2 );
		add_action( 'tutor_save_course_settings', array( $this, 'save_course' ), 10, 2 );
	}


	/**
	 * On course save callback
	 *
	 * @since 2.0.0
	 *
	 * @param integer $post_ID post ID.
	 * @param object  $post post object.
	 *
	 * @return void
	 */
	public function save_course( $post_ID, $post ) {
		$course_settings = Input::post( '_tutor_course_settings', array(), Input::TYPE_ARRAY );

		if ( tutor_utils()->count( $course_settings ) ) {
			$existing = get_post_meta( $post_ID, '_tutor_course_settings', true );
			if ( ! is_array( $existing ) ) {
				$existing = array();
			}

			$meta = array_merge( $existing, $course_settings );
			update_post_meta( $post_ID, '_tutor_course_settings', $meta );
		}
	}
}
