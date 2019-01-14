<?php
/**
 * Tutor Multi Instructor
 */

namespace TUTOR_MT;

class MultiInstructors{

	public function __construct() {
		$is_enabled = tutor_utils()->get_option('enable_course_multi_instructors');
		if ( ! $is_enabled){
			return;
		}

		add_filter('tutor_course_instructors_html', array($this, 'course_multi_instructors'), 10, 2);
		add_filter('tutor_instructor_query_when_exists', array($this, 'tutor_instructor_query_when_exists'), 10, 1);
	}

	public function course_multi_instructors($instructor_output, $instructors){
		$instructor_output = '';
		foreach ($instructors as $instructor){
			$instructor_output .= "<p><label><input type='checkbox' name='tutor_instructor_ids[]' value='{$instructor->ID}' > {$instructor->display_name} </label></p>";
		}

		return $instructor_output;
	}

	public function tutor_instructor_query_when_exists($query){
		return "";
	}
	

}