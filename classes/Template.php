<?php
/**
 * Template Class
 *
 * @since: v.1.0.0
 */
namespace LMS;


if ( ! defined( 'ABSPATH' ) )
	exit;


class Template extends LMS_Base {

	public function __construct() {
		parent::__construct();

		add_filter( 'template_include', array($this, 'load_course_archive_template'), 99 );
		add_filter( 'template_include', array($this, 'load_single_course_template'), 99 );
		add_filter( 'template_include', array($this, 'load_single_lesson_template'), 99 );
	}

	/**
	 * @param $template
	 *
	 * @return bool|string
	 *
	 * Load default template for course
	 *
	 * @since v.1.0.0
	 *
	 */
	public function load_course_archive_template($template){
		global $wp_query;

		$post_type = get_query_var('post_type');

		if ($post_type === $this->course_post_type && $wp_query->is_archive){
			$template = lms_get_template('archive-course');
			return $template;
		}
		return $template;
	}

	/**
	 * @param $template
	 *
	 * @return bool|string
	 *
	 * Load Single Course Template
	 *
	 * @since v.1.0.0
	 */
	public function load_single_course_template($template){
		global $wp_query;

		if ($wp_query->is_single && ! empty($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === $this->course_post_type){

			if (empty( $wp_query->query_vars['course_subpage'])) {
				$template = lms_get_template( 'single-course' );

				if ( is_user_logged_in() ) {
					if ( lms_utils()->is_enrolled() ) {
						$template = lms_get_template( 'single-course-enrolled' );
					}
				}
			}else{
				//If Course Subpage Exists
				if ( is_user_logged_in() ) {
					$template = lms_get_template( 'single-course-enrolled-'.$wp_query->query_vars['course_subpage']);
				}else{
					$template = lms_get_template('login');
				}
			}
			return $template;
		}
		return $template;
	}

	/**
	 * @param $template
	 *
	 * @return bool|string
	 *
	 * Load lesson template
	 *
	 * @since v.1.0.0
	 */

	public function load_single_lesson_template($template){
		global $wp_query;

		if ($wp_query->is_single && ! empty($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === $this->lesson_post_type){
			if (is_user_logged_in()){
				$is_course_enrolled = lms_utils()->is_course_enrolled_by_lesson();

				if ($is_course_enrolled) {
					$template = lms_get_template( 'single-lesson' );
				}else{
					//You need to enroll first
					$template = lms_get_template( 'single.lesson.required-enroll' );
				}
			}else{
				$template = lms_get_template('login');
			}
			return $template;
		}
		return $template;
	}

}