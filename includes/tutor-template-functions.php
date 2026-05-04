<?php
/**
 * Tutor template functions
 *
 * @package TutorFunctions
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Models\CourseModel;
use Tutor\Models\EnrollmentModel;

if ( ! function_exists( 'tutor_get_template' ) ) {
	/**
	 * Load template with override file system.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template template name.
	 * @param bool   $tutor_pro whether to load from tutor pro.
	 *
	 * @return bool|string
	 */
	function tutor_get_template( $template = null, $tutor_pro = false ) {
		if ( ! $template ) {
			return false;
		}
		$template = str_replace( '.', DIRECTORY_SEPARATOR, $template );

		/**
		 * Get template first from child-theme if exists
		 * If child theme not exists, then get template from parent theme
		 */
		$template_location = trailingslashit( get_stylesheet_directory() ) . "tutor/{$template}.php";
		if ( ! file_exists( $template_location ) ) {
			$template_location = trailingslashit( get_template_directory() ) . "tutor/{$template}.php";
		}
		$file_in_theme = $template_location;
		if ( ! file_exists( $template_location ) ) {
			$template_location = trailingslashit( tutor()->path ) . "templates/{$template}.php";

			if ( $tutor_pro && function_exists( 'tutor_pro' ) ) {
				$pro_template_location = trailingslashit( tutor_pro()->path ) . "templates/{$template}.php";
				if ( file_exists( $pro_template_location ) ) {
					$template_location = trailingslashit( tutor_pro()->path ) . "templates/{$template}.php";
				}
			}

			if ( ! file_exists( $template_location ) ) {
				$warning_msg = __( 'The file you are trying to load does not exist in your theme or Tutor LMS plugin location. If you are extending the Tutor LMS plugin, please create a php file here: ', 'tutor' );
				$warning_msg = $warning_msg . "<code>$file_in_theme</code>";
				$warning_msg = apply_filters( 'tutor_not_found_template_warning_msg', $warning_msg );
				echo wp_kses( $warning_msg, array( 'code' => true ) );
				?>
				<?php
			}
		}

		return apply_filters( 'tutor_get_template_path', $template_location, $template );
	}
}

if ( ! function_exists( 'tutor_get_template_path' ) ) {
	/**
	 * Get only template path without any warning.
	 *
	 * @since 1.4.2
	 *
	 * @param string $template template name.
	 * @param bool   $tutor_pro whether to load from tutor pro.
	 *
	 * @return bool|string
	 */
	function tutor_get_template_path( $template = null, $tutor_pro = false ) {
		if ( ! $template ) {
			return false;
		}
		$template = str_replace( '.', DIRECTORY_SEPARATOR, $template );

		/**
		 * Get template first from child-theme if exists
		 * If child theme not exists, then get template from parent theme
		 */
		$template_location = trailingslashit( get_stylesheet_directory() ) . "tutor/{$template}.php";
		if ( ! file_exists( $template_location ) ) {
			$template_location = trailingslashit( get_template_directory() ) . "tutor/{$template}.php";
		}
		if ( ! file_exists( $template_location ) ) {
			$template_location = trailingslashit( tutor()->path ) . "templates/{$template}.php";
		}
		if ( ! file_exists( $template_location ) && $tutor_pro && function_exists( 'tutor_pro' ) ) {
			$template_location = trailingslashit( tutor_pro()->path ) . "templates/{$template}.php";
		}

		return apply_filters( 'tutor_get_template_path', $template_location, $template );
	}
}

if ( ! function_exists( 'tutor_load_template' ) ) {
	/**
	 * Load template for TUTOR.
	 *
	 * @since 1.0.0
	 * @since 1.1.2 updated.
	 *
	 * @param string $template template name.
	 * @param array  $variables variables.
	 * @param bool   $tutor_pro whether to load from tutor pro.
	 *
	 * @return void
	 */
	function tutor_load_template( $template = null, $variables = array(), $tutor_pro = false ) {
		$variables = (array) $variables;
		$variables = apply_filters( 'get_tutor_load_template_variables', $variables );
		extract( $variables ); //phpcs:ignore

		$is_load = apply_filters( 'should_tutor_load_template', true, $template, $variables );
		if ( ! $is_load ) {
			return;
		}

		do_action( 'tutor_load_template_before', $template, $variables );
		$template_file = tutor_get_template( $template, $tutor_pro );
		if ( file_exists( $template_file ) ) {
			include tutor_get_template( $template, $tutor_pro );
		} else {
			do_action( 'tutor_after_template_not_found', $template );
		}
		do_action( 'tutor_load_template_after', $template, $variables );
	}
}

if ( ! function_exists( 'tutor_load_template_part' ) ) {
	/**
	 * Load tutor template part.
	 *
	 * @since 1.4.3
	 *
	 * @param string $template template name.
	 * @param array  $variables variables.
	 * @param bool   $tutor_pro whether to load from tutor pro.
	 *
	 * @return void
	 */
	function tutor_load_template_part( $template = null, $variables = array(), $tutor_pro = false ) {
		$variables = (array) $variables;
		$variables = apply_filters( 'get_tutor_load_template_variables', $variables );
		extract( $variables ); //phpcs:ignore

		/**
		 * Get template first from child-theme if exists
		 * If child theme not exists, then get template from parent theme
		 */
		$template_location = trailingslashit( get_stylesheet_directory() ) . 'tutor/template.php';
		if ( ! file_exists( $template_location ) ) {
			$template_location = trailingslashit( get_template_directory() ) . 'tutor/template.php';
		}

		if ( ! file_exists( $template_location ) ) {
			$template_location = trailingslashit( tutor()->path ) . 'templates/template.php';
			if ( ! file_exists( $template_location ) && $tutor_pro && function_exists( 'tutor_pro' ) ) {
				$template_location = trailingslashit( tutor_pro()->path ) . 'templates/template.php';
			}
		}

		include apply_filters( 'tutor_get_template_part_path', $template_location, $template );
	}
}

if ( ! function_exists( 'tutor_get_template_html' ) ) {
	/**
	 * Tutor get template HTML.
	 *
	 * @since 1.4.3
	 *
	 * @param string $template_name template name.
	 * @param array  $variables variables.
	 * @param bool   $tutor_pro is tutor pro.
	 *
	 * @return string
	 */
	function tutor_get_template_html( $template_name, $variables = array(), $tutor_pro = false ) {
		ob_start();
		tutor_load_template( $template_name, $variables, $tutor_pro );

		return ob_get_clean();
	}
}

if ( ! function_exists( 'tutor_course_loop_start' ) ) {
	/**
	 * Tutor course loop start.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string
	 */
	function tutor_course_loop_start( $echo = true ) {
		ob_start();
		tutor_load_template( 'loop.loop-start' );
		$output = apply_filters( 'tutor_course_loop_start', ob_get_clean() );

		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore
		}
		return $output;
	}
}

if ( ! function_exists( 'tutor_course_loop_end' ) ) {
	/**
	 * Tutor course loop end.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string
	 */
	function tutor_course_loop_end( $echo = true ) {
		ob_start();
		tutor_load_template( 'loop.loop-end' );

		$output = apply_filters( 'tutor_course_loop_end', ob_get_clean() );
		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
		}

		return $output;
	}
}

if ( ! function_exists( 'tutor_course_loop_before_content' ) ) {
	/**
	 * Tutor course loop before content.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function tutor_course_loop_before_content() {
		ob_start();
		tutor_load_template( 'loop.loop-before-content' );

		$output = apply_filters( 'tutor_course_loop_before_content', ob_get_clean() );
		echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
	}
}

if ( ! function_exists( 'tutor_course_loop_after_content' ) ) {
	/**
	 * Tutor course loop after content.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function tutor_course_loop_after_content() {
		ob_start();
		tutor_load_template( 'loop.loop-after-content' );

		$output = apply_filters( 'tutor_course_loop_after_content', ob_get_clean() );
		echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
	}
}

if ( ! function_exists( 'tutor_course_loop_title' ) ) {
	/**
	 * Tutor course loop title.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function tutor_course_loop_title() {
		ob_start();
		tutor_load_template( 'loop.title' );
		$output = apply_filters( 'tutor_course_loop_title', ob_get_clean() );

		echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
	}
}


if ( ! function_exists( 'tutor_course_loop_header' ) ) {
	/**
	 * Tutor course loop header.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function tutor_course_loop_header() {
		ob_start();
		tutor_load_template( 'loop.header' );
		$output = apply_filters( 'tutor_course_loop_header', ob_get_clean() );

		echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
	}
}

if ( ! function_exists( 'tutor_course_loop_footer' ) ) {
	/**
	 * Tutor course loop footer.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function tutor_course_loop_footer() {
		ob_start();
		tutor_load_template( 'loop.footer' );
		$output = apply_filters( 'tutor_course_loop_footer', ob_get_clean() );

		echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
	}
}

if ( ! function_exists( 'tutor_course_loop_start_content_wrap' ) ) {
	/**
	 * Tutor course loop start content wrap.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function tutor_course_loop_start_content_wrap() {
		ob_start();
		tutor_load_template( 'loop.start_content_wrap' );
		$output = apply_filters( 'tutor_course_loop_start_content_wrap', ob_get_clean() );

		echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
	}
}

if ( ! function_exists( 'tutor_course_loop_end_content_wrap' ) ) {
	/**
	 * Tutor course loop end content wrap.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function tutor_course_loop_end_content_wrap() {
		ob_start();
		tutor_load_template( 'loop.end_content_wrap' );
		$output = apply_filters( 'tutor_course_loop_end_content_wrap', ob_get_clean() );

		echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
	}
}

if ( ! function_exists( 'tutor_course_loop_thumbnail' ) ) {
	/**
	 * Tutor course loop thumbnail.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return mixed
	 */
	function tutor_course_loop_thumbnail( $echo = true ) {
		ob_start();
		tutor_load_template( 'loop.thumbnail' );
		$output = apply_filters( 'tutor_course_loop_thumbnail', ob_get_clean() );

		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
		} else {
			return $output;
		}
	}
}

if ( ! function_exists( 'tutor_course_loop_wrap_classes' ) ) {
	/**
	 * Tutor course loop wrap classes.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo.
	 *
	 * @return mixed
	 */
	function tutor_course_loop_wrap_classes( $echo = true ) {
		$course_id = get_the_ID();
		$classes   = apply_filters(
			'tutor_course_loop_wrap_classes',
			array(
				'tutor-course',
				'tutor-course-loop',
				'tutor-course-loop-' . $course_id,
			)
		);

		$class = implode( ' ', $classes );
		if ( $echo ) {
			echo esc_attr( $class );
		}

		return esc_attr( $class );
	}
}

if ( ! function_exists( 'tutor_course_loop_col_classes' ) ) {
	/**
	 * Tutor course loop col classes.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo.
	 *
	 * @return mixed
	 */
	function tutor_course_loop_col_classes( $echo = true ) {
		$course_filter      = (bool) tutor_utils()->get_option( 'course_archive_filter', false );
		$course_archive_arg = isset( $GLOBALS['tutor_course_archive_arg'] ) ? $GLOBALS['tutor_course_archive_arg']['column_per_row'] : null;
		$course_cols        = null === $course_archive_arg ? tutor_utils()->get_option( 'courses_col_per_row', 3 ) : $course_archive_arg;
		$classes            = apply_filters(
			'tutor_course_loop_col_classes',
			array(
				'tutor-col-' . $course_cols,
			)
		);

		$class = implode( ' ', $classes );
		if ( $echo ) {
			echo esc_attr( $class );
		}

		return esc_attr( $class );
	}
}


if ( ! function_exists( 'tutor_container_classes' ) ) {
	/**
	 * Tutor container classes.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo.
	 *
	 * @return mixed
	 */
	function tutor_container_classes( $echo = true ) {

		$classes = apply_filters(
			'tutor_container_classes',
			array(
				'tutor-wrap tutor-courses-wrap',
				'tutor-container',
			)
		);

		$classes = apply_filters(
			'tutor_container_classes',
			array(
				'tutor-wrap tutor-courses-wrap',
				'tutor-container',
			)
		);

		$class = implode( ' ', $classes );

		if ( $echo ) {
			echo esc_attr( $class );
		}

		return esc_attr( $class );
	}
}
if ( ! function_exists( 'tutor_post_class' ) ) {
	/**
	 * Tutor post class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $default default class.
	 *
	 * @return void
	 */
	function tutor_post_class( $default = '' ) {
		$classes = apply_filters(
			'tutor_post_class',
			array(
				'tutor-wrap',
				$default,
			)
		);

		post_class( $classes );
	}
}

if ( ! function_exists( 'tutor_widget_course_loop_classes' ) ) {
	/**
	 * Get classes for widget loop single course wrap.
	 *
	 * @since 1.3.1
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string
	 */
	function tutor_widget_course_loop_classes( $echo = true ) {

		$classes = apply_filters(
			'tutor_widget_course_loop_classes',
			array(
				'tutor-widget-course-loop',
				'tutor-widget-course',
				'tutor-widget-course-' . get_the_ID(),
			)
		);

		$class = implode( ' ', $classes );
		if ( $echo ) {
			echo esc_attr( $class );
		}

		return esc_attr( $class );
	}
}

if ( ! function_exists( 'get_tutor_course_thumbnail' ) ) {
	/**
	 * Get course thumbnail.
	 *
	 * @since 1.0.0
	 *
	 * @param string $size size.
	 * @param bool   $url whether to return url only.
	 *
	 * @return string
	 */
	function get_tutor_course_thumbnail( $size = 'post-thumbnail', $url = false ) {
		$post_id           = get_the_ID();
		$size              = apply_filters( 'tutor_course_thumbnail_size', $size, $post_id );
		$post_thumbnail_id = (int) get_post_thumbnail_id( $post_id );
		$placeholder_url   = tutor()->url . 'assets/images/placeholder.svg';
		$thumb_url         = $post_thumbnail_id ? wp_get_attachment_image_url( $post_thumbnail_id, $size ) : $placeholder_url;
		$thumb_url         = apply_filters( 'tutor_course_thumb_url', $thumb_url, $post_id, $size, $post_thumbnail_id );

		if ( $url ) {
			return $thumb_url;
		}

		echo '<div class="tutor-course-thumbnail">
            <img src="' . esc_url( $thumb_url ) . '" />
        </div>';
	}
}

if ( ! function_exists( 'get_tutor_course_thumbnail_src' ) ) {
	/**
	 * Get the course/post thumbnail src.
	 *
	 * @since 1.0.0
	 * @since 2.2.0 $id param added to provide post id to access outside of post loop.
	 *
	 * @since 3.4.1 Attachment src fallback added to show placeholder image
	 *
	 * @param string $size size of thumb.
	 * @param int    $id post id.
	 *
	 * @return string src of the post thumbnail | default placeholder
	 */
	function get_tutor_course_thumbnail_src( $size = 'post-thumbnail', $id = 0 ) {
		$post_id           = $id ? $id : get_the_ID();
		$post_thumbnail_id = (int) get_post_thumbnail_id( $post_id );
		$placeholder_url   = tutor()->url . 'assets/images/placeholder.svg';

		if ( $post_thumbnail_id ) {
			$size = apply_filters( 'tutor_course_thumbnail_size', $size, $post_id );
			$src  = wp_get_attachment_image_url( $post_thumbnail_id, $size, false );
			if ( ! $src ) {
				$src = $placeholder_url;
			}
		} else {
			$src = apply_filters( 'tutor_course_thumbnail_placeholder', $placeholder_url, $post_id );
		}

		$src = apply_filters( 'tutor_course_thumb_url', $src, $post_id, $size, $post_thumbnail_id );

		return $src;
	}
}

if ( ! function_exists( 'tutor_course_loop_meta' ) ) {
	/**
	 * Course loop meta.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function tutor_course_loop_meta() {
		ob_start();
		tutor_load_template( 'loop.meta' );
		$output = apply_filters( 'tutor_course_loop_meta', ob_get_clean() );

		echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
	}
}

if ( ! function_exists( 'tutor_course_loop_author' ) ) {
	/**
	 * Get course author name in loop.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function tutor_course_loop_author() {
		ob_start();
		tutor_load_template( 'loop.course-author' );
		$output = apply_filters( 'tutor_course_loop_author', ob_get_clean() );

		echo $output; //phpcs:ignore -- data already escaped inside template file
	}
}

if ( ! function_exists( 'tutor_course_loop_price' ) ) {
	/**
	 * Course loop price.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function tutor_course_loop_price() {

		ob_start();

		$course_id    = get_the_ID();
		$can_continue = EnrollmentModel::is_enrolled( $course_id ) || get_post_meta( $course_id, '_tutor_is_public_course', true ) == 'yes';

		// Check for further access type like course content access settings.
		if ( ! $can_continue ) {
			$can_continue = tutor_utils()->has_user_course_content_access( get_current_user_id(), $course_id );
		}

		if ( $can_continue ) {
			tutor_load_template( 'loop.course-continue' );

		} elseif ( tutor_utils()->is_course_added_to_cart( $course_id ) ) {
			tutor_load_template( 'loop.course-in-cart' );

		} else {
			$tutor_course_sell_by = apply_filters( 'tutor_course_sell_by', null );

			if ( $tutor_course_sell_by && ( tutor_utils()->is_course_purchasable( $course_id ) || ! is_user_logged_in() ) ) {
				tutor_load_template( 'loop.course-price-' . $tutor_course_sell_by );
			} else {
				tutor_load_template( 'loop.course-price' );
			}
		}
		echo apply_filters( 'tutor_course_loop_price', ob_get_clean(), $course_id ); //phpcs:ignore -- already escaped inside template file
	}
}

if ( ! function_exists( 'tutor_course_loop_rating' ) ) {
	/**
	 * Get Course rating.
	 *
	 * @since 1.0.0
	 * @since 1.4.5 updated.
	 *
	 * @return void
	 */
	function tutor_course_loop_rating() {

		$disable = ! get_tutor_option( 'enable_course_review' );
		if ( $disable ) {
			return;
		}

		ob_start();
		tutor_load_template( 'loop.rating' );
		$output = apply_filters( 'tutor_course_loop_rating', ob_get_clean() );

		echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
	}
}

if ( ! function_exists( 'tutor_course_loop_add_to_cart' ) ) {
	/**
	 * Get add to cart form.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string
	 */
	function tutor_course_loop_add_to_cart( $echo = true ) {
		ob_start();
		$tutor_course_sell_by = apply_filters( 'tutor_course_sell_by', null );

		if ( $tutor_course_sell_by ) {
			tutor_load_template( 'loop.add-to-cart-' . $tutor_course_sell_by );
		}

		$output = apply_filters( 'tutor_course_loop_add_to_cart_link', ob_get_clean() );

		if ( $echo ) {
			echo wp_kses_post( $output );
		}
		return $output;
	}
}

if ( ! function_exists( 'tutor_course_price' ) ) {
	/**
	 * Get course price.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function tutor_course_price() {
		ob_start();
		tutor_load_template( 'single.course.wc-price-html' );
		$output = apply_filters( 'tutor_course_price', ob_get_clean() );

		echo $output; //phpcs:ignore -- data already escaped inside template file
	}
}

if ( ! function_exists( 'tutor_the_excerpt' ) ) {
	/**
	 * Echo the excerpt of TUTOR post type.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id post ID.
	 *
	 * @return void
	 */
	function tutor_the_excerpt( $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		echo esc_textarea( tutor_get_the_excerpt( $post_id ) );
	}
}
if ( ! function_exists( 'tutor_get_the_excerpt' ) ) {
	/**
	 * Return excerpt of TUTOR post type.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id post ID.
	 *
	 * @return mixed
	 */
	function tutor_get_the_excerpt( $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		$get_post = get_post( $post_id );
		return apply_filters( 'tutor_get_the_excerpt', $get_post->post_excerpt );
	}
}

if ( ! function_exists( 'get_tutor_course_author' ) ) {
	/**
	 * Return course author.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	function get_tutor_course_author() {
		global $post;
		return apply_filters( 'get_tutor_course_author', get_the_author_meta( 'display_name', $post->post_author ) );
	}
}

/**
 * Get course author ID.
 *
 * @since 1.0.0
 *
 * @return int
 */
function get_tutor_course_author_id() {
	global $post;
	return (int) $post->post_author;
}

if ( ! function_exists( 'tutor_course_benefits' ) ) {
	/**
	 * Course benefits return array.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id course ID.
	 *
	 * @return array
	 */
	function tutor_course_benefits( $course_id = 0 ) {
		if ( ! $course_id ) {
			$course_id = get_the_ID();
		}
		$benefits = get_post_meta( $course_id, '_tutor_course_benefits', true );

		$benefits_array = array();
		if ( $benefits ) {
			$benefits_array = explode( "\n", $benefits );
		}

		$array = array_filter( array_map( 'trim', $benefits_array ) );

		return apply_filters( 'tutor_course/single/benefits', $array, $course_id );
	}
}

if ( ! function_exists( 'tutor_course_benefits_html' ) ) {
	/**
	 * Course single page benefits.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string
	 */
	function tutor_course_benefits_html( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.course.course-benefits' );
		$output = apply_filters( 'tutor_course/single/benefits_html', ob_get_clean() );

		if ( $echo ) {
			echo wp_kses_post( $output );
		}
		return $output;
	}
}

if ( ! function_exists( 'tutor_course_topics' ) ) {
	/**
	 * Return Topics HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string
	 */
	function tutor_course_topics( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.course.course-topics' );
		$output = apply_filters( 'tutor_course/single/topics', ob_get_clean() );
		wp_reset_postdata();

		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
		}

		return $output;
	}
}

if ( ! function_exists( 'tutor_course_requirements' ) ) {
	/**
	 * Return course requirements in array.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id course ID.
	 *
	 * @return array
	 */
	function tutor_course_requirements( $course_id = 0 ) {
		if ( ! $course_id ) {
			$course_id = get_the_ID();
		}
		$requirements = get_post_meta( $course_id, '_tutor_course_requirements', true );

		$requirements_array = array();
		if ( $requirements ) {
			$requirements_array = explode( "\n", $requirements );
		}

		$array = array_filter( array_map( 'trim', $requirements_array ) );
		return apply_filters( 'tutor_course/single/requirements', $array, $course_id );
	}
}

if ( ! function_exists( 'tutor_course_requirements_html' ) ) {
	/**
	 * Return course requirements in course single page.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string
	 */
	function tutor_course_requirements_html( $echo = true ) {
		ob_start();
		tutor_course_material_includes_html();
		tutor_load_template( 'single.course.course-requirements' );
		$output = apply_filters( 'tutor_course/single/requirements_html', ob_get_clean() );

		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
		}
		return $output;
	}
}


if ( ! function_exists( 'tutor_course_target_audience' ) ) {
	/**
	 * Return target audience in course single page.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id course ID.
	 *
	 * @return array
	 */
	function tutor_course_target_audience( $course_id = 0 ) {
		if ( ! $course_id ) {
			$course_id = get_the_ID();
		}
		$target_audience = get_post_meta( $course_id, '_tutor_course_target_audience', true );

		$target_audience_array = array();
		if ( $target_audience ) {
			$target_audience_array = explode( "\n", $target_audience );
		}

		$array = array_filter( array_map( 'trim', $target_audience_array ) );
		return apply_filters( 'tutor_course/single/target_audience', $array, $course_id );
	}
}

if ( ! function_exists( 'tutor_course_target_audience_html' ) ) {
	/**
	 * Return target audience in course single page.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string|void
	 */
	function tutor_course_target_audience_html( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.course.course-target-audience' );
		$output = apply_filters( 'tutor_course/single/audience_html', ob_get_clean() );

		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
		}
		return $output;
	}
}


if ( ! function_exists( 'tutor_course_material_includes' ) ) {
	/**
	 * Return course material includes.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id course ID.
	 *
	 * @return array
	 */
	function tutor_course_material_includes( $course_id = 0 ) {
		if ( ! $course_id ) {
			$course_id = get_the_ID();
		}
		$target_audience = get_post_meta( $course_id, '_tutor_course_material_includes', true );

		$target_audience_array = array();
		if ( $target_audience ) {
			$target_audience_array = explode( "\n", $target_audience );
		}

		$array = array_filter( array_map( 'trim', $target_audience_array ) );
		return apply_filters( 'tutor_course/single/material_includes', $array, $course_id );
	}
}

if ( ! function_exists( 'tutor_course_material_includes_html' ) ) {
	/**
	 * Return course material includes HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string
	 */
	function tutor_course_material_includes_html( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.course.material-includes' );
		$output = apply_filters( 'tutor_course/single/material_includes', ob_get_clean() );

		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
		}
		return $output;
	}
}


if ( ! function_exists( 'tutor_course_instructors_html' ) ) {
	/**
	 * Return course instructors HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string|void
	 */
	function tutor_course_instructors_html( $echo = true ) {
		$display_course_instructors = tutor_utils()->get_option( 'display_course_instructors' );
		if ( ! $display_course_instructors ) {
			return null;
		}

		ob_start();
		tutor_load_template( 'single.course.instructors' );
		$output = apply_filters( 'tutor_course/single/instructors_html', ob_get_clean() );

		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
		}
		return $output;
	}
}

if ( ! function_exists( 'tutor_course_target_reviews_html' ) ) {
	/**
	 * Return course reviews HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string|void
	 */
	function tutor_course_target_reviews_html( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.course.reviews' );

		$output = apply_filters( 'tutor_course/single/reviews_html', ob_get_clean() );

		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
		}
		return $output;
	}
}

if ( ! function_exists( 'tutor_course_content' ) ) {
	/**
	 * Course single page main content / description.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string|void
	 */
	function tutor_course_content( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.course.course-content' );
		$output = apply_filters( 'tutor_course/single/content', ob_get_clean() );

		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
		}

		return $output;
	}
}

if ( ! function_exists( 'tutor_course_lead_info' ) ) {
	/**
	 * Course single page lead info.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string|void
	 */
	function tutor_course_lead_info( $echo = true ) {
		ob_start();

		$course_id        = get_the_ID();
		$course_post_type = tutor()->course_post_type;
		$query_course     = new WP_Query(
			apply_filters(
				'tutor_course_lead_info_args',
				array(
					'p'         => $course_id,
					'post_type' => $course_post_type,
				)
			)
		);

		if ( $query_course->have_posts() ) {
			while ( $query_course->have_posts() ) {
				$query_course->the_post();
				tutor_load_template( 'single.course.lead-info' );
			}
			wp_reset_postdata();
		}

		$output = apply_filters( 'tutor_course/single/lead_info', ob_get_clean() );

		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
		}
		return $output;
	}
}

if ( ! function_exists( 'tutor_course_enrolled_lead_info' ) ) {
	/**
	 * Course enrolled lead info.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string|void
	 */
	function tutor_course_enrolled_lead_info( $echo = true ) {
		ob_start();

		$course_id        = get_the_ID();
		$course_post_type = tutor()->course_post_type;
		$query_course     = new WP_Query(
			array(
				'p'         => $course_id,
				'post_type' => $course_post_type,
			)
		);

		if ( $query_course->have_posts() ) {
			while ( $query_course->have_posts() ) {
				$query_course->the_post();
				tutor_load_template( 'single.course.lead-info' );
			}
			wp_reset_postdata();
		}

		$output = apply_filters( 'tutor_course/single/enrolled/lead_info', ob_get_clean() );

		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
		}

		return $output;
	}
}

if ( ! function_exists( 'tutor_lesson_lead_info' ) ) {
	/**
	 * Lesson lead info.
	 *
	 * @since 1.0.0
	 *
	 * @param int  $lesson_id lesson id.
	 * @param bool $echo      whether to echo content.
	 *
	 * @return string|void
	 */
	function tutor_lesson_lead_info( $lesson_id = 0, $echo = true ) {
		if ( ! $lesson_id ) {
			$lesson_id = get_the_ID();
		}

		ob_start();
		$course_id        = tutor_utils()->get_course_id_by( 'lesson', $lesson_id );
		$course_post_type = tutor()->course_post_type;
		$query_course     = new WP_Query(
			array(
				'p'         => $course_id,
				'post_type' => $course_post_type,
			)
		);

		if ( $query_course->have_posts() ) {
			while ( $query_course->have_posts() ) {
				$query_course->the_post();
				tutor_load_template( 'single.course.lead-info' );
			}
			wp_reset_postdata();
		}
		$output = apply_filters( 'tutor_course/single/enrolled/lead_info', ob_get_clean() );

		if ( $echo ) {
			echo $output; //phpcs:ignore -- already escaped inside template file
		}

		return $output;
	}
}

if ( ! function_exists( 'tutor_course_video' ) ) {
	/**
	 * Course video.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string|void
	 */
	function tutor_course_video( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.video.video' );
		$output = apply_filters( 'tutor_course/single/video', ob_get_clean() );

		if ( $echo ) {
			echo $output; //phpcs:ignore -- already escaped inside template file
		}
		return $output;
	}
}

if ( ! function_exists( 'tutor_lesson_video' ) ) {
	/**
	 * Lesson video.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string|void
	 */
	function tutor_lesson_video( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.video.video' );
		$output = apply_filters( 'tutor_lesson/single/video', ob_get_clean() );

		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
		}
		return $output;
	}
}

if ( ! function_exists( 'get_tutor_posts_attachments' ) ) {
	/**
	 * Get all lessons attachments.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string|void
	 */
	function get_tutor_posts_attachments( $echo = true ) {
		ob_start();
		tutor_load_template( 'global.attachments' );
		$output = apply_filters( 'tutor_lesson/single/attachments', ob_get_clean() );

		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
		}
		return $output;
	}
}

if ( ! function_exists( 'tutor_lesson_content' ) ) {
	/**
	 * Render Lesson Main Content.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string|void
	 */
	function tutor_lesson_content( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.lesson.content' );
		$output = apply_filters( 'tutor_lesson/single/content', ob_get_clean() );

		if ( $echo ) {
			echo $output; //phpcs:ignore -- already escaped inside template file
		}

		return $output;
	}
}

if ( ! function_exists( 'tutor_lesson_mark_complete_html' ) ) {
	/**
	 * Lesson mark complete HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string|void
	 */
	function tutor_lesson_mark_complete_html( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.lesson.complete_form' );
		$output = apply_filters( 'tutor_lesson/single/complete_form', ob_get_clean() );

		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
		}

		return $output;
	}
}

/**
 * Course question and answer.
 *
 * @since 1.0.0
 *
 * @param bool $echo whether to echo content.
 *
 * @return string|void
 */
function tutor_course_question_and_answer( $echo = true ) {
	ob_start();
	tutor_load_template( 'single.course.enrolled.question_and_answer' );
	$output = apply_filters( 'tutor_course/single/question_and_answer', ob_get_clean() );

	if ( $echo ) {
		echo $output; //phpcs:ignore -- already escaped inside template file
	}

	return $output;
}

/**
 * Render course info tab content.
 *
 * @since 2.0.0
 * @since 2.0.5 updated.
 *
 * @return void
 */
function tutor_course_info_tab() {
	tutor_course_content();
	tutor_course_benefits_html();
	tutor_course_topics();
}


/**
 * Course announcements.
 *
 * @since 1.0.0
 *
 * @param bool $echo whether to echo content.
 *
 * @return string|void
 */
function tutor_course_announcements( $echo = true ) {
	ob_start();
	tutor_load_template( 'single.course.enrolled.announcements' );
	$output = apply_filters( 'tutor_course/single/announcements', ob_get_clean() );

	if ( $echo ) {
		echo $output; //phpcs:ignore -- already escaped inside template file
	}

	return $output;
}

/**
 * Single quiz top.
 *
 * @since 1.0.0
 *
 * @param bool $echo whether to echo content.
 *
 * @return string|void
 */
function tutor_single_quiz_top( $echo = true ) {
	ob_start();
	tutor_load_template( 'single.quiz.top' );
	$output = apply_filters( 'tutor_single_quiz/top', ob_get_clean() );

	if ( $echo ) {
		echo $output; //phpcs:ignore -- already escaped inside template file
	}
	return $output;
}

/**
 * Single quiz body.
 *
 * @since 1.0.0
 *
 * @param bool $echo whether to echo content.
 *
 * @return string|void
 */
function tutor_single_quiz_body( $echo = true ) {
	ob_start();
	tutor_load_template( 'single.quiz.body' );
	$output = apply_filters( 'tutor_single_quiz/body', ob_get_clean() );

	if ( $echo ) {
		echo $output; //phpcs:ignore -- data already escaped inside template file
	}
	return $output;
}

/**
 * Get the quiz description.
 *
 * @since 1.0.0
 *
 * @param bool $echo whether to echo content.
 *
 * @return string|void
 */
function tutor_single_quiz_content( $echo = true ) {
	ob_start();
	tutor_load_template( 'single.quiz.content' );
	$output = apply_filters( 'tutor_single_quiz/content', ob_get_clean() );

	if ( $echo ) {
		echo $output; //phpcs:ignore -- already escaped inside template file
	}
	return $output;
}


/**
 * Single quiz no course belongs.
 *
 * @since 1.0.0
 *
 * @param bool $echo whether to echo content.
 *
 * @return string|void
 */
function tutor_single_quiz_no_course_belongs( $echo = true ) {
	ob_start();
	tutor_load_template( 'single.quiz.no_course_belongs' );
	$output = apply_filters( 'tutor_single_quiz/no_course_belongs', ob_get_clean() );

	if ( $echo ) {
		echo $output; //phpcs:ignore -- already escaped inside template file
	}
	return $output;
}

/**
 * Single quiz contents.
 *
 * @since 1.0.0
 *
 * @param bool $echo whether to echo content.
 *
 * @return string|void
 */
function single_quiz_contents( $echo = true ) {

	ob_start();
	tutor_load_template( 'single.quiz.single_quiz_contents' );
	$output = apply_filters( 'tutor_single_quiz/single_quiz_contents', ob_get_clean() );

	if ( $echo ) {
		echo $output; //phpcs:ignore -- already escaped inside template file
	}
	return $output;
}

/**
 * Get course level.
 *
 * @since 1.0.0
 *
 * @param int $course_id course ID.
 *
 * @return string|bool
 */
function get_tutor_course_level( $course_id = 0 ) {
	if ( ! $course_id ) {
		$course_id = get_the_ID();
	}
	if ( ! $course_id ) {
		return '';
	}

	$course_level = get_post_meta( $course_id, '_tutor_course_level', true );

	if ( $course_level ) {
		return tutor_utils()->course_levels( $course_level );
	}
	return false;
}

if ( ! function_exists( 'get_tutor_course_duration_context' ) ) {
	/**
	 * Get course duration context.
	 *
	 * @since 1.0.0
	 *
	 * @param int  $course_id  course ID.
	 * @param bool $short_form whether to use short form.
	 *
	 * @return string|bool
	 */
	function get_tutor_course_duration_context( $course_id = 0, $short_form = false ) {
		if ( ! $course_id ) {
			$course_id = get_the_ID();
		}
		if ( ! $course_id ) {
			return '';
		}

		$duration         = get_post_meta( $course_id, '_course_duration', true );
		$duration_hours   = tutor_utils()->avalue_dot( 'hours', $duration );
		$duration_minutes = tutor_utils()->avalue_dot( 'minutes', $duration );
		$duration_seconds = tutor_utils()->avalue_dot( 'seconds', $duration );

		$hour_format   = $short_form ? __( 'h', 'tutor' ) : ' ' . ( $duration_hours > 1 ? __( 'hours', 'tutor' ) : __( 'hour', 'tutor' ) );
		$minute_format = $short_form ? __( 'm', 'tutor' ) : ' ' . ( $duration_minutes > 1 ? __( 'minutes', 'tutor' ) : __( 'minute', 'tutor' ) );
		$second_format = $short_form ? __( 's', 'tutor' ) : ' ' . ( $duration_seconds > 1 ? __( 'seconds', 'tutor' ) : __( 'second', 'tutor' ) );

		if ( $duration ) {
			$output = '';
			if ( $duration_hours > 0 ) {
				$output .= '<span class="tutor-meta-level">' . $duration_hours . '</span><span class="tutor-meta-value tutor-color-secondary tutor-mr-4">' . $hour_format . '</span>';
			}

			if ( $duration_minutes > 0 ) {
				$output .= '<span class="tutor-meta-level">' . $duration_minutes . '</span><span class="tutor-meta-value tutor-color-secondary tutor-mr-4">' . $minute_format . '</span>';
			}

			if ( ! $duration_hours && ! $duration_minutes && $duration_seconds > 0 ) {
				$output .= '<span class="tutor-meta-level">' . $duration_seconds . '</span><span class="tutor-meta-value tutor-color-secondary tutor-mr-4">' . $second_format . '</span>';
			}

			return $output;
		}

		return false;
	}
}
if ( ! function_exists( 'get_tutor_course_categories' ) ) {
	/**
	 * Get course categories.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id course ID.
	 *
	 * @return array|bool|WP_Error
	 */
	function get_tutor_course_categories( $course_id = 0 ) {
		if ( ! $course_id ) {
			$course_id = get_the_ID();
		}
		$terms = get_the_terms( $course_id, CourseModel::COURSE_CATEGORY );

		return $terms;
	}
}

if ( ! function_exists( 'get_tutor_course_tags' ) ) {
	/**
	 * Get course tags.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id course ID.
	 *
	 * @return array|false|WP_Error
	 */
	function get_tutor_course_tags( $course_id = 0 ) {
		if ( ! $course_id ) {
			$course_id = get_the_ID();
		}
		$terms = get_the_terms( $course_id, CourseModel::COURSE_TAG );

		return $terms;
	}
}

if ( ! function_exists( 'tutor_course_tags_html' ) ) {
	/**
	 * Template for course tags html.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string
	 */
	function tutor_course_tags_html( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.course.tags' );
		$output = apply_filters( 'tutor_course/single/tags_html', ob_get_clean() );

		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore
		}

		return $output;
	}
}

if ( ! function_exists( 'tutor_lesson_sidebar_question_and_answer' ) ) {
	/**
	 * Get Q&A in lesson sidebar.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string|void
	 */
	function tutor_lesson_sidebar_question_and_answer( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.lesson.sidebar_question_and_answer' );
		$output = apply_filters( 'tutor_lesson/single/sidebar_question_and_answer', ob_get_clean() );

		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
		}

		return $output;
	}
}

if ( ! function_exists( 'tutor_assignment_content' ) ) {
	/**
	 * Get Assignment content.
	 *
	 * @since 1.3.3
	 *
	 * @param bool $echo whether to echo content.
	 *
	 * @return string|void
	 */
	function tutor_assignment_content( $echo = true ) {
		$output = apply_filters( 'tutor_assignment/single/content', '' );

		if ( $echo ) {
			echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
		}

		return $output;
	}
}

if ( ! function_exists( 'get_tnotice' ) ) {
	/**
	 * Get notice.
	 *
	 * @since 1.4.0
	 *
	 * @param string $msg   message.
	 * @param string $title title.
	 * @param string $type  type.
	 *
	 * @return string
	 */
	function get_tnotice( $msg = '', $title = 'Success', $type = 'success' ) {

		$output = '<div class="tnotice tnotice--' . $type . '">
        <div class="tnotice__icon">&iexcl;</div>
        <div class="tnotice__content">';

		if ( $title ) {
			$output .= '<p class="tnotice__type">' . $title . '</p>';
		}
		$output .= '<p class="tnotice__message">' . $msg . '</p>
        </div>
    	</div>';

		return $output;
	}
}

/**
 * Next Previous Pagination.
 *
 * @since 1.4.7
 *
 * @param int  $course_content_id course content ID.
 * @param bool $echo              whether to echo content.
 *
 * @return string|void
 */
function tutor_next_previous_pagination( $course_content_id = 0, $echo = true ) {
	$content_id  = tutor_utils()->get_post_id( $course_content_id );
	$contents    = tutor_utils()->get_course_prev_next_contents_by_id( $content_id );
	$previous_id = $contents->previous_id;
	$next_id     = $contents->next_id;

	ob_start();
	do_action( 'tutor_lesson_next_previous_pagination_before' );
	tutor_load_template( 'single.next-previous-pagination', compact( 'previous_id', 'next_id' ) );
	do_action( 'tutor_lesson_next_previous_pagination_after' );
	$output = apply_filters( 'tutor/single/next_previous_pagination', ob_get_clean() );

	if ( $echo ) {
		echo tutor_kses_html( $output ); //phpcs:ignore -- already escaped inside template file
	}

	return $output;
}

if ( ! function_exists( 'tutor_load_template_from_custom_path' ) ) {
	/**
	 * Load custom template from any given file.
	 *
	 * @since 1.9.8
	 *
	 * @param string $template template path.
	 * @param array  $data     data to pass.
	 * @param bool   $once     whether to include once.
	 *
	 * @return void
	 */
	function tutor_load_template_from_custom_path( $template = null, $data = array(), $once = true ) {
		do_action( 'tutor_load_template_from_custom_path_before', $template, $data );
		if ( file_exists( $template ) ) {
			if ( $once ) {
				include_once $template;
			} else {
				include $template;
			}
		}
		do_action( 'tutor_load_template_from_custom_path_after', $template, $data );
	}
}

if ( ! function_exists( 'tutor_enrolled_course_progress' ) ) {
	/**
	 * Load enrolled course progress template.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	function tutor_enrolled_course_progress() {
		global $wp_query;
		$query_vars = $wp_query->query_vars;
		if ( isset( $query_vars['tutor_dashboard_page'] ) && 'enrolled-courses' === $query_vars['tutor_dashboard_page'] ) {
			tutor_load_template_from_custom_path( tutor()->path . 'templates/loop/enrolled-course-progress.php', '', false );
		}
	}
}

if ( ! function_exists( 'tutor_permission_denied_template' ) ) {
	/**
	 * Load permission denied template.
	 *
	 * It will load permission denied template & return so not code
	 * after this will execute
	 *
	 * @since 2.2.0
	 *
	 * @param integer $post_id post id if 0 then current post id will be used.
	 *
	 * @return void
	 */
	function tutor_permission_denied_template( int $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		$args = array(
			'headline'    => __( 'Permission Denied', 'tutor' ),
			'message'     => __( 'You don\'t have the right to edit this course', 'tutor' ),
			'description' => __( 'Please make sure you are logged in to correct account', 'tutor' ),
			'button'      => array(
				'url'  => get_permalink( $post_id ),
				'text' => __( 'View Course', 'tutor' ),
			),
		);

		tutor_load_template( 'permission-denied', $args );
		return;
	}
}

if ( ! function_exists( 'get_template_buffer' ) ) {
	/**
	 * Render a template and return its output as a string.
	 *
	 * @since 4.0.0
	 *
	 * @param string $template   Template file path or slug.
	 * @param array  $data       Data to be passed to the template.
	 * @param bool   $once       Whether the template should be loaded only once.
	 *                           Defaults to true.
	 *
	 * @return string Rendered template output.
	 */
	function get_template_buffer( $template, $data, $once = true ) {

		ob_start();

		tutor_load_template_from_custom_path( $template, $data, $once );

		return ob_get_clean();
	}
}
