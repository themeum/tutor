<?php
/**
 * Tutor general functions
 *
 * @package TutorFunctions
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

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

/**
 * Convert date to desire format
 *
 * NOTE: mysql query use formated date from here
 * that's why date_i18n need to be ignore
 *
 * @param $format string
 *
 * @param $date string
 *
 * @return string ( date )
*/
if ( ! function_exists( 'tutor_get_formated_date' ) ) {
	function tutor_get_formated_date( string $require_format, string $user_date ) {
		$date = date_create( str_replace( '/', '-', $user_date ) );
		if ( is_a( $date, 'DateTime' ) ) {
			$formatted_date = date_format( $date, $require_format );
		} else {
			$formatted_date = date( $require_format, strtotime( $user_date ) );
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
	function tutor_log() {
		$arg_list = func_get_args();

		foreach ( $arg_list as $data ) {
			ob_start();
			var_dump( $data );
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

