<?php
/**
 * Created by PhpStorm.
 * User: mhshohel
 * Date: 11/9/18
 * Time: 12:40 PM
 */

namespace LMS;


class Template {

	public function __construct() {
		add_filter( 'template_include', array($this, 'load_course_archive_template'), 99 );
	}

	/**
	 * Load default template for course
	 */
	public function load_course_archive_template($template){
		global $wp_query;

		$post_type = get_query_var('post_type');

		if ($post_type === 'course' && $wp_query->is_archive){
			$template = lms_get_template('courses');
			return $template;
		}
		return $template;
	}

}