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
	public static function ajax(): string {
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
	public static function asset( $path = '' ): string {
		return tutor()->assets_url . $path;
	}

	/**
	 * Find the first existing file across asset sources for a given path,
	 * respecting kids-mode variant resolution.
	 *
	 * @since 4.0.0
	 *
	 * @param string $path Relative asset path (no leading slash).
	 * @return array{path: string, url: string}|null
	 */
	public static function resolve_asset( string $path ): ?array {
		if ( '' === $path ) {
			return null;
		}

		$sources = array(
			array(
				'path' => tutor()->path . 'assets/',
				'url'  => tutor()->assets_url,
			),
		);

		if ( function_exists( 'tutor_pro' ) ) {
			$sources[] = array(
				'path' => tutor_pro()->path . 'assets/',
				'url'  => tutor_pro()->assets,
			);
		}

		$candidates = array( $path );

		if ( tutor_utils()->is_kids_mode() && false === strpos( $path, '/kids/' ) ) {
			$directory = pathinfo( $path, PATHINFO_DIRNAME );
			$basename  = pathinfo( $path, PATHINFO_BASENAME );

			if ( '' !== $basename ) {
				$kids_path = ( '.' === $directory || '' === $directory )
					? 'kids/' . $basename
					: trailingslashit( $directory ) . 'kids/' . $basename;

				array_unshift( $candidates, $kids_path );
			}
		}

		foreach ( $candidates as $candidate ) {
			foreach ( $sources as $source ) {
				if ( file_exists( $source['path'] . $candidate ) ) {
					return array(
						'path' => $source['path'] . $candidate,
						'url'  => $source['url'] . $candidate,
					);
				}
			}
		}

		return null;
	}

	/**
	 * Get a theme-aware plugin asset URL.
	 *
	 * @since 4.0.0
	 *
	 * @param string $path Relative asset path.
	 * @return string
	 */
	public static function themed_asset( string $path = '' ): string {
		static $cache = array();

		$path = ltrim( $path, '/' );

		if ( '' === $path ) {
			return self::asset( $path );
		}

		if ( isset( $cache[ $path ] ) ) {
			return $cache[ $path ];
		}

		$resolved       = self::resolve_asset( $path );
		$cache[ $path ] = $resolved ? $resolved['url'] : self::asset( $path );

		return $cache[ $path ];
	}

	/**
	 * Get current URL.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public static function current(): string {
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
	public static function add_query_params( $url, array $query_params = array() ): string {
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
	public static function remove_query_params( $url, array $query_params = array() ): string {
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
	public static function back( $fallback = '' ): string {
		$back_url = wp_get_referer();
		if ( empty( $back_url ) ) {
			$back_url = empty( $fallback ) ? self::current() : $fallback;
		}
		return $back_url;
	}
}
