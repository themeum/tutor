<?php

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Input;

class Tools {

	public function __construct() {
		add_action( 'tutor_action_regenerate_tutor_pages', array( $this, 'regenerate_tutor_pages' ) );

		add_action( 'tutor_option_save_after', array( $this, 'tutor_option_save_after' ) );
		add_action( 'init', array( $this, 'check_if_maintenance' ) );

		add_action( 'admin_init', array( $this, 'redirect_to_wizard_page' ) );
	}

	/**
	 * Re-Generate Tutor Missing Pages
	 *
	 * @since v.1.4.3
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

	public function tutor_option_save_after() {
		$maintenance_mode = (bool) get_tutor_option( 'enable_tutor_maintenance_mode' );
		if ( $maintenance_mode ) {
			tutor_maintenance_mode( true );
		} else {
			tutor_maintenance_mode();
		}
	}

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

	public function is_wplogin() {
		$ABSPATH_MY = str_replace( array( '\\', '/' ), DIRECTORY_SEPARATOR, ABSPATH );
		return ( ( in_array( $ABSPATH_MY . 'wp-login.php', get_included_files() ) || in_array( $ABSPATH_MY . 'wp-register.php', get_included_files() ) ) || ( isset( $_GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] === 'wp-login.php' ) || $_SERVER['PHP_SELF'] == '/wp-login.php' );
	}

	/**
	 * Redirect to setup wizard page if any one click on the menu from tools page
	 *
	 * @since v.1.5.7
	 */
	public function redirect_to_wizard_page() {
		if ( Input::get( 'page' ) === 'tutor-tools' && Input::get( 'sub_page' ) === 'tutor-setup' ) {
			exit( wp_redirect( admin_url( 'admin.php?page=tutor-setup' ) ) );
		}
	}

}
