<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Rewrite_Rules extends Tutor_Base {


	public function __construct() {
		parent::__construct();

		add_filter( 'query_vars', array($this, 'tutor_register_query_vars') );
		add_action('generate_rewrite_rules', array($this, 'add_rewrite_rules'));

		//Lesson Permalink
		add_filter('post_type_link', array($this, 'change_lesson_single_url'), 1, 2);

	}


	public function tutor_register_query_vars( $vars ) {
		$vars[] = 'course_subpage';
		$vars[] = 'lesson_video';
		$vars[] = 'tutor_dashboard_page';
		return $vars;
	}

	public function add_rewrite_rules($wp_rewrite){
		$new_rules = array(
			//Lesson
			$this->course_post_type."/(.+?)/{$this->lesson_post_type}/(.+?)/?$" => "index.php?post_type={$this->lesson_post_type}&name=". $wp_rewrite->preg_index(2),

			"video-url/(.+?)/?$" => "index.php?post_type={$this->lesson_post_type}&lesson_video=true&name=". $wp_rewrite->preg_index(1),
		);

		//Nav Items
		$course_nav_items = tutor_utils()->course_sub_pages();
		//$course_nav_items = array_keys($course_nav_items);

		if (is_array($course_nav_items) && count($course_nav_items)){
			foreach ($course_nav_items as $nav_key => $nav_item){
				$new_rules[$this->course_post_type."/(.+?)/{$nav_key}/?$"] ='index.php?post_type=course&name='.$wp_rewrite->preg_index(1).'&course_subpage='.$nav_key;
			}
		}

		//Student Dashboard URL
		$dashboard_pages = tutor_utils()->tutor_student_dashboard_pages();
		foreach ($dashboard_pages as $dashboard_key => $dashboard_page){
			$new_rules["(.+?)/{$dashboard_key}/?$"] ='index.php?pagename='.$wp_rewrite->preg_index(1).'&tutor_dashboard_page=' .$dashboard_key;
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
		if( is_object($lesson) && $lesson->post_type == $this->lesson_post_type){
			global $wpdb;

			$course_id = get_post_meta($lesson->ID, '_tutor_course_id_for_lesson', true);
			if ($course_id){
				$course = $wpdb->get_row("select {$wpdb->posts}.post_name from {$wpdb->posts} where ID = {$course_id} ");
				return home_url("/{$this->course_post_type}/".$course->post_name."/{$this->lesson_post_type}/". $lesson->post_name.'/');
			}else{
				return home_url("/{$this->course_post_type}/sample-course/{$this->lesson_post_type}/". $lesson->post_name.'/');
			}

		}
		return $post_link;
	}

}


