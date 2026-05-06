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
	private static function resolve_asset( string $path ): ?array {
		if ( '' === $path ) {
			return null;
		}

		$sources = array(
			array( 'path' => tutor()->path . 'assets/', 'url' => tutor()->assets_url ),
		);

		if ( function_exists( 'tutor_pro' ) ) {
			$sources[] = array( 'path' => tutor_pro()->path . 'assets/', 'url' => tutor_pro()->assets );
		}

		$candidates = array( $path );

		if ( tutor_utils()->is_kids_mode() && ! str_contains( $path, '/kids/' ) ) {
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
	 * Default visual token color map.
	 *
	 * Maps CSS variable names to their hex color equivalents.
	 *
	 * @since 3.0.0
	 *
	 * @return array<string, string>
	 */
	private static function get_default_color_map(): array {
		return array(
			'--tutor-visual-gray-1'      => '#fff',
			'--tutor-visual-gray-2'      => '#ececed',
			'--tutor-visual-gray-3'      => '#CECFD2',
			'--tutor-visual-gray-4'      => '#2d3039',
			'--tutor-visual-brand-1'     => '#4979e8',
			'--tutor-visual-brand-2'     => '#a4bcf4',
			'--tutor-visual-brand-3'     => '#dbe4fa',
			'--tutor-visual-success-1'   => '#28a745',
			'--tutor-visual-success-2'   => '#52c41a',
			'--tutor-visual-critical-1'  => '#f04438',
			'--tutor-visual-critical-2'  => '#fee4e2',
			'--tutor-visual-caution-1'   => '#fde272',
			'--tutor-visual-caution-2'   => '#a15c07',
			'--tutor-visual-caution-3'   => '#542c0d',
			'--tutor-visual-orange-1'    => '#ff8904',
			'--tutor-visual-exception-1' => '#cbfd78',
			'--tutor-visual-exception-2' => '#f4f433',
			'--tutor-visual-exception-3' => '#ede9fe',
		);
	}

	/**
	 * Allowed HTML tags and attributes for SVG output.
	 *
	 * @since 4.0.0
	 *
	 * @return array<string, array<string, bool>>
	 */
	private static function get_svg_allowed_html(): array {
		return array(
			'svg'  => array(
				'xmlns'   => true,
				'width'   => true,
				'height'  => true,
				'fill'    => true,
				'viewBox' => true,
			),
			'path' => array(
				'd'         => true,
				'fill'      => true,
				'mask'      => true,
				'fill-rule' => true,
				'clip-rule' => true,
			),
			'mask' => array(
				'id'        => true,
				'width'     => true,
				'height'    => true,
				'x'         => true,
				'y'         => true,
				'fill'      => true,
				'maskunits' => true,
				'mask-type' => true,
			),
			'rect' => array(
				'x'         => true,
				'y'         => true,
				'width'     => true,
				'height'    => true,
				'fill'      => true,
				'maskunits' => true,
			),
		);
	}

	/**
	 * Get SVG content with colors replaced by CSS variables for dynamic theming.
	 *
	 * @since 4.0.0
	 *
	 * @param string                    $path      Relative asset path to the SVG file.
	 * @param array<string,string>|null $color_map CSS variable → hex color map. Null = default, [] = skip.
	 * @param bool                      $output    Whether to echo the output.
	 * @return string Processed SVG markup.
	 */
	public static function themed_svg( string $path, ?array $color_map = null, bool $output = false ): string {
		$resolved = self::resolve_asset( ltrim( $path, '/' ) );
		if ( ! $resolved ) {
			return '';
		}

		$svg_content = file_get_contents( $resolved['path'] );
		if ( false === $svg_content ) {
			return '';
		}

		$color_map ??= self::get_default_color_map();
		foreach ( $color_map as $css_var => $hex_color ) {
			$svg_content = preg_replace(
				'/' . preg_quote( $hex_color, '/' ) . '/i',
				"var({$css_var}, {$hex_color})",
				$svg_content
			);
		}

		$escaped = wp_kses( $svg_content, self::get_svg_allowed_html() );

		if ( $output ) {
			echo $escaped; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $escaped;
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
