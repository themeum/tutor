<?php
/**
 * Frontend class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since v.1.5.2
 */


namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Frontend {

	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'remove_admin_bar' ) );
		add_filter( 'nav_menu_link_attributes', array( $this, 'add_menu_atts' ), 10, 3 );
		// add_action('pre_get_posts', array($this, 'tutor_offset_courses'));
		add_action( 'admin_init', array( $this, 'restrict_wp_admin_area' ) );
	}

	function tutor_offset_courses($query){
		if (!is_admin() && $query->is_main_query() && is_archive(tutor()->course_post_type)) $query->set('offset', 0);
	}

	/**
	 * Check current user has admin area access for tutor
	 *
	 * @return boolean
	 * 
	 * @since 2.0.7
	 */
	private function has_admin_area_access() {
		$has_access 	= true;
		$user 			= new \WP_User( get_current_user_id() );
		$roles			= $user->roles;

		if ( ! in_array( 'administrator', $roles ) && ( in_array( 'subscriber', $roles ) || in_array( tutor()->instructor_role, $roles ) ) ) 
		{
			$has_access = false;
		}

		return $has_access;
	}

	/**
	 * Remove admin bar based on option
	 */
	function remove_admin_bar() {
		$hide_admin_bar_for_users	= (bool) get_tutor_option( 'hide_admin_bar_for_users' );
		$has_access					= $this->has_admin_area_access();

		if ( ! $has_access && $hide_admin_bar_for_users ) {
			show_admin_bar( false );
		}
	}

	/**
	 * Restrict the WP admin area for student, instructor
	 *
	 * @return void
	 */
	public function restrict_wp_admin_area() {
		$hide_admin_bar_for_users	= (bool) get_tutor_option( 'hide_admin_bar_for_users' );
		$has_access					= $this->has_admin_area_access();

		if ( $hide_admin_bar_for_users && ! $has_access && ! wp_doing_ajax() ) {
			wp_die( __( 'Access Denied!', 'tutor' ) );
		}
	}

	/**
	 * add_menu_atts
	 *
	 * @param  mixed $atts
	 * @param  mixed $item
	 * @param  mixed $args
	 * @return void
	 */
	function add_menu_atts( $atts, $item, $args ) {
		$atts['onClick'] = 'return true';
		return $atts;
	}
}
