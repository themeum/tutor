<?php
/**
 * Manage course embed
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Course embed class
 *
 * @since 2.1.0
 */
class Course_Embed {

	/**
	 * Register hooks
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function __construct() {
		add_filter( 'tutor_get_template_path', __CLASS__ . '::filter_template_path', 100, 2 );
		add_filter( 'embed_oembed_html', __CLASS__ . '::oembed_iframe_overrides', 10, 3 );
	}

	/**
	 * Filter oembed data
	 *
	 * @since 2.1.0
	 *
	 * @param string $html  html content to filter.
	 * @param string $url  post embed url.
	 * @param array  $attr attrs.
	 *
	 * @return string customized html content
	 */
	public static function oembed_iframe_overrides( $html, $url, $attr ) {

		$post_id = url_to_postid( $url );
		if ( ! $post_id || tutor()->course_post_type !== get_post_type( $post_id ) ) {
			return $html;
		}

		if ( strpos( $html, '<iframe' ) !== false ) {
			$html = str_replace(
				'<iframe class="wp-embedded-content" sandbox="allow-scripts" security="restricted" style="position: absolute; clip: rect(1px, 1px, 1px, 1px);"',
				'<iframe class="wp-embedded-content" sandbox="allow-forms allow-popups allow-scripts allow-same-origin allow-top-navigation" security="restricted" ',
				$html
			);

			$html = preg_replace( '/( height=".*")/i', ' height="620" marginwidth="0" marginheight="0" frameborder="0" scrolling="no" ', $html );

			$html = str_replace(
				'<blockquote class="wp-embedded-content"',
				'<blockquote class="wp-embedded-content" style="display:none" ',
				$html
			);
			return $html;
		} else {
			return $html;
		}
	}

	/**
	 * Filter template
	 *
	 * @since 2.1.0
	 *
	 * @param string $template_location default template location.
	 * @param string $template template name.
	 *
	 * @return string
	 */
	public static function filter_template_path( $template_location, $template ) {
		$post_id = get_the_ID();
		if ( get_post_type( $post_id ) === tutor()->course_post_type && function_exists( 'is_embed' ) && is_embed() ) {
			if ( 'single-course' === $template ) {
				$template_location = tutor()->path . 'templates/course-embed.php';
			}
		}
		return $template_location;
	}

	/**
	 * Check if current course is embedded
	 *
	 * @since 2.1.0
	 * @return boolean
	 */
	public static function is_embed_course() {
		$post_id = get_the_ID();
		if ( get_post_type( $post_id ) === tutor()->course_post_type && function_exists( 'is_embed' ) && is_embed() ) {
			return true;
		}
		return false;
	}
}
