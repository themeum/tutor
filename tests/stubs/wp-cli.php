<?php
/**
 * WP-CLI stubs for static analysis.
 *
 * @package Tutor
 */

if ( ! class_exists( 'WP_CLI' ) ) {
	/**
	 * Stub for static analysis when WP-CLI is not present in autoload.
	 */
	class WP_CLI {
		/**
		 * Display an error message and exit.
		 *
		 * @param string $message Error message.
		 * @return void
		 */
		public static function error( $message ) {}

		/**
		 * Display a success message.
		 *
		 * @param string $message Success message.
		 * @return void
		 */
		public static function success( $message ) {}

		/**
		 * Display a line of message.
		 *
		 * @param string $message Line message.
		 * @return void
		 */
		public static function line( $message ) {}
	}
}
