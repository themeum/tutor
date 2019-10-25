<?php
/**
 * Created by PhpStorm.
 * User: mhshohel
 * Date: 22/10/19
 * Time: 12:57 PM
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;


class MigrationLP {

	public function __construct() {
		add_filter('tutor_tool_pages', array($this, 'tutor_tool_pages'));

		add_action('tutor_option_save_after', array($this, 'tutor_option_save_after'));
		add_action('init', array($this, 'check_if_maintenance'));
	}

	public function tutor_tool_pages($pages){
		$hasLPdata = get_option('learnpress_version');

		if ($hasLPdata){
			$pages['migration_lp'] = __('LearnPress Migration', 'tutor');
		}

		return $pages;
	}


	public function migrate_course($course_id){
		global $wpdb;

		$course = learn_press_get_course($course_id);
		$curriculum = $course->get_curriculum() ;

		$lesson_post_type = tutor()->lesson_post_type;
		$course_post_type = tutor()->course_post_type;

		$tutor_course = array();
		$i = 0;
		foreach ( $curriculum as $section ) {
			$i++;

			$topic = array(
				'post_type'     => 'topics',
				'post_title'    => $section->get_title(),
				'post_content'  => $section->get_description(),
				'post_status'   => 'publish',
				'post_author'   => $course->get_author('id'),
				'post_parent'   => $course_id,
				'menu_order'    => $i,
				'items'         => array()
			);

			$lessons = $section->get_items();
			foreach ($lessons as $lesson){
				$item_post_type = learn_press_get_post_type( $lesson->get_id() );

				if ($item_post_type !== 'lp_lesson'){
					if ($item_post_type === 'lp_quiz'){
						$lesson_post_type = 'tutor_quiz';
					}
				}

				$tutor_lessons = array(
					'ID'    => $lesson->get_id(),
					'post_type'    => $lesson_post_type,
					'post_parent'  => '{topic_id}',
				);

				$topic['items'][] = $tutor_lessons;
			}

			$tutor_course[] = $topic;
		}


		if (tutils()->count($tutor_course)){
			foreach ($tutor_course as $course_topic){

				//Remove items from this topic
				$lessons = $course_topic['items'];
				unset($course_topic['items']);

				//Insert Topic post type
				$topic_id = wp_insert_post( $course_topic );

				//Update lesson from LearnPress to TutorLMS
				foreach ($lessons as $lesson){
					$lesson['post_parent'] = $topic_id;
					wp_update_post($lesson);

					$lesson_id = tutils()->array_get('ID', $lesson);
					if ($lesson_id){
						update_post_meta( $lesson_id, '_tutor_course_id_for_lesson', $course_id );
					}
				}
			}
		}

		//Migrate Course
		$tutor_course = array(
			'ID'            => $course_id,
			'post_type'     => $course_post_type,
		);
		wp_update_post($tutor_course);
		update_post_meta($course_id, '_was_lp_course', true);

		$lp_enrollments = $wpdb->get_results( "SELECT lp_user_items.*,
        lp_order.ID as order_id,
        lp_order.post_date as order_time
          
        FROM {$wpdb->prefix}learnpress_user_items lp_user_items  
        LEFT JOIN {$wpdb->posts} lp_order ON lp_user_items.ref_id = lp_order.ID
        WHERE item_id = {$course_id} AND item_type = 'lp_course' AND status = 'enrolled'" );

		foreach ($lp_enrollments as $lp_enrollment){
			$user_id = $lp_enrollment->user_id;

			if ( ! tutils()->is_enrolled($course_id, $user_id)) {
				$order_time = strtotime($lp_enrollment->order_time);

				$title = __('Course Enrolled', 'tutor')." &ndash; ".date( get_option('date_format'), $order_time ).' @ '.date( get_option( 'time_format'), $order_time );
				$tutor_enrollment_data = array(
					'post_type'   => 'tutor_enrolled',
					'post_title'  => $title,
					'post_status' => 'completed',
					'post_author' => $user_id,
					'post_parent' => $course_id,
				);

				$isEnrolled = wp_insert_post( $tutor_enrollment_data );

				if ($isEnrolled){
					//Mark Current User as Students with user meta data
					update_user_meta( $user_id, '_is_tutor_student', $order_time );
				}
			}
		}
	}

	public function tutor_option_save_after(){
		$maintenance_mode = (bool) get_tutor_option('enable_tutor_maintenance_mode');
		if ($maintenance_mode){
			tutor_maintenance_mode(true);
		}else{
			tutor_maintenance_mode();
		}
	}

	public function check_if_maintenance(){
		if ( ! is_admin()) {
			$maintenance_mode = (bool) get_tutor_option( 'enable_tutor_maintenance_mode' );
			if ( ! $maintenance_mode){
				return;
			}

			header( 'Retry-After: 600' );
			tutor_alert(__('Briefly unavailable for scheduled maintenance. Check back in a minute.', 'tutor'));
			die();
		}

	}



}