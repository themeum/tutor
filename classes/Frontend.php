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
	}
	function tutor_offset_courses($query){
		if (!is_admin() && $query->is_main_query() && is_archive(tutor()->course_post_type)) $query->set('offset', 0);
	}
	/**
	 * Remove admin bar based on option
	 */
	function remove_admin_bar() {
		$hide_admin_bar_for_users = (bool) get_tutor_option( 'hide_admin_bar_for_users' );
		if ( ! current_user_can( 'administrator' ) && ! is_admin() && $hide_admin_bar_for_users ) {
			show_admin_bar( false );
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
