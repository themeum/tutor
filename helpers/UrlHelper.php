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
		return esc_url( admin_url( 'admin-ajax.php' ) );
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
		return esc_url( tutor()->assets_url . $path );
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

		return esc_url(
			home_url(
				add_query_arg( array(), $wp->request )
			)
		);
	}

	/**
	 * Add query args to URL.
	 *
	 * @since 4.0.0
	 *
	 * @param string $url URL.
	 * @param array  $query_args Query args.
	 *
	 * @return string
	 */
	public static function add_query_var( $url, array $query_args = array() ) : string {
		$url = ltrim( $url, '/' );

		if ( ! empty( $query_args ) ) {
			$url = add_query_arg( $query_args, $url );
		}

		return esc_url( $url );
	}

	/**
	 * Remove query args from URL.
	 *
	 * @since 4.0.0
	 *
	 * @param string $url URL.
	 * @param array  $query_args Query args.
	 *
	 * @return string
	 */
	public static function remove_query_var( $url, array $query_args = array() ) : string {
		return self::add_query_var( $url, array_diff_key( $query_args, $query_args ) );
	}
}

