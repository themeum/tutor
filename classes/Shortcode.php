<?php
/**
 * Class Shortcode
 * @package TUTOR
 *
 * @since v.1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Shortcode {

	public function __construct() {
		add_shortcode('tutor_student_registration_form', array($this, 'student_registration_form'));
		add_shortcode('tutor_dashboard', array($this, 'tutor_dashboard'));
		add_shortcode('tutor_instructor_registration_form', array($this, 'instructor_registration_form'));
		add_shortcode('tutor_course', array($this, 'tutor_course'));
	}

	/**
	 * @return mixed
	 *
	 * Instructor Registration Shortcode
	 *
	 * @since v.1.0.0
	 */
	public function student_registration_form(){
		ob_start();
		if (is_user_logged_in()){
			tutor_load_template( 'dashboard.logged-in' );
		}else{
			tutor_load_template( 'dashboard.registration' );
		}
		return apply_filters( 'tutor/student/register', ob_get_clean() );
	}

	/**
	 * @return mixed
	 *
	 * Tutor Dashboard for students
	 *
	 * @since v.1.0.0
	 */
	public function tutor_dashboard(){
		global $wp_query;

		ob_start();
		if (is_user_logged_in()){
			/**
			 * Added isset() Condition to avoid infinite loop since v.1.5.4
			 * This has cause error by others plugin, Such AS SEO
			 */

			if ( ! isset($wp_query->query_vars['tutor_dashboard_page'])){
				tutor_load_template( 'dashboard.index' );
			}
		}else{
			tutor_load_template( 'global.login' );
		}
		return apply_filters( 'tutor_dashboard/index', ob_get_clean() );
	}

	/**
	 * @return mixed
	 *
	 * Instructor Registration Shortcode
	 *
	 * @since v.1.0.0
	 */
	public function instructor_registration_form(){
		ob_start();
		if (is_user_logged_in()){
			tutor_load_template( 'dashboard.instructor.logged-in' );
		}else{
			tutor_load_template( 'dashboard.instructor.registration' );
		}
		return apply_filters( 'tutor_dashboard/student/index', ob_get_clean() );
	}

	/**
	 * @param $atts
	 *
	 * @return string
	 *
	 * Shortcode for getting course
	 */
	public function tutor_course($atts){
		$course_post_type = tutor()->course_post_type;

		$a = shortcode_atts( array(
			'post_type'         => $course_post_type,
			'post_status'       => 'publish',

			'id'       => '',
			'exclude_ids'       => '',
			'category'       => '',

			'orderby'           => 'ID',
			'order'             => 'DESC',
			'count'     => '6',
		), $atts );

		if ( ! empty($a['id'])){
			$ids = (array) explode(',', $a['id']);
			$a['post__in'] = $ids;
		}

		if ( ! empty($a['exclude_ids'])){
			$exclude_ids = (array) explode(',', $a['exclude_ids']);
			$a['post__not_in'] = $exclude_ids;
		}
		if ( ! empty($a['category'])){
			$category = (array) explode(',', $a['category']);

			$a['tax_query'] = array(
				array(
					'taxonomy' => 'course-category',
					'field'    => 'term_id',
					'terms'    => $category,
					'operator' => 'IN',
				),
			);
		}
		$a['posts_per_page'] = (int) $a['count'];

		wp_reset_query();
		query_posts($a);
		ob_start();
		tutor_load_template('shortcode.tutor-course');
		$output = ob_get_clean();
		wp_reset_query();

		return $output;
	}



}