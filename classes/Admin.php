<?php
namespace TUTOR;

/**
 * Class Admin
 * @package TUTOR
 *
 * @since v.1.0.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

class Admin{
	public function __construct() {
		add_action('admin_menu', array($this, 'register_menu'));
		add_action('admin_init', array($this, 'filter_posts_for_teachers'));

		add_action( 'load-post.php', array($this, 'check_if_current_users_post') );
	}

	public function register_menu(){
		add_menu_page(__('Tutor', 'tutor'), __('Tutor', 'tutor'), 'manage_tutor', 'tutor', array($this, 'tutor_page'), 'dashicons-welcome-learn-more', 2);

		add_submenu_page('tutor', __('Students', 'tutor'), __('Students', 'tutor'), 'manage_tutor', 'tutor-students', array($this, 'tutor_students') );
		
		
		add_submenu_page('tutor', __('Teachers', 'tutor'), __('Teachers', 'tutor'), 'manage_tutor', 'tutor-teachers', array($this, 'tutor_teachers') );
	}

	public function tutor_page(){
		$tutor_option = new Options();
		echo apply_filters('tutor/options/generated-html', $tutor_option->generate());
	}

	public function tutor_students(){
		include tutor()->path.'views/pages/students.php';
	}

	public function tutor_teachers(){
		include tutor()->path.'views/pages/teachers.php';
	}

	/**
	 * Filter posts for teacher
	 */
	public function filter_posts_for_teachers(){
		if (current_user_can(tutor()->teacher_role)){
			remove_menu_page( 'edit-comments.php' ); //Comments
			add_action( 'pre_get_posts', array($this, 'filter_posts_query_for_current_user') );
		}
	}

	/**
	 * @param $query
	 *
	 * Prevent unauthorised posts query at teacher panel
	 */
	public function filter_posts_query_for_current_user($query){
		$user_id = get_current_user_id();
		$query->set('author', $user_id);
	}

	/**
	 * Prevent unauthorised post edit page by direct URL
	 *
	 * @since v.1.0.0
	 */
	public function check_if_current_users_post(){
		if (! current_user_can(tutor()->teacher_role)) {
			return;
		}

		if (! empty($_GET['post']) ) {
			$get_post_id = (int) sanitize_text_field($_GET['post']);
			$get_post = get_post($get_post_id);
			$current_user = get_current_user_id();

			if ($get_post->post_author != $current_user){
				wp_die(__('Permission Denied', 'tutor'));
			}
		}
	}
}