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
		$previous_dashboard = get_tutor_option('tutor_dashboard_page_id');
		$changed_dashboard_id = apply_filters( 'wpml_object_id', $previous_dashboard, 'page' );
		// echo $previous_dashboard.' - '.$changed_dashboard_id;
		if(isset($changed_dashboard_id) && $previous_dashboard !== $changed_dashboard_id){
			$tutor_option = get_option( 'tutor_option' );
			$tutor_option['tutor_dashboard_page_id'] = $changed_dashboard_id;
			update_option('tutor_option',$tutor_option);
			$this->rewrite_flush();
		}
	}

	public function rewrite_flush() {
        global $wp_rewrite;
        $wp_rewrite->set_permalink_structure('/%postname%/');
        update_option( "rewrite_rules", false );
        $wp_rewrite->flush_rules( true );
        flush_rewrite_rules();
    }
}
