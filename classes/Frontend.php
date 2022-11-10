<?php
/**
 * Manage Frontend
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.5.2
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend class
 *
 * @since 1.5.2
 */
class Frontend {

	/**
	 * Constructor
	 *
	 * @since 1.5.2
	 * @return void
	 */
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'remove_admin_bar' ) );
		add_filter( 'nav_menu_link_attributes', array( $this, 'add_menu_atts' ), 10, 3 );
		add_action( 'admin_init', array( $this, 'restrict_wp_admin_area' ) );

		// Handle flash toast message for redirect_to util helper.
		add_action( 'wp_head', array( new Utils(), 'handle_flash_message' ), 999 );
	}

	/**
	 * Check current user has admin area access for tutor
	 *
	 * @since 2.0.7
	 * @return boolean
	 */
	private function has_admin_area_access() {
		$has_access = true;
		$user       = new \WP_User( get_current_user_id() );
		$roles      = $user->roles;

		if ( ! in_array( 'administrator', $roles ) && ( in_array( 'subscriber', $roles ) || in_array( tutor()->instructor_role, $roles ) ) ) {
			$has_access = false;
		}

		return $has_access;
	}

	/**
	 * PRO - Remove admin bar based on option
	 *
	 * @since 1.5.2
	 * @return void
	 */
	public function remove_admin_bar() {
		$hide_admin_bar_for_users = (bool) get_tutor_option( 'hide_admin_bar_for_users' );
		$has_access               = $this->has_admin_area_access();

		if ( tutor()->has_pro && ! $has_access && $hide_admin_bar_for_users ) {
			show_admin_bar( false );
		}
	}

	/**
	 * PRO - Restrict the WP admin area for student, instructor
	 *
	 * @since 1.5.2
	 * @return void
	 */
	public function restrict_wp_admin_area() {
		$hide_admin_bar_for_users = (bool) get_tutor_option( 'hide_admin_bar_for_users' );
		$has_access               = $this->has_admin_area_access();

		if ( tutor()->has_pro && $hide_admin_bar_for_users && ! $has_access && ! wp_doing_ajax() ) {
			wp_die( esc_html__( 'Access Denied!', 'tutor' ) );
		}
	}

	/**
	 * Add menu attributes
	 *
	 * @since 1.5.2
	 *
	 * @param  mixed $atts attributes.
	 * @param  mixed $item item.
	 * @param  mixed $args arguments.
	 *
	 * @return array
	 */
	public function add_menu_atts( $atts, $item, $args ) {
		$atts['onClick'] = 'return true';
		return $atts;
	}
}
