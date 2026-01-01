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
	 * @return string
	 */
	public static function ajax() {
		return esc_url( admin_url( 'admin-ajax.php' ) );
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
}

