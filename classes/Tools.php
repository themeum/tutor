<?php
/**
 * Options for TutorLMS
 *
 * @package Tutor\Tools
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Input;

/**
 * Tools class
 *
 * @since 1.0.0
 */
class Tools {

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'tutor_action_regenerate_tutor_pages', array( $this, 'regenerate_tutor_pages' ) );

		add_action( 'tutor_option_save_after', array( $this, 'tutor_option_save_after' ) );
		add_action( 'init', array( $this, 'check_if_maintenance' ) );

		add_action( 'admin_init', array( $this, 'redirect_to_wizard_page' ) );
	}

	/**
	 * Re-Generate Tutor Missing Pages
	 *
	 * @since 1.4.3
	 */
	public function regenerate_tutor_pages() {
		tutor_utils()->checking_nonce();

		$tutor_pages = tutor_utils()->tutor_pages();

		foreach ( $tutor_pages as $page ) {
			$visible    = tutor_utils()->array_get( 'page_visible', $page );
			$page_title = tutor_utils()->array_get( 'page_name', $page );
			$option_key = tutor_utils()->array_get( 'option_key', $page );
			$page_id    = tutor_utils()->array_get( 'page_id', $page );

			$page_content = '';
			if ( $option_key === 'tutor_login_page' ) {
				$page_content = '[tutor_login]';
			}

			if ( ! $visible ) {
				$page_arg = array(
					'ID'           => $page_id,
					'post_title'   => $page_title,
					'post_content' => $page_content,
					'post_type'    => 'page',
					'post_status'  => 'publish',
				);
				$page_id  = wp_insert_post( $page_arg );
				update_tutor_option( $option_key, $page_id );
			}
		}
	}

	/**
	 * Handler after tutor option save
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_option_save_after() {
		$maintenance_mode = (bool) get_tutor_option( 'enable_tutor_maintenance_mode' );
		if ( $maintenance_mode ) {
			tutor_maintenance_mode( true );
		} else {
			tutor_maintenance_mode();
		}
	}

	/**
	 * Check if maintenance mode
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function check_if_maintenance() {
		if ( ! is_admin() && ! $this->is_wplogin() ) {
			$maintenance_mode = (bool) get_tutor_option( 'enable_tutor_maintenance_mode' );
			if ( false === $maintenance_mode || current_user_can( 'administrator' ) ) {
				return;
			}
			header( 'Retry-After: 600' );
			include tutor()->path . 'views/maintenance.php';
			die();
		}
	}

	/**
	 * Check if wp_login
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function is_wplogin() {
		$abs_path = str_replace( array( '\\', '/' ), DIRECTORY_SEPARATOR, ABSPATH );
		return (
			( in_array( $abs_path . 'wp-login.php', get_included_files() ) || in_array( $abs_path . 'wp-register.php', get_included_files() ) )
			||
			( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] )
			||
			( isset( $_SERVER['PHP_SELF'] ) && '/wp-login.php' === $_SERVER['PHP_SELF'] )
		);
	}

	/**
	 * Redirect to setup wizard page if any one click on the menu from tools page
	 *
	 * @since 1.5.7
	 *
	 * @return void
	 */
	public function redirect_to_wizard_page() {
		if ( Input::get( 'page' ) === 'tutor-tools' && Input::get( 'sub_page' ) === 'tutor-setup' ) {
			wp_safe_redirect( admin_url( 'admin.php?page=tutor-setup' ) );
			exit();
		}
	}

}
