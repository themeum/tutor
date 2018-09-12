<?php
/**
 * Template Class
 *
 * @since: v.1.0.0
 */
namespace LMS;


if ( ! defined( 'ABSPATH' ) )
	exit;


class Template {

	public function __construct() {
		add_filter( 'template_include', array($this, 'load_course_archive_template'), 99 );
		add_filter( 'template_include', array($this, 'load_single_course_template'), 99 );
	}

	/**
	 * Load default template for course
	 */
	public function load_course_archive_template($template){
		global $wp_query;

		$post_type = get_query_var('post_type');

		if ($post_type === 'course' && $wp_query->is_archive){
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
	 */
	public function load_single_course_template($template){
		global $wp_query;

		if ($wp_query->is_single && $wp_query->query_vars['post_type'] && $wp_query->query_vars['post_type'] === 'course'){
			$template = lms_get_template('single-course');
			return $template;
		}
		return $template;
	}

}