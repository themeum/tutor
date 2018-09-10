<?php
namespace LMS;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Rewrite_Rules{
	public function __construct() {
		add_action('generate_rewrite_rules', array($this, 'add_rewrite_rules'));

		add_filter('post_type_link', array($this, 'change_lesson_single_url'), 1, 2);
	}

	public function add_rewrite_rules($wp_rewrite){
		$new_rules = array(
			'course/(.+?)/lesson/(.+?)/?$' => 'index.php?post_type=lesson&name='. $wp_rewrite->preg_index(2),
		);

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
		$post = get_post($id);
		if( is_object($post) && $post->post_type == 'lesson'){
			$course_id = (int) get_post_meta(get_the_ID(), '_lms_course_id_for_lesson', true);
			if ($course_id){
				$course = get_post($course_id);
				if ($course){
					return home_url('/course/'.$course->post_name.'/lesson/'. $post->post_name.'/');
				}
			}else{
				return home_url('/course/sample-course/lesson/'. $post->post_name.'/');
			}

		}
		return $post_link;
	}


}


