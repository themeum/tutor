<?php
/**
 * Session Management Helper Class.
 *
 * @package Tutor\Helper
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

namespace Tutor\Helpers;

/**
 * SessionHelper class
 *
 * @since 2.1.9
 */
class SessionHelper {
	/**
	 * Start the session if no session ID exist.
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	private function start_session() {
		if ( ! session_id() ) {
			session_start();
		}
	}

	/**
	 * Get session data by a key.
	 *
	 * @since 2.1.9
	 *
	 * @param string $key session key.
	 * @param mixed  $default default data if key not exist in session.
	 *
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		( new self() )->start_session();

		if ( isset( $_SESSION[ $key ] ) ) {
			return maybe_unserialize( $_SESSION[ $key ] );
		} else {
			return $default;
		}
	}

	/**
	 * Set data in specific session key.
	 *
	 * @since 2.1.9
	 *
	 * @param string $key session key.
	 * @param mixed  $value value.
	 *
	 * @return void
	 */
	public static function set( $key, $value ) {
		( new self() )->start_session();

		$_SESSION[ $key ] = maybe_serialize( $value );
	}

	/**
	 * Unset a specific session key.
	 *
	 * @since 2.1.9
	 *
	 * @param string $key session key.
	 *
	 * @return bool
	 */
	public static function unset( $key ) {
		( new self() )->start_session();

		if ( isset( $_SESSION[ $key ] ) ) {
			unset( $_SESSION[ $key ] );
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Reset all session keys without reset the session ID.
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public static function reset() {
		( new self() )->start_session();
		session_unset();
	}

	/**
	 * Destroy session data.
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public static function destroy() {
		( new self() )->start_session();
		session_destroy();
	}
}
