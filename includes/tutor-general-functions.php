<?php
/**
 * Tutor general functions
 *
 * @package TutorFunctions
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

use Tutor\Cache\FlashMessage;
use Tutor\Ecommerce\OptionKeys;
use Tutor\Ecommerce\Settings;
use TUTOR\Input;
use Tutor\Models\CourseModel;
use Tutor\Course;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tutor input sanitization
 */

if ( ! function_exists( 'tutor_sanitize_data' ) ) {
	/**
	 * Escaping for Sanitize data.
	 *
	 * @since 1.9.13
	 *
	 * @param  string $input.
	 * @param  string $type.
	 * @return string|array|object
	 */
	function tutor_sanitize_data( $input = null, $type = null ) {
		$array  = array();
		$object = new stdClass();

		if ( is_string( $input ) ) {

			if ( 'textarea' == $type ) {
				$input = sanitize_textarea_field( $input );
			} elseif ( 'kses' == $type ) {
				$input = wp_kses_post( $input );
			} else {
				$input = sanitize_text_field( $input );
			}

			return $input;

		} elseif ( is_object( $input ) && count( get_object_vars( $input ) ) ) {

			foreach ( $input as $key => $value ) {
				if ( is_object( $value ) ) {
					$object->$key = tutor_sanitize_data( $value );
				} else {
					$key          = sanitize_text_field( $key );
					$value        = sanitize_text_field( $value );
					$object->$key = $value;
				}
			}
			return $object;
		} elseif ( is_array( $input ) && count( $input ) ) {
			foreach ( $input as $key => $value ) {
				if ( is_array( $value ) ) {
					$array[ $key ] = tutor_sanitize_data( $value );
				} else {
					$key           = sanitize_text_field( $key );
					$value         = sanitize_text_field( $value );
					$array[ $key ] = $value;
				}
			}

			return $array;
		}
	}
}

if ( ! function_exists( 'tutor_placeholder_img_src' ) ) {
	function tutor_placeholder_img_src() {
		$src = tutor()->url . 'assets/images/placeholder.svg';
		return apply_filters( 'tutor_placeholder_img_src', $src );
	}
}

/**
 * @return string
 *
 * Get course categories selecting UI
 *
 * @since v.1.3.4
 */

if ( ! function_exists( 'tutor_course_categories_dropdown' ) ) {
	function tutor_course_categories_dropdown( $post_ID = 0, $args = array() ) {

		$default = array(
			'classes'  => '',
			'name'     => 'tax_input[course-category]',
			'multiple' => true,
		);

		$args = apply_filters( 'tutor_course_categories_dropdown_args', array_merge( $default, $args ) );

		$multiple_select = '';

		if ( tutor_utils()->array_get( 'multiple', $args ) ) {
			if ( isset( $args['name'] ) ) {
				$args['name'] = $args['name'] . '[]';
			}
			$multiple_select = "multiple='multiple'";
		}

		extract( $args );

		$classes = (array) $classes;
		$classes = implode( ' ', $classes );

		$categories = tutor_utils()->get_course_categories();

		$output  = '';
		$output .= '<select name="' . $name . '" ' . $multiple_select . ' class="' . $classes . '" data-placeholder="' . __( 'Search Course Category. ex. Design, Development, Business', 'tutor' ) . '">';
		$output .= '<option value="">' . __( 'Select a category', 'tutor' ) . '</option>';
		$output .= _generate_categories_dropdown_option( $post_ID, $categories, $args );
		$output .= '</select>';

		return $output;
	}
}

/**
 * @return string
 *
 * Get course tags selecting UI
 *
 * @since v.1.3.4
 */

if ( ! function_exists( 'tutor_course_tags_dropdown' ) ) {
	function tutor_course_tags_dropdown( $post_ID = 0, $args = array() ) {

		$default = array(
			'classes'  => '',
			'name'     => 'tax_input[course-tag]',
			'multiple' => true,
		);

		$args = apply_filters( 'tutor_course_tags_dropdown_args', array_merge( $default, $args ) );

		$multiple_select = '';

		if ( tutor_utils()->array_get( 'multiple', $args ) ) {
			if ( isset( $args['name'] ) ) {
				$args['name'] = $args['name'] . '[]';
			}
			$multiple_select = "multiple='multiple'";
		}

		extract( $args );

		$classes = (array) $classes;
		$classes = implode( ' ', $classes );

		$tags = tutor_utils()->get_course_tags();

		$output  = '';
		$output .= '<select name=' . $name . ' ' . $multiple_select . ' class="' . $classes . '" data-placeholder="' . __( 'Search Course Tags. ex. Design, Development, Business', 'tutor' ) . '">';
		$output .= '<option value="">' . __( 'Select a tag', 'tutor' ) . '</option>';
		$output .= _generate_tags_dropdown_option( $post_ID, $tags, $args );
		$output .= '</select>';

		return $output;
	}
}

/**
 * @param $categories
 * @param string $parent_name
 *
 * @return string
 *
 * Get selecting options, recursive supports
 *
 * @since v.1.3.4
 */

if ( ! function_exists( '_generate_categories_dropdown_option' ) ) {
	function _generate_categories_dropdown_option( $post_ID = 0, $categories = array(), $args = array(), $depth = 0 ) {
		$output = '';

		if ( ! tutor_utils()->count( $categories ) ) {
			return $output;
		}

		if ( ! is_numeric( $post_ID ) || $post_ID < 1 ) {
			return $output;
		}

		foreach ( $categories as $category_id => $category ) {
			if ( ! $category->parent ) {
				$depth = 0;
			}

			$childrens   = tutor_utils()->array_get( 'children', $category );
			$has_in_term = has_term( $category->term_id, 'course-category', $post_ID );

			$depth_seperator = '';
			if ( $depth ) {
				for ( $depth_i = 0; $depth_i < $depth; $depth_i++ ) {
					$depth_seperator .= '-';
				}
			}

			$output .= '<option value="' . $category->term_id . '" ' . selected( $has_in_term, true, false ) . '>  ' . $depth_seperator . ' ' . $category->name . '</option>';

			if ( tutor_utils()->count( $childrens ) ) {
				$depth++;
				$output .= _generate_categories_dropdown_option( $post_ID, $childrens, $args, $depth );
			}
		}

		return $output;
	}
}
/**
 * @param $tags
 * @param string $parent_name
 *
 * @return string
 *
 * Get selecting options, recursive supports
 *
 * @since v.1.3.4
 */

if ( ! function_exists( '_generate_tags_dropdown_option' ) ) {
	function _generate_tags_dropdown_option( $post_ID = 0, $tags = array(), $args = array(), $depth = 0 ) {
		$output = '';

		if ( ! tutor_utils()->count( $tags ) ) {
			return $output;
		}

		if ( ! is_numeric( $post_ID ) || $post_ID < 1 ) {
			return $output;
		}

		foreach ( $tags as $tag ) {

			$has_in_term = has_term( $tag->term_id, 'course-tag', $post_ID );

			$output .= '<option value="' . esc_attr( $tag->name ) . '" ' . selected( $has_in_term, true, false ) . '>' . esc_html( $tag->name ) . '</option>';

		}

		return $output;
	}
}

/**
 * @param array $args
 *
 * @return string
 *
 * Generate course categories checkbox
 * @since  v.1.3.4
 */

if ( ! function_exists( 'tutor_course_categories_checkbox' ) ) {
	function tutor_course_categories_checkbox( $post_ID = 0, $args = array() ) {
		$default = array(
			'name' => 'tax_input[course-category]',
		);

		$args = apply_filters( 'tutor_course_categories_checkbox_args', array_merge( $default, $args ) );

		if ( isset( $args['name'] ) ) {
			$args['name'] = $args['name'] . '[]';
		}

		extract( $args );

		$categories = tutor_utils()->get_course_categories();
		$output     = '';
		$output    .= __tutor_generate_categories_checkbox( $post_ID, $categories, $args );

		return $output;
	}
}

/**
 * @param array $args
 *
 * @return string
 *
 * Generate course tags checkbox
 * @since  v.1.3.4
 */

if ( ! function_exists( 'tutor_course_tags_checkbox' ) ) {
	function tutor_course_tags_checkbox( $post_ID = 0, $args = array() ) {
		$default = array(
			'name' => 'tax_input[course-tag]',
		);

		$args = apply_filters( 'tutor_course_tags_checkbox_args', array_merge( $default, $args ) );

		if ( isset( $args['name'] ) ) {
			$args['name'] = $args['name'] . '[]';
		}

		extract( $args );

		$tags    = tutor_utils()->get_course_tags();
		$output  = '';
		$output .= __tutor_generate_tags_checkbox( $post_ID, $tags, $args );

		return $output;
	}
}

/**
 * @param $categories
 * @param string $parent_name
 * @param array $args
 *
 * @return string
 *
 * Internal function to generate course categories checkbox
 *
 * @since v.1.3.4
 */
if ( ! function_exists( '__tutor_generate_categories_checkbox' ) ) {
	function __tutor_generate_categories_checkbox( $post_ID = 0, $categories = array(), $args = array() ) {

		$output     = '';
		$input_name = tutor_utils()->array_get( 'name', $args );

		if ( tutor_utils()->count( $categories ) ) {
			$output .= '<ul class="tax-input-course-category">';
			foreach ( $categories as $category_id => $category ) {
				$childrens   = tutor_utils()->array_get( 'children', $category );
				$has_in_term = has_term( $category->term_id, 'course-category', $post_ID );

				$output .= '<li class="tax-input-course-category-item tax-input-course-category-item-' . $category->term_id . '"><label class="course-category-checkbox"> <input type="checkbox" name="' . $input_name . '" value="' . $category->term_id . '" ' . checked( $has_in_term, true, false ) . '/> <span>' . $category->name . '</span> </label>';

				if ( tutor_utils()->count( $childrens ) ) {
					$output .= __tutor_generate_categories_checkbox( $post_ID, $childrens, $args );
				}
				$output .= ' </li>';
			}
			$output .= '</ul>';
		}

		return $output;

	}
}
/**
 * @param $tags
 * @param string $parent_name
 * @param array $args
 *
 * @return string
 *
 * Internal function to generate course tags checkbox
 *
 * @since v.1.3.4
 */
if ( ! function_exists( '__tutor_generate_tags_checkbox' ) ) {
	function __tutor_generate_tags_checkbox( $post_ID = 0, $tags = array(), $args = array() ) {

		$output     = '';
		$input_name = tutor_utils()->array_get( 'name', $args );

		if ( tutor_utils()->count( $tags ) ) {
			$output .= '<ul class="tax-input-course-tag">';
			foreach ( $tags as $tag ) {
				$has_in_term = has_term( $tag->term_id, 'course-tag', $post_ID );

				$output .= '<li class="tax-input-course-tag-item tax-input-course-tag-item-' . $tag->term_id . '"><label class="course-tag-checkbox"> <input type="checkbox" name="' . $input_name . '" value="' . $tag->term_id . '" ' . checked( $has_in_term, true, false ) . ' /> <span>' . $tag->name . '</span> </label>';

				$output .= ' </li>';
			}
			$output .= '</ul>';
		}

		return $output;
	}
}

/**
 * @param string $content
 * @param string $title
 *
 * @return string
 *
 * Wrap course builder sections within div for frontend
 *
 * @since v.1.3.4
 */

if ( ! function_exists( 'course_builder_section_wrap' ) ) {
	function course_builder_section_wrap( $content = '', $title = '', $echo = true ) {
		$template = trailingslashit( tutor()->path . 'templates' ) . 'metabox-wrapper.php';
		if ( $echo ) {
			if ( file_exists( $template ) ) {
				include $template;
			} else {
				echo esc_html( $template ) . esc_html__( 'file not exists', 'tutor' );
			}
		} else {
			ob_start();
			if ( file_exists( $template ) ) {
				include $template;
			} else {
				echo esc_html( $template ) . esc_html__( 'file not exists', 'tutor' );
			}
			$html = ob_get_clean();
			return $html;
		}
	}
}


if ( ! function_exists( 'get_tutor_header' ) ) {
	function get_tutor_header( $fullScreen = false ) {
		$enable_spotlight_mode = tutor_utils()->get_option( 'enable_spotlight_mode' );

		if ( $enable_spotlight_mode || $fullScreen ) {
			?>
			<!doctype html>
			<html <?php language_attributes(); ?>>

			<head>
				<meta charset="<?php bloginfo( 'charset' ); ?>" />
				<meta name="viewport" content="width=device-width, initial-scale=1" />
				<link rel="profile" href="https://gmpg.org/xfn/11" />
			<?php wp_head(); ?>
			</head>

			<body <?php body_class(); ?>>
				<div id="tutor-page-wrap" class="tutor-site-wrap site">
				<?php
		} else {
			tutor_utils()->tutor_custom_header();
		}
	}
}

if ( ! function_exists( 'get_tutor_footer' ) ) {
	function get_tutor_footer( $fullScreen = false ) {
		$enable_spotlight_mode = tutor_utils()->get_option( 'enable_spotlight_mode' );
		if ( $enable_spotlight_mode || $fullScreen ) {
			?>
				</div>
			<?php wp_footer(); ?>

			</body>

			</html>
			<?php
		} else {
			tutor_utils()->tutor_custom_footer();
		}
	}
}

	/**
	 * @param null $key
	 * @param bool $default
	 *
	 * @return array|bool|mixed
	 *
	 * Get tutor option by this helper function
	 *
	 * @since v.1.3.6
	 */
if ( ! function_exists( 'get_tutor_option' ) ) {
	function get_tutor_option( $key = null, $default = false ) {
		return tutor_utils()->get_option( $key, $default );
	}
}

	/**
	 * @param null $key
	 * @param bool $value
	 *
	 * Update tutor option by this helper function
	 *
	 * @since v.1.3.6
	 */
if ( ! function_exists( 'update_tutor_option' ) ) {
	function update_tutor_option( $key = null, $value = false ) {
		tutor_utils()->update_option( $key, $value );
	}
}
	/**
	 * @param int $course_id
	 * @param null $key
	 * @param bool $default
	 *
	 * @return array|bool|mixed
	 *
	 * Get tutor course settings by course ID
	 *
	 * @since v.1.4.1
	 */
if ( ! function_exists( 'get_tutor_course_settings' ) ) {
	function get_tutor_course_settings( $course_id = 0, $key = null, $default = false ) {
		return tutor_utils()->get_course_settings( $course_id, $key, $default );
	}
}

	/**
	 * @param int $lesson_id
	 * @param null $key
	 * @param bool $default
	 *
	 * @return array|bool|mixed
	 *
	 * Get lesson content drip settings
	 */

if ( ! function_exists( 'get_item_content_drip_settings' ) ) {
	function get_item_content_drip_settings( $lesson_id = 0, $key = null, $default = false ) {
		return tutor_utils()->get_item_content_drip_settings( $lesson_id, $key, $default );
	}
}

	/**
	 * @param null $msg
	 * @param string $type
	 * @param bool $echo
	 *
	 * @return string
	 *
	 * Print Alert by tutor_alert()
	 *
	 * @since v.1.4.1
	 */
if ( ! function_exists( 'tutor_alert' ) ) {
	function tutor_alert( $msg = null, $type = 'warning', $echo = true ) {
		if ( ! $msg ) {

			if ( $type === 'any' ) {
				if ( ! $msg ) {
					$type = 'warning';
					$msg  = tutor_flash_get( $type );
				}
				if ( ! $msg ) {
					$type = 'danger';
					$msg  = tutor_flash_get( $type );
				}
				if ( ! $msg ) {
					$type = 'success';
					$msg  = tutor_flash_get( $type );
				}
			} else {
				$msg = tutor_flash_get( $type );
			}
		}
		if ( ! $msg ) {
			return $msg;
		}

		$html = '<div class="asas tutor-alert tutor-' . esc_attr( $type ) . '">
					<div class="tutor-alert-text">
						<span class="tutor-alert-icon tutor-fs-4 tutor-icon-circle-info tutor-mr-12"></span>
						<span>' . wp_kses( $msg, array( 'div', 'span' ) ) . '</span>
					</div>
				</div>';
		if ( $echo ) {
			echo tutor_kses_html( $html ); //phpcs:ignore
		}
		return $html;
	}
}


	/**
	 * @param bool $echo
	 *
	 * Simply call tutor_nonce_field() to generate nonce field
	 *
	 * @since v.1.4.2
	 */

if ( ! function_exists( 'tutor_nonce_field' ) ) {
	function tutor_nonce_field( $echo = true ) {
		wp_nonce_field( tutor()->nonce_action, tutor()->nonce, $echo );
	}
}

	/**
	 * @param null $key
	 * @param string $message
	 *
	 * Set Flash Message
	 */

if ( ! function_exists( 'tutor_flash_set' ) ) {
	function tutor_flash_set( $key = null, $message = '' ) {
		if ( ! $key ) {
			return;
		}
		// ensure session is started
		if ( session_status() !== PHP_SESSION_ACTIVE ) {
			session_start();
		}
		$_SESSION[ $key ] = $message;
	}
}

	/**
	 * @param null $key
	 *
	 * @return array|bool|mixed|null
	 *
	 * @since v.1.4.2
	 *
	 * Get flash message
	 */

if ( ! function_exists( 'tutor_flash_get' ) ) {
	function tutor_flash_get( $key = null ) {
		if ( $key ) {
			// ensure session is started
			if ( session_status() !== PHP_SESSION_ACTIVE ) {
				@session_start();
			}
			if ( empty( $_SESSION ) ) {
				return null;
			}
			$message = tutor_utils()->array_get( $key, $_SESSION );
			if ( $message ) {
				unset( $_SESSION[ $key ] );
			}
			return $message;
		}
		return $key;
	}
}

if ( ! function_exists( 'tutor_redirect_back' ) ) {
	/**
	 * @param null $url
	 *
	 * Redirect to back or a specific URL and terminate
	 *
	 * @since v.1.4.3
	 */
	function tutor_redirect_back( $url = null ) {
		if ( ! $url ) {
			$url = tutor_utils()->referer();
		}
		wp_safe_redirect( $url );
		exit();
	}
}

	/**
	 * @param string $action
	 * @param bool $echo
	 *
	 * @return string
	 *
	 * @since v.1.4.3
	 */

if ( ! function_exists( 'tutor_action_field' ) ) {
	function tutor_action_field( $action = '', $echo = true ) {
		$output = '';
		if ( $action ) {
			$output = '<input type="hidden" name="tutor_action" value="' . esc_attr( $action ) . '">';
		}

		if ( $echo ) {
			echo wp_kses(
				$output,
				array(
					'input' => array(
						'type'  => true,
						'name'  => true,
						'value' => true,
					),
				)
			);
		} else {
			return $output;
		}
	}
}


if ( ! function_exists( 'tutor_time' ) ) {
	/**
	 * Return current Time from WordPress time
	 *
	 * @return int|string
	 * @since v.1.4.3
	 */
	function tutor_time() {
		$gmt_offset = get_option( 'gmt_offset' );
		return time() + ( $gmt_offset * HOUR_IN_SECONDS );
	}
}

	/**
	 * Toggle maintenance mode for the site.
	 *
	 * Creates/deletes the maintenance file to enable/disable maintenance mode.
	 *
	 * @since v.1.4.6
	 *
	 * @global WP_Filesystem_Base $wp_filesystem Subclass
	 *
	 * @param bool $enable True to enable maintenance mode, false to disable.
	 */
if ( ! function_exists( 'tutor_maintenance_mode' ) ) {
	function tutor_maintenance_mode( $enable = false ) {
		$file = ABSPATH . '.tutor_maintenance';
		if ( $enable ) {
			// Create maintenance file to signal that we are upgrading
			$maintenance_string = '<?php $upgrading = ' . time() . '; ?>';

			if ( ! file_exists( $file ) ) {
				file_put_contents( $file, $maintenance_string );
			}
		} else {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
	}
}

	/**
	 * @return bool
	 *
	 * Check if the current page is course single page
	 *
	 * @since v.1.6.0
	 */

if ( ! function_exists( 'is_single_course' ) ) {
	function is_single_course( $check_spotlight = false ) {
		global $wp_query;
		$course_post_type = tutor()->course_post_type;

		$post_types = array( $course_post_type );
		if ( $check_spotlight ) {
			$post_types = array_merge(
				$post_types,
				array(
					'lesson',
					'tutor_quiz',
					'tutor_assignments',
					'tutor_zoom_meeting',
				)
			);
		}

		if ( is_single() && ! empty( $wp_query->query['post_type'] ) && in_array( $wp_query->query['post_type'], $post_types ) ) {
			return true;
		}
		return false;
	}
}

	/**
	 * Require wp_date form return js date format.
	 * this is helpful for date picker
	 *
	 * @return string
	 *
	 * @since 1.9.7
	 */
if ( ! function_exists( 'tutor_js_date_format_against_wp' ) ) {
	function tutor_js_date_format_against_wp() {
		$wp_date_format = get_option( 'date_format' );
		$default_format = 'Y-M-d';

		$formats = array(
			'Y-m-d'  => 'Y-M-d',
			'm/d/Y'  => 'M-d-Y',
			'd/m/Y'  => 'd-M-Y',
			'F j, Y' => 'MMMM d, yyyy',
			'j F Y'  => 'MMMM d, yyyy',
		);
		return isset( $formats[ $wp_date_format ] ) ? $formats[ $wp_date_format ] : $default_format;
	}
}

if ( ! function_exists( 'tutor_get_formated_date' ) ) {
	/**
	 * Convert date to desire format
	 *
	 * NOTE: mysql query use formated date from here
	 * that's why date_i18n need to be ignore
	 *
	 * @param string $require_format string If empty Y-m-d is used.
	 * @param string $user_date string Date.
	 *
	 * @return string ( date )
	 */
	function tutor_get_formated_date( string $require_format = '', string $user_date = '' ) {
		$require_format = $require_format ?: 'Y-m-d';

		$date = date_create( str_replace( '/', '-', $user_date ) );
		if ( is_a( $date, 'DateTime' ) ) {
			$formatted_date = date_format( $date, $require_format );
		} else {
			$formatted_date = gmdate( $require_format, strtotime( $user_date ) );
		}
		return $formatted_date;
	}
}

/**
 * Get translated date
 *
 * @since v2.0.2
 *
 * @param string $date  date in string from to translate & format.
 * @param string $format optional date format, default is wp date time format.
 *
 * @return string translated date
 */
if ( ! function_exists( 'tutor_i18n_get_formated_date' ) ) {
	function tutor_i18n_get_formated_date( string $date, string $format = '' ) {
		if ( '' === $format ) {
			$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		}
		return date_i18n( $format, strtotime( $date ) );
	}
}

if ( ! function_exists( '_tutor_search_by_title_only' ) ) {
	/**
	 * Search SQL filter for matching against post title only.
	 *
	 * @link    http://wordpress.stackexchange.com/a/11826/1685
	 *
	 * @param   string   $search
	 * @param   WP_Query $wp_query
	 */
	function _tutor_search_by_title_only( $search, $wp_query ) {
		if ( ! empty( $search ) && ! empty( $wp_query->query_vars['search_terms'] ) ) {
			global $wpdb;

			$q = $wp_query->query_vars;
			$n = ! empty( $q['exact'] ) ? '' : '%';

			$search = array();

			foreach ( (array) $q['search_terms'] as $term ) {
				$search[] = $wpdb->prepare( "$wpdb->posts.post_title LIKE %s", $n . $wpdb->esc_like( $term ) . $n );
			}

			if ( ! is_user_logged_in() ) {
				$search[] = "$wpdb->posts.post_password = ''";
			}

			$search = ' AND ' . implode( ' AND ', $search );
		}

		return $search;
	}
}

if ( ! function_exists( 'get_request' ) ) {
	/**
	 * Function to get_request
	 *
	 * @param  array $var .
	 * @return array
	 */
	function get_request( $var ) {
		return isset( $_REQUEST[ $var ] ) ? sanitize_text_field( $_REQUEST[ $var ] ) : false;

	}
}

if ( ! function_exists( 'tutor_kses_allowed_html' ) ) {
	function tutor_kses_allowed_html( $allowed_tags, $context ) {
		$tags = array( 'input', 'style', 'script', 'select', 'form', 'option', 'optgroup', 'iframe', 'bdi', 'source', 'a' );
		$atts = array( 'min', 'max', 'maxlength', 'type', 'method', 'enctype', 'action', 'selected', 'class', 'id', 'disabled', 'checked', 'readonly', 'name', 'aria-*', 'style', 'role', 'placeholder', 'value', 'data-*', 'src', 'width', 'height', 'frameborder', 'allow', 'fullscreen', 'title', 'multiple', 'tutor-hide-course-single-sidebar', 'href' );

		foreach ( $tags as $tag ) {
			$tag_attrs = array();

			foreach ( $atts as $att ) {
				$tag_attrs[ $att ] = true;
			}

			$allowed_tags[ $tag ] = $tag_attrs;
		}

		return $allowed_tags;
	}
}

if ( ! function_exists( 'tutor_kses_allowed_css' ) ) {
	function tutor_kses_allowed_css( $styles ) {
		$styles[] = 'display';
		$styles[] = '--progress-value';
		return $styles;
	}
}

if ( ! function_exists( 'tutor_kses_html' ) ) {
	function tutor_kses_html( $content ) {

		return $content;
		add_filter( 'wp_kses_allowed_html', 'tutor_kses_allowed_html', 10, 2 );
		add_filter( 'safe_style_css', 'tutor_kses_allowed_css' );

		$content = preg_replace( '/<!--(.|\s)*?-->/', '', $content );
		$content = wp_kses_post( $content );
		$content = str_replace( '&amp;', '&', $content );

		remove_filter( 'safe_style_css', 'tutor_kses_allowed_css' );
		remove_filter( 'wp_kses_allowed_html', 'tutor_kses_allowed_html' );

		return $content;
	}
}

/**
 * @return array
 *
 * Get all Withdraw Methods available on this system
 *
 * @since v.1.5.7
 */
if ( ! function_exists( 'get_tutor_all_withdrawal_methods' ) ) {
	function get_tutor_all_withdrawal_methods() {
		return apply_filters( 'tutor_withdrawal_methods_all', array() );
	}
}


if ( ! function_exists( 'tutor_log' ) ) {
	/**
	 * Logging data.
	 *
	 * @since 1.0.0
	 * @since 3.0.0 exception logging support added.
	 *
	 * @return void
	 */
	function tutor_log() {
		$arg_list = func_get_args();

		foreach ( $arg_list as $data ) {
			ob_start();

			if ( $data instanceof Exception ) {
				var_dump( $data->getMessage() );
				var_dump( $data->getTraceAsString() );
			} else {
				var_dump( $data );
			}

			error_log( ob_get_clean() );
		}
	}
}

if ( ! function_exists( 'tutor_wc_price_currency_format' ) ) {
	function tutor_wc_price_currency_format( $amount ) {

		$symbol   = get_woocommerce_currency_symbol();
		$position = get_option( 'woocommerce_currency_pos', 'left' );

		switch ( $position ) {
			case 'left':
				$amount = $symbol . $amount;
				break;
			case 'left_space':
				$amount = $symbol . ' ' . $amount;
				break;

			case 'right':
				$amount = $amount . $symbol;
				break;
			case 'right_space':
				$amount = $amount . ' ' . $symbol;
				break;

			default:
				$amount = $symbol . $amount;
				break;
		}

		return $amount;
	}
}

if ( ! function_exists( 'tutor_meta_box_wrapper' ) ) {
	/**
	 * Tutor meta box wrapper
	 *
	 * @since v2.0.2
	 *
	 * @param string $id  id of meta box.
	 * @param string $title  meta box title.
	 * @param mixed  $callback callback function that meta box will call.
	 * @param string $screen  which screen meta box should appear.
	 * @param string $context optional param. Where meta box should appear.
	 * @param string $priority optional.
	 * @param string $custom_class optional. If provide it will add this class along
	 * with div id.
	 *
	 * @return void  if class provided then filter hook will return class.
	 */
	function tutor_meta_box_wrapper(
		$id,
		$title,
		$callback,
		$screen,
		$context = 'advanced',
		$priority = 'default',
		$custom_class = ''
	) {
		add_meta_box(
			$id,
			$title,
			$callback,
			$screen,
			$context,
			$priority
		);
		if ( '' !== $custom_class ) {
			$post_type = tutor()->course_post_type;
			add_filter(
				"postbox_classes_{$post_type}_{$id}",
				function( $classes ) use ( $custom_class ) {
					if ( ! in_array( $custom_class, $classes ) ) {
						$classes[] = $custom_class;
					}
					return $classes;
				}
			);
		}
	}
}

if ( ! function_exists( 'tutor_closeable_alert_msg' ) ) {
	/**
	 * Create a close-able alert message
	 *
	 * @since 2.1.9
	 *
	 * @param string $message alert message.
	 * @param string $alert alert key like: success, warning, danger, etc.
	 * @param array  $allowed_tags allowed tags to use with WP_KSES.
	 *
	 * @return void
	 */
	function tutor_closeable_alert_msg( string $message, string $alert = 'success', $allowed_tags = array() ) {
		?>
		<div class="tutor-alert tutor-<?php echo esc_attr( $alert ); ?> tutor-mb-12 tutor-alert tutor-success tutor-mb-12 tutor-d-flex tutor-align-center tutor-justify-between">
			<span>
				<?php echo is_array( $allowed_tags ) && count( $allowed_tags ) ? wp_kses( $message, $allowed_tags ) : esc_html( $message ); ?>
			</span>
			<span class="tutor-icon-times" area-hidden="true" onclick="this.closest('div').remove()" style="cursor: pointer;"></span>
		</div>
		<?php
	}
}

if ( ! function_exists( 'tutor_set_flash_message' ) ) {
	/**
	 * Utility API Set flash message to show somewhere
	 *
	 * It will call set_cache method of FlashMessage class to set cache
	 *
	 * @param mixed  $message message to show.
	 * @param string $alert alert type as FlashMessage::$alert_types.
	 *
	 * @return void
	 */
	function tutor_set_flash_message( $message = '', $alert = 'success' ) {
		$flash_msg = new FlashMessage( $message, $alert );
		$flash_msg->set_cache();
	}
}


if ( ! function_exists( 'tutor_snackbar' ) ) {
	/**
	 * Reuseable snackbar to show on the frontend
	 *
	 * Create a snackbar based on title, action buttons
	 *
	 * @since 2.2.0
	 *
	 * @param string $title title to show.
	 * @param array  $action_buttons 2 dimensional array of action buttons to show.
	 * Supported attrs: [ [title => title, id => '', class => '' url => '', target => ''] ].
	 * @param string $title_icon_class title icon to show before title.
	 *
	 * @return void
	 */
	function tutor_snackbar( string $title, array $action_buttons = array(), $title_icon_class = '' ) {
		?>
		<div id="tutor-reuseable-snackbar" class="tutor-snackbar-wrapper">
			<div class="tutor-snackbar">
				<p>
					<?php if ( ! empty( $title_icon_class ) ) : ?>
						<i class="tutor-snackbar-title-icon <?php echo esc_attr( $title_icon_class ); ?>"></i>
					<?php endif; ?>
					<?php echo esc_html( $title ); ?>
				</p>
				<div>
					<?php foreach ( $action_buttons as $attr => $button ) : ?>
						<a
							<?php foreach ( $button as $attr => $value ) : ?>
								<?php if ( ! empty( $value ) ) : ?>
									<?php echo esc_attr( $attr ) . '="' . esc_attr( $value ) . '" '; ?>
								<?php endif; ?>
							<?php endforeach; ?>
						>
							<?php echo esc_html( isset( $button['title'] ) ? $button['title'] : '' ); ?>
						</a>
					<?php endforeach; ?>
					<span class="tutor-icon-times" area-hidden="true" onclick="this.closest('#tutor-reuseable-snackbar').remove()" style="cursor: pointer;"></span>
				</div>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'tutor_is_rest' ) ) {
	/**
	 * Checks if the current request is a WP REST API request.
	 *
	 * @since 2.6.0
	 *
	 * Case #1: After WP_REST_Request initialisation
	 * Case #2: Support "plain" permalink settings and check if `rest_route` starts with `/`
	 * Case #3: It can happen that WP_Rewrite is not yet initialized,
	 *          so do this (wp-settings.php)
	 * Case #4: URL Path begins with wp-json/ (your REST prefix)
	 *          Also supports WP installations in subfolders
	 *
	 * @see https://wordpress.stackexchange.com/questions/221202/does-something-like-is-rest-exist
	 * @returns boolean
	 */
	function tutor_is_rest() {
		$rest_route = Input::get( 'rest_route' );
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST || $rest_route && strpos( $rest_route, '/', 0 ) === 0 ) {
			return true;
		}

		// (#3)
		global $wp_rewrite;
		if ( null === $wp_rewrite ) {
			$wp_rewrite = new WP_Rewrite();
		}

		// (#4)
		$rest_url    = wp_parse_url( trailingslashit( rest_url() ) );
		$current_url = wp_parse_url( add_query_arg( array() ) );
		return strpos( $current_url['path'] ?? '/', $rest_url['path'], 0 ) === 0;
	}
}

if ( ! function_exists( 'tutor_getallheaders' ) ) {
	/**
	 * Wrapper of PHP getallheaders with a fallback if getallheaders not available
	 *
	 * @since 2.6.0
	 *
	 * @see https://www.php.net/manual/en/function.getallheaders.php
	 *
	 * @return array of headers
	 */
	function tutor_getallheaders() {
		$headers = array();
		if ( function_exists( 'getallheaders' ) ) {
			$headers = getallheaders();
		}

		if ( ! $headers ) {
			foreach ( $_SERVER as $name => $value ) {
				if ( substr( $name, 0, 5 ) == 'HTTP_' ) {
					$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
				}
			}
		}

		return $headers;
	}
}

if ( ! function_exists( 'tutor_entry_box_buttons' ) ) {
	/**
	 * Tutor conditional buttons for the enrollment box
	 *
	 * @since 2.6.0
	 *
	 * @param int $course_id course id.
	 * @param int $user_id user id.
	 *
	 * @return object
	 */
	function tutor_entry_box_buttons( int $course_id = 0, int $user_id = 0 ) {
		$conditional_buttons = (object) array(
			'show_enroll_btn'              => false,
			'show_add_to_cart_btn'         => false,
			'show_start_learning_btn'      => false,
			'show_continue_learning_btn'   => false,
			'show_complete_course_btn'     => false,
			'show_retake_course_btn'       => false,
			'show_certificate_view_btn'    => false,
			'show_course_fully_booked_btn' => false,
		);

		$course_id = tutor_utils()->get_post_id( $course_id );
		$user_id   = tutor_utils()->get_user_id( $user_id );

		$has_course_access = tutor_utils()->has_user_course_content_access( $user_id, $course_id );

		$is_public_course = get_post_meta( $course_id, '_tutor_is_public_course', true );

		$is_enabled_retake = tutor_utils()->get_option( 'course_retake_feature' );

		$is_enrolled = tutor_utils()->is_enrolled( $course_id, $user_id );

		if ( 'yes' === $is_public_course ) {
			$conditional_buttons->show_start_learning_btn = true;
		} else {
			// Admin & instructor can manage posts.
			if ( $is_enrolled || $has_course_access ) {
				$can_complete_course = CourseModel::can_complete_course( $course_id, $user_id );
				$is_completed_course = tutor_utils()->is_completed_course( $course_id, $user_id );
				$course_progress     = (int) tutor_utils()->get_course_completed_percent( $course_id, $user_id );

				if ( $course_progress > 0 && $course_progress < 100 ) {
					$conditional_buttons->show_continue_learning_btn = true;
				}

				if ( 0 === $course_progress ) {
					$conditional_buttons->show_start_learning_btn = true;
				}

				if ( $can_complete_course ) {
					$conditional_buttons->show_complete_course_btn = true;
				}

				if ( $is_completed_course ) {
					$conditional_buttons->show_certificate_view_btn = true;
				}

				if ( $is_enabled_retake && $is_completed_course ) {
					$conditional_buttons->show_retake_course_btn = true;
				}
			} else {
				$is_paid_course = tutor_utils()->is_course_purchasable( $course_id );
				if ( $is_paid_course ) {
					$conditional_buttons->show_add_to_cart_btn = true;
				} else {
					$conditional_buttons->show_enroll_btn = true;
				}
			}
		}

		if ( ! $is_public_course && ! ( $is_enrolled || $has_course_access ) ) {
			$is_fully_booked = tutor_utils()->is_course_fully_booked( $course_id );
			if ( $is_fully_booked ) {
				$conditional_buttons->show_course_fully_booked_btn = true;
			}
		}

		return apply_filters( 'tutor_enrollment_buttons', $conditional_buttons );
	}
}

if ( ! function_exists( 'tutor_global_timezone_lists' ) ) {
	/**
	 * Get list of global timezones
	 *
	 * @return array
	 */
	function tutor_global_timezone_lists() {
		return array(
			'Pacific/Midway'                 => '(GMT-11:00) Midway Island, Samoa ',
			'Pacific/Pago_Pago'              => '(GMT-11:00) Pago Pago ',
			'Pacific/Honolulu'               => '(GMT-10:00) Hawaii ',
			'America/Anchorage'              => '(GMT-8:00) Alaska ',
			'America/Vancouver'              => '(GMT-7:00) Vancouver ',
			'America/Los_Angeles'            => '(GMT-7:00) Pacific Time (US and Canada) ',
			'America/Tijuana'                => '(GMT-7:00) Tijuana ',
			'America/Phoenix'                => '(GMT-7:00) Arizona ',
			'America/Edmonton'               => '(GMT-6:00) Edmonton ',
			'America/Denver'                 => '(GMT-6:00) Mountain Time (US and Canada) ',
			'America/Mazatlan'               => '(GMT-6:00) Mazatlan ',
			'America/Regina'                 => '(GMT-6:00) Saskatchewan ',
			'America/Guatemala'              => '(GMT-6:00) Guatemala ',
			'America/El_Salvador'            => '(GMT-6:00) El Salvador ',
			'America/Managua'                => '(GMT-6:00) Managua ',
			'America/Costa_Rica'             => '(GMT-6:00) Costa Rica ',
			'America/Tegucigalpa'            => '(GMT-6:00) Tegucigalpa ',
			'America/Winnipeg'               => '(GMT-5:00) Winnipeg ',
			'America/Chicago'                => '(GMT-5:00) Central Time (US and Canada) ',
			'America/Mexico_City'            => '(GMT-5:00) Mexico City ',
			'America/Panama'                 => '(GMT-5:00) Panama ',
			'America/Bogota'                 => '(GMT-5:00) Bogota ',
			'America/Lima'                   => '(GMT-5:00) Lima ',
			'America/Caracas'                => '(GMT-4:30) Caracas ',
			'America/Montreal'               => '(GMT-4:00) Montreal ',
			'America/New_York'               => '(GMT-4:00) Eastern Time (US and Canada) ',
			'America/Indianapolis'           => '(GMT-4:00) Indiana (East) ',
			'America/Puerto_Rico'            => '(GMT-4:00) Puerto Rico ',
			'America/Santiago'               => '(GMT-4:00) Santiago ',
			'America/Halifax'                => '(GMT-3:00) Halifax ',
			'America/Montevideo'             => '(GMT-3:00) Montevideo ',
			'America/Araguaina'              => '(GMT-3:00) Brasilia ',
			'America/Argentina/Buenos_Aires' => '(GMT-3:00) Buenos Aires, Georgetown ',
			'America/Sao_Paulo'              => '(GMT-3:00) Sao Paulo ',
			'Canada/Atlantic'                => '(GMT-3:00) Atlantic Time (Canada) ',
			'America/St_Johns'               => '(GMT-2:30) Newfoundland and Labrador ',
			'America/Godthab'                => '(GMT-2:00) Greenland ',
			'Atlantic/Cape_Verde'            => '(GMT-1:00) Cape Verde Islands ',
			'Atlantic/Azores'                => '(GMT+0:00) Azores ',
			'UTC'                            => '(GMT+0:00) Universal Time UTC ',
			'Etc/Greenwich'                  => '(GMT+0:00) Greenwich Mean Time ',
			'Atlantic/Reykjavik'             => '(GMT+0:00) Reykjavik ',
			'Africa/Nouakchott'              => '(GMT+0:00) Nouakchott ',
			'Europe/Dublin'                  => '(GMT+1:00) Dublin ',
			'Europe/London'                  => '(GMT+1:00) London ',
			'Europe/Lisbon'                  => '(GMT+1:00) Lisbon ',
			'Africa/Casablanca'              => '(GMT+1:00) Casablanca ',
			'Africa/Bangui'                  => '(GMT+1:00) West Central Africa ',
			'Africa/Algiers'                 => '(GMT+1:00) Algiers ',
			'Africa/Tunis'                   => '(GMT+1:00) Tunis ',
			'Europe/Belgrade'                => '(GMT+2:00) Belgrade, Bratislava, Ljubljana ',
			'CET'                            => '(GMT+2:00) Sarajevo, Skopje, Zagreb ',
			'Europe/Oslo'                    => '(GMT+2:00) Oslo ',
			'Europe/Copenhagen'              => '(GMT+2:00) Copenhagen ',
			'Europe/Brussels'                => '(GMT+2:00) Brussels ',
			'Europe/Berlin'                  => '(GMT+2:00) Amsterdam, Berlin, Rome, Stockholm, Vienna ',
			'Europe/Amsterdam'               => '(GMT+2:00) Amsterdam ',
			'Europe/Rome'                    => '(GMT+2:00) Rome ',
			'Europe/Stockholm'               => '(GMT+2:00) Stockholm ',
			'Europe/Vienna'                  => '(GMT+2:00) Vienna ',
			'Europe/Luxembourg'              => '(GMT+2:00) Luxembourg ',
			'Europe/Paris'                   => '(GMT+2:00) Paris ',
			'Europe/Zurich'                  => '(GMT+2:00) Zurich ',
			'Europe/Madrid'                  => '(GMT+2:00) Madrid ',
			'Africa/Harare'                  => '(GMT+2:00) Harare, Pretoria ',
			'Europe/Warsaw'                  => '(GMT+2:00) Warsaw ',
			'Europe/Prague'                  => '(GMT+2:00) Prague Bratislava ',
			'Europe/Budapest'                => '(GMT+2:00) Budapest ',
			'Africa/Tripoli'                 => '(GMT+2:00) Tripoli ',
			'Africa/Cairo'                   => '(GMT+2:00) Cairo ',
			'Africa/Johannesburg'            => '(GMT+2:00) Johannesburg ',
			'Europe/Helsinki'                => '(GMT+3:00) Helsinki ',
			'Africa/Nairobi'                 => '(GMT+3:00) Nairobi ',
			'Europe/Sofia'                   => '(GMT+3:00) Sofia ',
			'Europe/Istanbul'                => '(GMT+3:00) Istanbul ',
			'Europe/Athens'                  => '(GMT+3:00) Athens ',
			'Europe/Bucharest'               => '(GMT+3:00) Bucharest ',
			'Asia/Nicosia'                   => '(GMT+3:00) Nicosia ',
			'Asia/Beirut'                    => '(GMT+3:00) Beirut ',
			'Asia/Damascus'                  => '(GMT+3:00) Damascus ',
			'Asia/Jerusalem'                 => '(GMT+3:00) Jerusalem ',
			'Asia/Amman'                     => '(GMT+3:00) Amman ',
			'Europe/Moscow'                  => '(GMT+3:00) Moscow ',
			'Asia/Baghdad'                   => '(GMT+3:00) Baghdad ',
			'Asia/Kuwait'                    => '(GMT+3:00) Kuwait ',
			'Asia/Riyadh'                    => '(GMT+3:00) Riyadh ',
			'Asia/Bahrain'                   => '(GMT+3:00) Bahrain ',
			'Asia/Qatar'                     => '(GMT+3:00) Qatar ',
			'Asia/Aden'                      => '(GMT+3:00) Aden ',
			'Africa/Khartoum'                => '(GMT+3:00) Khartoum ',
			'Africa/Djibouti'                => '(GMT+3:00) Djibouti ',
			'Africa/Mogadishu'               => '(GMT+3:00) Mogadishu ',
			'Europe/Kiev'                    => '(GMT+3:00) Kiev ',
			'Asia/Dubai'                     => '(GMT+4:00) Dubai ',
			'Asia/Muscat'                    => '(GMT+4:00) Muscat ',
			'Asia/Tehran'                    => '(GMT+4:30) Tehran ',
			'Asia/Kabul'                     => '(GMT+4:30) Kabul ',
			'Asia/Baku'                      => '(GMT+5:00) Baku, Tbilisi, Yerevan ',
			'Asia/Yekaterinburg'             => '(GMT+5:00) Yekaterinburg ',
			'Asia/Tashkent'                  => '(GMT+5:00) Tashkent ',
			'Asia/Karachi'                   => '(GMT+5:00) Islamabad, Karachi ',
			'Asia/Calcutta'                  => '(GMT+5:30) India ',
			'Asia/Kolkata'                   => '(GMT+5:30) Mumbai, Kolkata, New Delhi ',
			'Asia/Kathmandu'                 => '(GMT+5:45) Kathmandu ',
			'Asia/Novosibirsk'               => '(GMT+6:00) Novosibirsk ',
			'Asia/Almaty'                    => '(GMT+6:00) Almaty ',
			'Asia/Dacca'                     => '(GMT+6:00) Dacca ',
			'Asia/Dhaka'                     => '(GMT+6:00) Astana, Dhaka ',
			'Asia/Krasnoyarsk'               => '(GMT+7:00) Krasnoyarsk ',
			'Asia/Bangkok'                   => '(GMT+7:00) Bangkok ',
			'Asia/Saigon'                    => '(GMT+7:00) Vietnam ',
			'Asia/Jakarta'                   => '(GMT+7:00) Jakarta ',
			'Asia/Irkutsk'                   => '(GMT+8:00) Irkutsk, Ulaanbaatar ',
			'Asia/Shanghai'                  => '(GMT+8:00) Beijing, Shanghai ',
			'Asia/Hong_Kong'                 => '(GMT+8:00) Hong Kong ',
			'Asia/Taipei'                    => '(GMT+8:00) Taipei ',
			'Asia/Kuala_Lumpur'              => '(GMT+8:00) Kuala Lumpur ',
			'Asia/Singapore'                 => '(GMT+8:00) Singapore ',
			'Australia/Perth'                => '(GMT+8:00) Perth ',
			'Asia/Yakutsk'                   => '(GMT+9:00) Yakutsk ',
			'Asia/Seoul'                     => '(GMT+9:00) Seoul ',
			'Asia/Tokyo'                     => '(GMT+9:00) Osaka, Sapporo, Tokyo ',
			'Australia/Darwin'               => '(GMT+9:30) Darwin ',
			'Australia/Adelaide'             => '(GMT+9:30) Adelaide ',
			'Asia/Vladivostok'               => '(GMT+10:00) Vladivostok ',
			'Pacific/Port_Moresby'           => '(GMT+10:00) Guam, Port Moresby ',
			'Australia/Brisbane'             => '(GMT+10:00) Brisbane ',
			'Australia/Sydney'               => '(GMT+10:00) Canberra, Melbourne, Sydney ',
			'Australia/Hobart'               => '(GMT+10:00) Hobart ',
			'Asia/Magadan'                   => '(GMT+10:00) Magadan ',
			'SST'                            => '(GMT+11:00) Solomon Islands ',
			'Pacific/Noumea'                 => '(GMT+11:00) New Caledonia ',
			'Asia/Kamchatka'                 => '(GMT+12:00) Kamchatka ',
			'Pacific/Fiji'                   => '(GMT+12:00) Fiji Islands, Marshall Islands ',
			'Pacific/Auckland'               => '(GMT+12:00) Auckland, Wellington',
		);
	}

	if ( ! function_exists( 'tutor_get_all_active_payment_gateways' ) ) {
		/**
		 * Get all active payment gateways including manual & automate
		 *
		 * @since 3.0.0
		 *
		 * @return array
		 */
		function tutor_get_all_active_payment_gateways() {
			$payment_settings = Settings::get_payment_settings();
			$payment_methods  = ! empty( $payment_settings['payment_methods'] ) ? $payment_settings['payment_methods'] : array();

			$active_gateways = array();

			foreach ( $payment_methods as $method ) {
				$is_active = $method['is_active'] ?? false;
				$is_manual = $method['is_manual'] ?? false;
				if ( ! $is_active ) {
					continue;
				}

				$fields = $method['fields'];
				unset( $method['fields'] );

				$gateway = $method;
				if ( $is_manual ) {
					foreach ( $fields as $field ) {
						if ( 'payment_instructions' === $field['name'] || 'additional_details' === $field['name'] ) {
							$gateway[ $field['name'] ] = $field['value'];
						}
					}
				}

				$active_gateways[] = $gateway;
			}

			return $active_gateways;
		}
	}

	if ( ! function_exists( 'tutor_get_supported_payment_gateways' ) ) {
		/**
		 * Get all supported gateways
		 *
		 * This function will return only subscription supported gateways if
		 * plan id provided.
		 *
		 * @since 3.0.0
		 *
		 * @param int $plan_id Plan id.
		 *
		 * @return array
		 */
		function tutor_get_supported_payment_gateways( int $plan_id = 0 ) {
			$payment_gateways = tutor_get_all_active_payment_gateways();

			$supported_gateways = array();
			foreach ( $payment_gateways as $gateway ) {
				$support_subscription = $gateway['support_subscription'] ?? false;

				if ( $plan_id && ! $support_subscription ) {
					continue;
				}

				$supported_gateways[] = array(
					'name'                 => $gateway['name'] ?? '',
					'label'                => $gateway['label'] ?? '',
					'icon'                 => $gateway['icon'] ?? '',
					'support_subscription' => $gateway['support_subscription'] ?? '',
					'is_manual'            => $gateway['is_manual'] ?? '',
					'additional_details'   => $gateway['additional_details'] ?? '',
					'payment_instructions' => $gateway['payment_instructions'] ?? '',
				);
			}

			return $supported_gateways;
		}
	}

	if ( ! function_exists( 'tutor_get_manual_payment_gateways' ) ) {
		/**
		 * Get manual payment gateways
		 *
		 * @since 3.0.0
		 *
		 * @return array
		 */
		function tutor_get_manual_payment_gateways() {
			$payments = tutor_utils()->get_option( 'payment_settings' );
			$payments = json_decode( stripslashes( $payments ) );

			$manual_methods = array();

			if ( $payments ) {
				foreach ( $payments->payment_methods as $method ) {
					if ( isset( $method->is_manual ) && 1 === (int) $method->is_manual ) {
						$manual_methods[] = $method;
					}
				}
			}

			return apply_filters( 'tutor_manual_payment_methods', $manual_methods );
		}
	}
}

if ( ! function_exists( 'tutor_get_course_formatted_price_html' ) ) {
	/**
	 * Get course formatted price
	 * Only for monetized by tutor.
	 *
	 * @since 3.0.0
	 *
	 * @param int     $course_id Course price.
	 * @param boolean $echo Whether to echo content.
	 *
	 * @return string|void
	 */
	function tutor_get_course_formatted_price_html( $course_id, $echo = true ) {
		$price_data = tutor_utils()->get_raw_course_price( $course_id );

		if ( ! $price_data->regular_price ) {
			return;
		}
		ob_start();
		?>
			<div class="list-item-price tutor-item-price">
				<?php if ( $price_data->sale_price ) : ?>
					<span><?php tutor_print_formatted_price( $price_data->display_price ); ?></span>
					<del><?php tutor_print_formatted_price( $price_data->regular_price ); ?></del>
				<?php else : ?>
					<span><?php tutor_print_formatted_price( $price_data->display_price ); ?></span>
				<?php endif; ?>
			</div>
			<?php if ( $price_data->show_price_with_tax ) : ?>
			<div class="tutor-course-price-tax tutor-fs-8 tutor-fw-medium tutor-color-muted"><?php esc_html_e( 'Incl. tax', 'tutor' ); ?></div>
			<?php endif; ?>
		<?php
		$content = apply_filters( 'tutor_course_formatted_price', ob_get_clean() );
		if ( $echo ) {
			echo $content; // PHPCS:ignore
		} else {
			return $content;
		}
	}
}

if ( ! function_exists( 'tutor_get_formatted_price' ) ) {
	/**
	 * Get course formatted price
	 *
	 * Formatting as per ecommerce price settings
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $price Raw price.
	 *
	 * @return string|void
	 */
	function tutor_get_formatted_price( $price ) {
		$price = floatval( Input::sanitize( $price ) );

		$currency_symbol    = Settings::get_currency_symbol_by_code( tutor_utils()->get_option( OptionKeys::CURRENCY_CODE, 'USD' ) );
		$currency_position  = tutor_utils()->get_option( OptionKeys::CURRENCY_POSITION, 'left' );
		$thousand_separator = tutor_utils()->get_option( OptionKeys::THOUSAND_SEPARATOR, ',' );
		$decimal_separator  = tutor_utils()->get_option( OptionKeys::DECIMAL_SEPARATOR, '.' );
		$no_of_decimal      = tutor_utils()->get_option( OptionKeys::NUMBER_OF_DECIMALS, '2' );

		$price = number_format( $price, $no_of_decimal, $decimal_separator, $thousand_separator );
		$price = 'left' === $currency_position ? $currency_symbol . $price : $price . $currency_symbol;

		return $price;
	}
}

if ( ! function_exists( 'tutor_print_formatted_price' ) ) {
	/**
	 * A clone copy of `tutor_get_formatted_price` helper
	 * To print formated price with output scaping.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $price price.
	 *
	 * @return void
	 */
	function tutor_print_formatted_price( $price ) {
		echo esc_html( tutor_get_formatted_price( $price ) );
	}
}

if ( ! function_exists( 'tutor_get_locale_price' ) ) {
	/**
	 * Get price as per locale format
	 *
	 * For locale settings currency code will be used
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $price Raw price.
	 *
	 * @return mixed raw price.
	 */
	function tutor_get_locale_price( $price ) {
		// TODO: implement price formation.
		return $price;
	}
}

if ( ! function_exists( 'tutor_is_json' ) ) {
	/**
	 * Check a string is valid JSON.
	 *
	 * @param string $string string.
	 *
	 * @return boolean
	 */
	function tutor_is_json( $string ) {
		json_decode( $string );
		return json_last_error() === JSON_ERROR_NONE;
	}
}

if ( ! function_exists( 'tutor_is_dev_mode' ) ) {
	/**
	 * Check tutor is in development mode or not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if the current environment is local, false otherwise.
	 */
	function tutor_is_dev_mode() {
		return defined( 'TUTOR_DEV_MODE' ) && TUTOR_DEV_MODE;
	}
}
