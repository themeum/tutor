<?php
/**
 * URL helper.
 *
 * @package Tutor\Helper
 * @since 4.0.0
 */

namespace Tutor\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Class UrlHelper
 *
 * @since 4.0.0
 */
class UrlHelper {

	/**
	 * Get site base URL.
	 *
	 * @param string $path Optional path.
	 *
	 * @return string
	 */
	public static function site( $path = '' ) {
		return esc_url( site_url( $path ) );
	}

	/**
	 * Get home URL.
	 *
	 * @param string $path Optional path.
	 *
	 * @return string
	 */
	public static function home( $path = '' ) {
		return esc_url( home_url( $path ) );
	}

	/**
	 * Get admin URL.
	 *
	 * @param string $path Optional path.
	 * @return string
	 */
	public static function admin( $path = '' ) {
		return esc_url( admin_url( $path ) );
	}

	/**
	 * Get AJAX URL.
	 *
	 * @return string
	 */
	public static function ajax() {
		return esc_url( admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Get REST API base URL.
	 *
	 * @param string $route Optional route.
	 * @return string
	 */
	public static function rest( $route = '' ) {
		return esc_url( rest_url( $route ) );
	}

	/**
	 * Get plugin asset URL.
	 *
	 * @param string $path Relative asset path.
	 * @return string
	 */
	public static function asset( $path = '' ) {
		return esc_url( tutor()->assets_url . $path );
	}

	/**
	 * Get current URL.
	 *
	 * @return string
	 */
	public static function current() {
		global $wp;

		return esc_url(
			home_url(
				add_query_arg( array(), $wp->request )
			)
		);
	}

	/**
	 * Get referer URL safely.
	 *
	 * @return string
	 */
	public static function referer() {
		return esc_url( wp_get_referer() );
	}

	/**
	 * Check if current request is HTTPS.
	 *
	 * @return bool
	 */
	public static function is_https() {
		return is_ssl();
	}

	/**
	 * Prepare URL with query args.
	 *
	 * @param string $url URL.
	 * @param array  $query_args Query args.
	 *
	 * @return string
	 */
	public static function prepare( $url, array $query_args = array() ) {
		$url = ltrim( $url, '/' );

		if ( ! empty( $query_args ) ) {
			$url = add_query_arg( $query_args, $url );
		}

		return esc_url( $url );
	}

	/**
	 * Add query args to URL.
	 *
	 * @param string $url URL.
	 * @param array  $query_args Query args.
	 *
	 * @return string
	 */
	public static function add_query_var( $url, array $query_args = array() ) {
		return self::prepare( $url, $query_args );
	}

	/**
	 * Remove query args from URL.
	 *
	 * @param string $url URL.
	 * @param array  $query_args Query args.
	 *
	 * @return string
	 */
	public static function remove_query_var( $url, array $query_args = array() ) {
		return self::prepare( $url, array_diff_key( $query_args, $query_args ) );
	}

	/**
	 * Get login URL with redirect.
	 *
	 * @param string $redirect Redirect URL.
	 * @return string
	 */
	public static function login( $redirect = '' ) {
		return esc_url( wp_login_url( $redirect ) );
	}

	/**
	 * Get logout URL.
	 *
	 * @param string $redirect Redirect URL.
	 * @return string
	 */
	public static function logout( $redirect = '' ) {
		return esc_url( wp_logout_url( $redirect ) );
	}
}

