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
	 * Get AJAX URL.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public static function ajax() : string {
		return admin_url( 'admin-ajax.php' );
	}

	/**
	 * Get plugin asset URL.
	 *
	 * @since 4.0.0
	 *
	 * @param string $path Relative asset path.
	 * @return string
	 */
	public static function asset( $path = '' ) : string {
		return tutor()->assets_url . $path;
	}

	/**
	 * Get current URL.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public static function current() : string {
		global $wp;

		return home_url(
			add_query_arg( array(), $wp->request )
		);
	}

	/**
	 * Add query params to URL.
	 *
	 * @since 4.0.0
	 *
	 * @param string $url URL.
	 * @param array  $query_params Query params.
	 *
	 * @return string
	 */
	public static function add_query_params( $url, array $query_params = array() ) : string {
		$url = ltrim( $url, '/' );

		if ( ! empty( $query_params ) ) {
			$url = add_query_arg( $query_params, $url );
		}

		return $url;
	}

	/**
	 * Remove query params from URL.
	 *
	 * @since 4.0.0
	 *
	 * @param string $url URL.
	 * @param array  $query_params Query params.
	 *
	 * @return string
	 */
	public static function remove_query_params( $url, array $query_params = array() ) : string {
		return remove_query_arg( $query_params, $url );
	}

	/**
	 * Get back URL.
	 *
	 * @since 4.0.0
	 *
	 * @param string $fallback fallback URL.
	 *
	 * @return string
	 */
	public static function back( $fallback = '' ) : string {
		$back_url = wp_get_referer();
		if ( empty( $back_url ) ) {
			$back_url = empty( $fallback ) ? self::current() : $fallback;
		}
		return $back_url;
	}
}

