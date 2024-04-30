<?php
/**
 * Manage short codes
 *
 * @package Tutor\ShortCode
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Short code class
 *
 * @since 1.0.0
 */
class Shortcode {

	/**
	 * Instructor page design layouts
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $instructor_layout = array(
		'default',
		'cover',
		'minimal',
		'portrait-horizontal',
		'minimal-horizontal',
	);

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_shortcode( 'tutor_student_registration_form', array( $this, 'student_registration_form' ) );
		add_shortcode( 'tutor_dashboard', array( $this, 'tutor_dashboard' ) );
		add_shortcode( 'tutor_instructor_registration_form', array( $this, 'instructor_registration_form' ) );
		add_shortcode( 'tutor_course', array( $this, 'tutor_course' ) );

		add_shortcode( 'tutor_instructor_list', array( $this, 'tutor_instructor_list' ) );
		add_action( 'wp_ajax_load_filtered_instructor', array( $this, 'load_filtered_instructor' ) );
		add_action( 'wp_ajax_nopriv_load_filtered_instructor', array( $this, 'load_filtered_instructor' ) );

		/**
		 * Load more categories
		 *
		 * @since 2.0.0
		 */
		add_action( 'wp_ajax_show_more', array( $this, 'show_more' ) );
		add_action( 'wp_ajax_nopriv_show_more', array( $this, 'show_more' ) );
	}

	/**
	 *  Instructor Registration Shortcode
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function student_registration_form() {
		ob_start();
		if ( is_user_logged_in() ) {
			tutor_load_template( 'dashboard.logged-in' );
		} else {
			tutor_load_template( 'dashboard.registration' );
		}
		return apply_filters( 'tutor/student/register', ob_get_clean() );
	}

	/**
	 * Tutor Dashboard for students
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function tutor_dashboard() {
		global $wp_query;

		ob_start();
		if ( is_user_logged_in() ) {
			/**
			 * Added isset() Condition to avoid infinite loop since v.1.5.4
			 * This has cause error by others plugin, Such AS SEO
			 */

			if ( ! isset( $wp_query->query_vars['tutor_dashboard_page'] ) ) {
				tutor_load_template( 'dashboard', array( 'is_shortcode' => true ) );
			}
		} else {
			/**
			 * If user not logged in show login form instead of
			 * popup sign-in button
			 *
			 * @since 2.1.3
			 */
			$login_url = tutor_utils()->get_option( 'enable_tutor_native_login', null, true, true ) ? '' : wp_login_url( tutor()->current_url );
			echo sprintf( __( 'Please %1$sSign-In%2$s to view this page', 'tutor' ), '<a data-login_url="' . esc_url( $login_url ) . '" href="#" class="tutor-open-login-modal">', '</a>' );//phpcs:ignore
		}
		return apply_filters( 'tutor_dashboard/index', ob_get_clean() );
	}

	/**
	 * Instructor Registration Shortcode
	 *
	 * @since v.1.0.0
	 *
	 * @return mixed
	 */
	public function instructor_registration_form() {
		ob_start();
		if ( is_user_logged_in() ) {
			tutor_load_template( 'dashboard.instructor.logged-in' );
		} else {
			tutor_load_template( 'dashboard.instructor.registration' );
		}
		return apply_filters( 'tutor_dashboard/student/index', ob_get_clean() );
	}

	/**
	 * Short code for getting course
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $atts attributes.
	 *
	 * @return string
	 */
	public function tutor_course( $atts ) {
		$a = shortcode_atts(
			array(
				'post_type'   => apply_filters( 'tutor_course_archive_post_types', array( tutor()->course_post_type ) ),
				'post_status' => 'publish',

				'id'          => '',
				'exclude_ids' => '',
				'category'    => '',

				'orderby'     => 'ID',
				'order'       => 'DESC',
				'count'       => tutils()->get_option( 'courses_per_page', 12 ),
				'paged'       => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
			),
			$atts
		);

		if ( ! empty( $a['id'] ) ) {
			$ids           = (array) explode( ',', $a['id'] );
			$a['post__in'] = $ids;
		}

		if ( ! empty( $a['exclude_ids'] ) ) {
			$exclude_ids       = (array) explode( ',', $a['exclude_ids'] );
			$a['post__not_in'] = $exclude_ids;
		}
		if ( ! empty( $a['category'] ) ) {
			$category = (array) explode( ',', $a['category'] );

			$a['tax_query'] = array();

			$category_ids = array_filter(
				$category,
				function( $id ) {
					return is_numeric( $id );
				}
			);

			$category_names = array_filter(
				$category,
				function( $id ) {
					return ! is_numeric( $id );
				}
			);

			if ( ! empty( $category_ids ) ) {
				$a['tax_query'] = array(
					array(
						'taxonomy' => 'course-category',
						'field'    => 'term_id',
						'terms'    => $category_ids,
						'operator' => 'IN',
					),
				);
			}

			if ( ! empty( $category_names ) ) {
				$a['tax_query'] = array(
					array(
						'taxonomy' => 'course-category',
						'field'    => 'name',
						'terms'    => $category_names,
						'operator' => 'IN',
					),
				);
			}
		}
		$a['posts_per_page'] = (int) $a['count'];

		wp_reset_query();
		$the_query = new \WP_Query( $a );

		/**
		 * Pagination & course filter handle from query param on page load (without ajax)
		 *
		 * @since 2.4.0
		 */
		$get = Input::has( 'course_filter' ) ? Input::sanitize_array( $_GET ) : array();
		if ( Input::has( 'course_filter' ) ) {
			$filter    = ( new \Tutor\Course_Filter( false ) )->load_listing( $get, true );
			$the_query = new \WP_Query( $filter );
		}

		// Load the renderer now.
		ob_start();

		if ( $the_query->have_posts() ) {
			tutor_load_template(
				'archive-course-init',
				array(
					'course_filter'     => isset( $atts['course_filter'] ) && 'on' === $atts['course_filter'],
					'supported_filters' => tutor_utils()->get_option( 'supported_course_filters', array() ),
					'loop_content_only' => false,
					'column_per_row'    => isset( $atts['column_per_row'] ) ? $atts['column_per_row'] : null,
					'course_per_page'   => $a['posts_per_page'],
					'show_pagination'   => isset( $atts['show_pagination'] ) && 'on' === $atts['show_pagination'],
					'the_query'         => $the_query,
					'current_page'      => isset( $get['current_page'] ) ? (int) $get['current_page'] : 1,
				)
			);
		} else {
			tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() );
		}

		$output = ob_get_clean();
		wp_reset_postdata();
		return $output;
	}

	/**
	 * Prepare instructor list
	 *
	 * @param string $current_page current page.
	 * @param mixed  $atts atts for query.
	 * @param array  $cat_ids category ids.
	 * @param string $keyword search keyword.
	 *
	 * @return array
	 */
	private function prepare_instructor_list( $current_page, $atts, $cat_ids = array(), $keyword = '' ) {

		$default_pagination = tutor_utils()->get_option( 'pagination_per_page', 9 );
		$limit              = (int) sanitize_text_field( tutor_utils()->array_get( 'count', $atts, $default_pagination ) );
		$page               = $current_page - 1;
		$rating_filter      = Input::post( 'rating_filter', '' );

		/**
		 * Sort by Relevant | New | Popular
		 *
		 * @since 2.0.0
		 */
		$short_by = Input::post( 'short_by', 'ASC' );

		$instructors       = tutor_utils()->get_instructors( $limit * $page, $limit, $keyword, '', '', $short_by, 'approved', $cat_ids, $rating_filter );
		$instructors_count = tutor_utils()->get_instructors( $limit * $page, $limit, $keyword, '', '', $short_by, 'approved', $cat_ids, $rating_filter, true );

		$layout      = sanitize_text_field( tutor_utils()->array_get( 'layout', $atts, '' ) );
		$layout      = in_array( $layout, $this->instructor_layout ) ? $layout : tutor_utils()->get_option( 'instructor_list_layout', $this->instructor_layout[0] );
		$default_col = tutor_utils()->get_option( 'courses_col_per_row', 3 );

		$payload = array(
			'instructors'       => is_array( $instructors ) ? $instructors : array(),
			'instructors_count' => $instructors_count,
			'column_count'      => sanitize_text_field( tutor_utils()->array_get( 'column_per_row', $atts, $default_col ) ),
			'layout'            => $layout,
			'limit'             => $limit,
			'current_page'      => $current_page,
			'filter'            => $atts,
		);

		return $payload;
	}

	/**
	 * Short code for getting instructors
	 *
	 * @param array $atts array of attrs.
	 *
	 * @return string
	 */
	public function tutor_instructor_list( $atts ) {
		global $wpdb;
		! is_array( $atts ) ? $atts = array() : 0;

		$current_page = (int) tutor_utils()->array_get( 'instructor-page', $_GET, 1 );
		$current_page = Input::get( 'instructor-page', 1, Input::TYPE_INT );
		$current_page = $current_page >= 1 ? $current_page : 1;

		$show_filter         = isset( $atts['filter'] ) ? 'on' === $atts['filter'] : tutor_utils()->get_option( 'instructor_list_show_filter', false );
		$atts['show_filter'] = $show_filter;

		// Get instructor list to sow.
		$payload                = $this->prepare_instructor_list( $current_page, $atts );
		$payload['show_filter'] = $show_filter;

		ob_start();
		tutor_load_template( 'shortcode.tutor-instructor', $payload );
		$content = ob_get_clean();

		if ( $show_filter ) {
			$limit           = 8;
			$course_taxonomy = 'course-category';
			$course_cats     = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT
						* 
					FROM {$wpdb->terms} AS term
					
					INNER JOIN {$wpdb->term_taxonomy} AS taxonomy
						ON taxonomy.term_id = term.term_id AND taxonomy.taxonomy = %s

					ORDER BY term.term_id DESC
					LIMIT %d
					",
					$course_taxonomy,
					$limit
				)
			);

			$all_cats = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT
						COUNT(*) as total 
					FROM {$wpdb->terms} AS term
						INNER JOIN {$wpdb->term_taxonomy} AS taxonomy
							ON taxonomy.term_id = term.term_id AND taxonomy.taxonomy = %s
					ORDER BY term.term_id DESC
					",
					$course_taxonomy
				)
			);

			$attributes = $payload;
			unset( $attributes['instructors'] );

			$payload = array(
				'show_filter' => $show_filter,
				'content'     => $content,
				'categories'  => $course_cats,
				'all_cats'    => $all_cats,
				'attributes'  => array_merge( $atts, $attributes ),
			);

			ob_start();

			tutor_load_template( 'shortcode.instructor-filter', $payload );

			$content = ob_get_clean();
		}

		return $content;
	}

	/**
	 * Load more categories
	 * handle ajax request
	 *
	 * @since 2.0.0
	 *
	 * @return void send wp_json response
	 */
	public function show_more() {
		global $wpdb;
		tutor_utils()->checking_nonce();
		$term_id         = Input::post( 'term_id', 0, Input::TYPE_INT );
		$limit           = 8;
		$course_taxonomy = 'course-category';

		$remaining_categories = $wpdb->get_var(
			$wpdb->prepare(
				"SElECT 
					COUNT(*) AS total 
				FROM {$wpdb->terms} AS term
					INNER JOIN {$wpdb->term_taxonomy} AS taxonomy
						ON taxonomy.term_id = term.term_id AND taxonomy.taxonomy = %s
				WHERE term.term_id < %d
				ORDER BY term.term_id DESC
				",
				$course_taxonomy,
				$term_id
			)
		);

		$add_categories = $wpdb->get_results(
			$wpdb->prepare(
				"SElECT
					* 
				FROM {$wpdb->terms} term
				INNER JOIN {$wpdb->term_taxonomy} as taxonomy
					ON taxonomy.term_id = term.term_id AND taxonomy.taxonomy = %s
				WHERE term.term_id < %d
				ORDER BY term.term_id DESC
				LIMIT %d
				",
				$course_taxonomy,
				$term_id,
				$limit
			)
		);
		$show_more      = false;
		if ( $remaining_categories > $limit ) {
			$show_more = true;
		}
		$response = array(
			'categories' => $add_categories,
			'show_more'  => $show_more,
			'remaining'  => $remaining_categories,
		);
		wp_send_json_success( $response );
		exit;
	}

	/**
	 * Filter instructor
	 *
	 * @since 1.0.0
	 *
	 * @return void send wp_json response
	 */
	public function load_filtered_instructor() {
		tutor_utils()->checking_nonce();

		// phpcs:disable WordPress.Security.NonceVerification.Missing --nonce already verified
		$_post        = tutor_sanitize_data( $_POST );
		$current_page = (int) sanitize_text_field( tutor_utils()->array_get( 'current_page', $_post, 1 ) );
		$keyword      = (string) sanitize_text_field( tutor_utils()->array_get( 'keyword', $_post, '' ) );

		$category = (array) tutor_utils()->array_get( 'category', $_post, array() );
		$category = array_filter(
			$category,
			function( $cat ) {
				return is_numeric( $cat );
			}
		);

		$data = $this->prepare_instructor_list( $current_page, $_post, $category, $keyword );

		ob_start();
		tutor_load_template( 'shortcode.tutor-instructor', $data );
		wp_send_json_success( array( 'html' => ob_get_clean() ) );
		exit;
	}
}
