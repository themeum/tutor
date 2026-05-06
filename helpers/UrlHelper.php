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
	 * Get a theme-aware plugin asset URL.
	 *
	 * In kids mode, this looks for a matching kids variant before falling back
	 * to the default asset path.
	 *
	 * @since 4.0.0
	 *
	 * @param string $path Relative asset path.
	 *
	 * @return string
	 */
	public static function themed_asset( $path = '' ): string {
		static $resolved_paths = array();

		$path = ltrim( (string) $path, '/' );

		if ( '' === $path ) {
			return self::asset( $path );
		}

		if ( isset( $resolved_paths[ $path ] ) ) {
			return $resolved_paths[ $path ];
		}

		$asset_sources = array(
			array(
				'path' => tutor()->path . 'assets/',
				'url'  => tutor()->assets_url,
			),
		);

		if ( function_exists( 'tutor_pro' ) ) {
			$asset_sources[] = array(
				'path' => tutor_pro()->path . 'assets/',
				'url'  => tutor_pro()->assets,
			);
		}

		if ( ! tutor_utils()->is_kids_mode() || false !== strpos( $path, '/kids/' ) ) {
			foreach ( $asset_sources as $asset_source ) {
				if ( file_exists( $asset_source['path'] . $path ) ) {
					$resolved_paths[ $path ] = $asset_source['url'] . $path;
					return $resolved_paths[ $path ];
				}
			}

			$resolved_paths[ $path ] = self::asset( $path );
			return $resolved_paths[ $path ];
		}

		$directory = pathinfo( $path, PATHINFO_DIRNAME );
		$basename  = pathinfo( $path, PATHINFO_BASENAME );
		if ( '' !== $basename ) {
			$candidate_path = '.' === $directory || '' === $directory
				? 'kids/' . $basename
				: trailingslashit( $directory ) . 'kids/' . $basename;

			foreach ( $asset_sources as $asset_source ) {
				if ( file_exists( $asset_source['path'] . $candidate_path ) ) {
					$resolved_paths[ $path ] = $asset_source['url'] . $candidate_path;
					return $resolved_paths[ $path ];
				}
			}
		}

		foreach ( $asset_sources as $asset_source ) {
			if ( file_exists( $asset_source['path'] . $path ) ) {
				$resolved_paths[ $path ] = $asset_source['url'] . $path;
				return $resolved_paths[ $path ];
			}
		}

		$resolved_paths[ $path ] = self::asset( $path );
		return $resolved_paths[ $path ];
	}

	/**
	 * Resolve the file path for a themed asset.
	 *
	 * @since 4.0.0
	 *
	 * @param string $path Relative asset path.
	 *
	 * @return string|false Resolved file path or false if not found.
	 */
	private static function resolve_themed_path( string $path ) {
		$path = ltrim( (string) $path, '/' );

		if ( '' === $path ) {
			return false;
		}

		$asset_sources = array(
			array(
				'path' => tutor()->path . 'assets/',
			),
		);

		if ( function_exists( 'tutor_pro' ) ) {
			$asset_sources[] = array(
				'path' => tutor_pro()->path . 'assets/',
			);
		}

		if ( ! tutor_utils()->is_kids_mode() || false !== strpos( $path, '/kids/' ) ) {
			foreach ( $asset_sources as $asset_source ) {
				if ( file_exists( $asset_source['path'] . $path ) ) {
					return $asset_source['path'] . $path;
				}
			}
			return false;
		}

		$directory = pathinfo( $path, PATHINFO_DIRNAME );
		$basename  = pathinfo( $path, PATHINFO_BASENAME );
		if ( '' !== $basename ) {
			$candidate_path = '.' === $directory || '' === $directory
				? 'kids/' . $basename
				: trailingslashit( $directory ) . 'kids/' . $basename;

			foreach ( $asset_sources as $asset_source ) {
				if ( file_exists( $asset_source['path'] . $candidate_path ) ) {
					return $asset_source['path'] . $candidate_path;
				}
			}
		}

		foreach ( $asset_sources as $asset_source ) {
			if ( file_exists( $asset_source['path'] . $path ) ) {
				return $asset_source['path'] . $path;
			}
		}

		return false;
	}

	/**
	 * Default visual token color map.
	 *
	 * Maps CSS variable names to their hex color equivalents.
	 *
	 * @since 3.0.0
	 *
	 * @return array
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
	 * @return array
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
	 * @param string     $path      Relative asset path to the SVG file.
	 * @param array|null $color_map Array mapping CSS variable names to hex colors.
	 *                              Pass null to use the default map, empty array to skip replacement.
	 * @param bool       $output    Whether to echo the output.
	 *
	 * @return string Processed SVG markup.
	 */
	public static function themed_svg( string $path, ?array $color_map = null, bool $output = false ): string {
		$color_map ??= self::get_default_color_map();

		$svg_path = self::resolve_themed_path( $path );

		if ( ! $svg_path ) {
			return '';
		}

		$svg_content = file_get_contents( $svg_path );
		if ( false === $svg_content ) {
			return '';
		}

		foreach ( $color_map as $css_var => $hex_color ) {
			$pattern     = '/' . preg_quote( $hex_color, '/' ) . '/i';
			$replacement = "var({$css_var}, {$hex_color})";
			$svg_content = preg_replace( $pattern, $replacement, $svg_content );
		}

		$escaped = wp_kses( $svg_content, self::get_svg_allowed_html() );
		// $escaped = $svg_content;

		if ( $output ) {
			echo $escaped; // phpcs:ignore
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
