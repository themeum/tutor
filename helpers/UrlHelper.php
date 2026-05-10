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
	 * Allowed HTML tags and attributes for SVG output.
	 *
	 * @since 4.0.0
	 *
	 * @return array<string, array<string, bool>>
	 */
	private static function get_svg_allowed_html(): array {
		$attr_fill      = array( 'fill' => true );
		$attr_id        = array( 'id' => true );
		$attr_dims      = array(
			'width'  => true,
			'height' => true,
			'x'      => true,
			'y'      => true,
		);
		$attr_transform = array( 'transform' => true );
		$attr_clip      = array(
			'clip-path' => true,
			'clip-rule' => true,
		);
		$attr_center    = array(
			'cx' => true,
			'cy' => true,
		);

		$attr_stroke = array(
			'stroke'           => true,
			'stroke-width'     => true,
			'stroke-linecap'   => true,
			'stroke-linejoin'  => true,
			'stroke-dasharray' => true,
		);

		return array(
			'svg'      => array_merge(
				$attr_fill,
				$attr_dims,
				array(
					'xmlns'   => true,
					'viewBox' => true,
				)
			),
			'g'        => $attr_clip,
			'defs'     => $attr_id,
			'clipPath' => $attr_id,
			'path'     => array_merge(
				$attr_fill,
				$attr_stroke,
				$attr_clip,
				array(
					'd'         => true,
					'mask'      => true,
					'fill-rule' => true,
				)
			),
			'mask'     => array_merge(
				$attr_fill,
				$attr_dims,
				$attr_id,
				array(
					'maskunits' => true,
					'mask-type' => true,
				)
			),
			'rect'     => array_merge(
				$attr_fill,
				$attr_dims,
				$attr_stroke,
				$attr_transform,
				array(
					'maskunits' => true,
					'rx'        => true,
				)
			),
			'circle'   => array_merge(
				$attr_fill,
				$attr_center,
				$attr_transform,
				array(
					'r' => true,
				)
			),
			'ellipse'  => array_merge(
				$attr_fill,
				$attr_center,
				$attr_transform,
				array(
					'rx' => true,
					'ry' => true,
				)
			),
		);
	}

	/**
	 * Get SVG content for inline rendering.
	 *
	 * Colors are pre-tokenised at build time by replace-hex-colors.ts,
	 * so no runtime color-map substitution is needed here.
	 *
	 * @since 4.0.0
	 *
	 * @param string $path    Relative asset path to the SVG file.
	 * @param array  $options {
	 *   output?: bool,
	 *   width?: string|int|null,
	 *   height?: string|int|null
	 * } $options Optional settings.
	 * @return string Processed SVG markup.
	 */
	public static function themed_svg( string $path, array $options = array() ): string {
		$options = array_merge(
			array(
				'output' => false,
				'width'  => null,
				'height' => null,
			),
			$options
		);

		$resolved = self::resolve_asset( ltrim( $path, '/' ) );
		if ( ! $resolved ) {
			return '';
		}

		$svg_content = file_get_contents( $resolved['path'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( false === $svg_content ) {
			return '';
		}

		if ( ! empty( $options['width'] ) || ! empty( $options['height'] ) ) {
			$svg_content = preg_replace_callback(
				'/(<svg[^>]*)\s(?:width|height)="[^"]*"/i',
				static function ( $matches ) use ( $options ) {
					$tag = $matches[1];
					if ( ! empty( $options['width'] ) ) {
						$tag .= ' width="' . esc_attr( (string) $options['width'] ) . '"';
					}
					if ( ! empty( $options['height'] ) ) {
						$tag .= ' height="' . esc_attr( (string) $options['height'] ) . '"';
					}
					return $tag;
				},
				$svg_content
			);
		}

		$escaped = wp_kses( $svg_content, self::get_svg_allowed_html() );

		if ( $options['output'] ) {
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
