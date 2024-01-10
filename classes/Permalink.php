<?php
/**
 * Manage permalink update
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.6.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permalink Class
 *
 * @since 2.6.0
 */
class Permalink {

	const TRANS_KEY = 'tutor_permalink_update_required';

	/**
	 * Register hooks
	 *
	 * @since 2.6.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'update_permalink' ) );

		add_action( 'tutor_setup_finished', array( $this, 'set_permalink_flag' ) );

		add_action( 'tutor_option_course_permalink_base_changed', array( $this, 'listen_tutor_setting_changes' ), 10, 2 );
		add_action( 'tutor_option_lesson_permalink_base_changed', array( $this, 'listen_tutor_setting_changes' ), 10, 2 );
		add_action( 'tutor_option_quiz_permalink_base_changed', array( $this, 'listen_tutor_setting_changes' ), 10, 2 );
		add_action( 'tutor_option_assignment_permalink_base_changed', array( $this, 'listen_tutor_setting_changes' ), 10, 2 );
	}

	/**
	 * Check permalink update is required or not.
	 *
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public static function update_required() {
		return (bool) get_transient( self::TRANS_KEY );
	}

	/**
	 * Set permalink update required flag
	 *
	 * @since 2.6.0
	 *
	 * @return void
	 */
	public static function set_permalink_flag() {
		set_transient( self::TRANS_KEY, true, HOUR_IN_SECONDS );
	}

	/**
	 * Listen tutor settings changes to update permalink.
	 *
	 * @since 2.6.0
	 *
	 * @param mixed $from from.
	 * @param mixed $to to.
	 *
	 * @return void
	 */
	public function listen_tutor_setting_changes( $from, $to ) {
		if ( $from !== $to ) {
			self::set_permalink_flag();
		}
	}

	/**
	 * Update permalink when required.
	 *
	 * @since 2.6.0
	 *
	 * @return void
	 */
	public function update_permalink() {
		if ( ! self::update_required() ) {
			return;
		}

		update_option( 'permalink_structure', '/%postname%/' );
		flush_rewrite_rules();

		delete_transient( self::TRANS_KEY );
	}

}
