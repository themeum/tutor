<?php
namespace LMS;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Rewrite_Rules{
	public function __construct() {
		add_filter( 'query_vars', array($this, 'lms_register_query_vars') );
		add_action('generate_rewrite_rules', array($this, 'add_rewrite_rules'));

		//Lesson Permalink
		add_filter('post_type_link', array($this, 'change_lesson_single_url'), 1, 2);
	}


	public function lms_register_query_vars( $vars ) {
		$vars[] = 'course_subpage';
		return $vars;
	}

	public function add_rewrite_rules($wp_rewrite){
		$new_rules = array(
			//Lesson
			'course/(.+?)/lesson/(.+?)/?$' => 'index.php?post_type=lesson&name='. $wp_rewrite->preg_index(2),
		);

		//Nav Items
		$course_nav_items = lms_utils()->course_sub_pages();
		//$course_nav_items = array_keys($course_nav_items);

		if (is_array($course_nav_items) && count($course_nav_items)){
			foreach ($course_nav_items as $nav_key => $nav_item){
				$new_rules["course/(.+?)/{$nav_key}/?$"] ='index.php?post_type=course&name='.$wp_rewrite->preg_index(1).'&course_subpage='.$nav_key;
			}
		}

		$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
	}

	/**
	 * @param $post_link
	 * @param int $id
	 *
	 * @return string
	 *
	 * Change the lesson permalink
	 */
	function change_lesson_single_url($post_link, $id=0){
		$lesson = get_post($id);
		if( is_object($lesson) && $lesson->post_type == 'lesson'){
			global $wpdb;

			$course_id = get_post_meta($lesson->ID, '_lms_course_id_for_lesson', true);
			if ($course_id){
				$course = $wpdb->get_row("select {$wpdb->posts}.post_name from {$wpdb->posts} where ID = {$course_id} ");
				return home_url('/course/'.$course->post_name.'/lesson/'. $lesson->post_name.'/');
			}else{
				return home_url('/course/sample-course/lesson/'. $lesson->post_name.'/');
			}

		}
		return $post_link;
	}

}


