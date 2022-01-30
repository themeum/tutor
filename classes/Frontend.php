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
		add_action( 'init', array( $this, 'wpml_switch_dashboard' ), 10, 3 );
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

	/**
	 * wpml_switch_dashboard
	 *
	 * @return void
	 */
	function wpml_switch_dashboard() {
		$tutor_option = get_option( 'tutor_option' );
		$previous_dashboard = get_tutor_option('tutor_dashboard_page_id');

		$changed_dashboard_id = apply_filters( 'wpml_object_id', get_tutor_option('tutor_dashboard_page_id'), 'page' );


// pr(get_queried_object_id());

vd(get_page_template_slug());
pr($changed_dashboard_id);
		vd(tutor_utils()->is_tutor_dashboard());
		// pr(!tutor_utils()->is_tutor_frontend_dashboard($changed_dashboard_id));
		// die('not a good page ');

		/* $changed_dashboard_id = apply_filters( 'wpml_object_id', get_tutor_option('tutor_dashboard_page_id'), 'page' );

		$tutor_option['tutor_dashboard_page_id'] = $changed_dashboard_id;
		update_option('tutor_option',$tutor_option); */
	}
}
