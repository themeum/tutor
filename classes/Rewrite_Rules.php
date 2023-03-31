<?php
/**
 * Manage rewrite rules for Tutor
 *
 * @package Tutor\RewriteRules
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate and manage rewrite rules
 *
 * @since 2.0.0
 */
class Rewrite_Rules extends Tutor_Base {

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'query_vars', array( $this, 'tutor_register_query_vars' ) );
		add_action( 'generate_rewrite_rules', array( $this, 'add_rewrite_rules' ) );

		// Lesson Permalink.
		add_filter( 'post_type_link', array( $this, 'change_lesson_single_url' ), 1, 2 );
	}

	/**
	 * Prepare query vars
	 *
	 * @since 1.0.0
	 *
	 * @param sting $vars url structure.
	 *
	 * @return array
	 */
	public function tutor_register_query_vars( $vars ) {
		$vars[] = 'course_subpage';
		$vars[] = 'lesson_video';
		$vars[] = 'tutor_dashboard_page';
		$vars[] = 'tutor_dashboard_sub_page';

		/**
		 * If public_profile_layout is not private then
		 * add rewrite rules
		 *
		 * @since v2.0.0
		 */
		$vars[] = 'tutor_profile_username';
		return $vars;
	}

	/**
	 * Tutor rewrite rules
	 *
	 * @since 1.0.0
	 *
	 * @param string $wp_rewrite get the rewrite rule.
	 *
	 * @return void
	 */
	public function add_rewrite_rules( $wp_rewrite ) {
		$new_rules = array(
			// Lesson Permalink.
			$this->course_post_type . "/(.+?)/{$this->lesson_base_permalink}/(.+?)/?$" => "index.php?post_type={$this->lesson_post_type}&name=" . $wp_rewrite->preg_index( 2 ),
			// Quiz Permalink.
			$this->course_post_type . '/(.+?)/tutor_quiz/(.+?)/?$' => 'index.php?post_type=tutor_quiz&name=' . $wp_rewrite->preg_index( 2 ),
			// Assignments URL.
			$this->course_post_type . '/(.+?)/assignments/(.+?)/?$' => 'index.php?post_type=tutor_assignments&name=' . $wp_rewrite->preg_index( 2 ),
			// Zoom Meeting.
			$this->course_post_type . '/(.+?)/zoom-meeting/(.+?)/?$' => 'index.php?post_type=tutor_zoom_meeting&name=' . $wp_rewrite->preg_index( 2 ),

			// Private Video URL.
			'video-url/(.+?)/?$' => "index.php?post_type={$this->lesson_post_type}&lesson_video=true&name=" . $wp_rewrite->preg_index( 1 ),
			// Student Public Profile URL.
			'profile/(.+?)/?$'   => 'index.php?tutor_profile_username=' . $wp_rewrite->preg_index( 1 ),
		);

		// Student Dashboard URL.
		$dashboard_pages     = tutor_utils()->tutor_dashboard_permalinks();
		$dashboard_page_id   = (int) tutor_utils()->get_option( 'tutor_dashboard_page_id' );
		$dashboard_page_slug = get_post_field( 'post_name', $dashboard_page_id );

		foreach ( $dashboard_pages as $dashboard_key => $dashboard_page ) {
			$new_rules[ "({$dashboard_page_slug})/{$dashboard_key}/?$" ] = 'index.php?pagename=' . $wp_rewrite->preg_index( 1 ) . '&tutor_dashboard_page=' . $dashboard_key;

			// Sub Page of dashboard sub page.
			// regext = ([^/]*).
			$new_rules[ "({$dashboard_page_slug})/{$dashboard_key}/(.+?)/?$" ] = 'index.php?pagename=' . $wp_rewrite->preg_index( 1 ) . '&tutor_dashboard_page=' . $dashboard_key . '&tutor_dashboard_sub_page=' . $wp_rewrite->preg_index( 2 );
		}

		$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
	}

	/**
	 * Change the lesson permalink
	 *
	 * @param string $post_link post link.
	 * @param int    $id post id.
	 *
	 * @return string
	 */
	public function change_lesson_single_url( $post_link, $id = 0 ) {
		$post             = get_post( $id );
		$course_base_slug = 'sample-course';

		if ( is_object( $post ) && $post->post_type == $this->lesson_post_type ) {
			// Lesson Permalink.
			$course_id = tutor_utils()->get_course_id_by( 'lesson', $post->ID );

			if ( $course_id ) {
				$course = get_post( $course_id );
				if ( $course ) {
					$course_base_slug = $course->post_name;
				}
				return home_url( "/{$this->course_post_type}/{$course_base_slug}/{$this->lesson_base_permalink}/" . $post->post_name . '/' );
			} else {
				return home_url( "/{$this->course_post_type}/sample-course/{$this->lesson_base_permalink}/" . $post->post_name . '/' );
			}
		} elseif ( is_object( $post ) && 'tutor_quiz' === $post->post_type ) {
			// Quiz Permalink.
			$course = get_post( $post->post_parent );
			if ( $course ) {
				// Checking if this topic.
				if ( $course->post_type !== $this->course_post_type ) {
					$course = get_post( $course->post_parent );
				}
				// Checking if this lesson.
				if ( isset( $course->post_type ) && $course->post_type !== $this->course_post_type ) {
					$course = get_post( $course->post_parent );
				}

				$course_post_name = isset( $course->post_name ) ? $course->post_name : 'sample-course';
				return home_url( "/{$this->course_post_type}/{$course_post_name}/tutor_quiz/{$post->post_name}/" );
			} else {
				return home_url( "/{$this->course_post_type}/sample-course/tutor_quiz/{$post->post_name}/" );
			}
		}
		return $post_link;
	}
}
