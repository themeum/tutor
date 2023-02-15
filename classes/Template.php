<?php
/**
 * Template Class
 *
 * @package Tutor\Template
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle template before include
 *
 * @since 1.0.0
 */
class Template extends Tutor_Base {

	/**
	 * Register Hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		/**
		 * Should Load Template Override
		 * Integration for specially oxygen builder
		 * If we found false of below filter, then we will not use this file
		 */

		$template_override = apply_filters( 'tutor_lms_should_template_override', true );
		if ( ! $template_override ) {
			return;
		}

		add_filter( 'template_include', array( $this, 'load_course_archive_template' ), 99 );
		add_filter( 'template_include', array( $this, 'load_single_course_template' ), 99 );
		add_filter( 'template_include', array( $this, 'load_single_lesson_template' ), 99 );
		add_filter( 'template_include', array( $this, 'play_private_video' ), 99 );
		add_filter( 'template_include', array( $this, 'load_quiz_template' ), 99 );
		add_filter( 'template_include', array( $this, 'load_assignment_template' ), 99 );

		add_filter( 'template_include', array( $this, 'student_public_profile' ), 99 );
		add_filter( 'template_include', array( $this, 'tutor_dashboard' ), 99 );
		add_filter( 'pre_get_document_title', array( $this, 'student_public_profile_title' ) );

		add_filter( 'the_content', array( $this, 'convert_static_page_to_template' ) );
		add_action( 'pre_get_posts', array( $this, 'limit_course_query_archive' ), 99 );
	}

	/**
	 * Load default template for course
	 *
	 * @since v.1.0.0
	 *
	 * @param sting $template template name.
	 *
	 * @return bool|string
	 */
	public function load_course_archive_template( $template ) {
		global $wp_query;

		$post_type       = get_query_var( 'post_type' );
		$course_category = get_query_var( 'course-category' );

		if ( ( $post_type === $this->course_post_type || ! empty( $course_category ) ) && $wp_query->is_archive ) {
			$template = tutor_get_template( 'archive-course' );
			return $template;
		}

		return $template;
	}

	/**
	 * Limit for course archive listing
	 *
	 * Make a page to archive listing for courses
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $query query argument.
	 *
	 * @return void
	 */
	public function limit_course_query_archive( $query ) {
		$courses_per_page = (int) tutor_utils()->get_option( 'courses_per_page', 12 );

		if ( $query->is_main_query() && ! $query->is_feed() && ! is_admin() && is_page() ) {
			$queried_object = get_queried_object();
			if ( $queried_object instanceof \WP_Post ) {
				$page_id               = $queried_object->ID;
				$selected_archive_page = (int) tutor_utils()->get_option( 'course_archive_page' );

				if ( $page_id === $selected_archive_page ) {
					$paged        = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
					$search_query = get_search_query();
					query_posts(
						array(
							'post_type'      => $this->course_post_type,
							'paged'          => $paged,
							's'              => $search_query,
							'posts_per_page' => $courses_per_page,
						)
					);
				}
			}
		}

		if ( $query->is_archive && $query->is_main_query() && ! $query->is_feed() && ! is_admin() ) {
			$post_type       = get_query_var( 'post_type' );
			$course_category = get_query_var( 'course-category' );
			if ( ( $post_type === $this->course_post_type || ! empty( $course_category ) ) ) {
				$query->set( 'posts_per_page', $courses_per_page );

				$course_filter = 'newest_first';
				if ( ! empty( Input::get( 'tutor_course_filter', '' ) ) ) {
					$course_filter = Input::get( 'tutor_course_filter' );
				}
				switch ( $course_filter ) {
					case 'newest_first':
						$query->set( 'orderby', 'ID' );
						$query->set( 'order', 'desc' );
						break;
					case 'oldest_first':
						$query->set( 'orderby', 'ID' );
						$query->set( 'order', 'asc' );
						break;
					case 'course_title_az':
						$query->set( 'orderby', 'post_title' );
						$query->set( 'order', 'asc' );
						break;
					case 'course_title_za':
						$query->set( 'orderby', 'post_title' );
						$query->set( 'order', 'desc' );
						break;
				}
			}
		}
	}

	/**
	 * Load Single Course Template
	 *
	 * @since v.1.0.0
	 *
	 * @param string $template template name to load.
	 *
	 * @return bool|string
	 */
	public function load_single_course_template( $template ) {
		global $wp_query;

		if ( $wp_query->is_single && ! empty( $wp_query->query_vars['post_type'] ) && $wp_query->query_vars['post_type'] === $this->course_post_type ) {
			do_action( 'single_course_template_before_load', get_the_ID() );
			wp_reset_query();
			return tutor_get_template( 'single-course' );
		}

		return $template;
	}

	/**
	 * Get root post parent id
	 *
	 * @param int $id post id.
	 *
	 * @return int root post id
	 */
	private function get_root_post_parent_id( $id ) {
		$ancestors = get_post_ancestors( $id );
		$root      = is_array( $ancestors ) ? end( $ancestors ) : null;

		return is_numeric( $root ) ? $root : $id;
	}

	/**
	 * Load lesson template
	 *
	 * @since v.1.0.0
	 *
	 * @param string $template template name to load.
	 *
	 * @return bool|string
	 */
	public function load_single_lesson_template( $template ) {
		global $wp_query;

		if ( $wp_query->is_single && ! empty( $wp_query->query_vars['post_type'] ) && $wp_query->query_vars['post_type'] === $this->lesson_post_type ) {
			$page_id = get_the_ID();

			do_action( 'tutor_lesson_load_before', $template );
			setup_postdata( $page_id );

			if ( is_user_logged_in() ) {
				$has_content_access = tutor_utils()->has_enrolled_content_access( 'lesson' );
				if ( $has_content_access ) {
					$template = tutor_get_template( 'single-lesson' );
				} else {
					$template = tutor_get_template( 'single.lesson.required-enroll' ); // You need to enroll first
				}
			} else {
				$template = tutor_get_template( 'login' );
			}
			wp_reset_postdata();

			// Forcefully show lessons if it is public and not paid.
			$course_id = $this->get_root_post_parent_id( $page_id );
			if ( 'yes' === get_post_meta( $course_id, '_tutor_is_public_course', true ) && ! tutor_utils()->is_course_purchasable( $course_id ) ) {
				$template = tutor_get_template( 'single-lesson' );
			}

			return apply_filters( 'tutor_lesson_template', $template );
		}
		return $template;
	}

	/**
	 * Play the video in this url.
	 *
	 * @param string $template template to load.
	 *
	 * @return mixed
	 */
	public function play_private_video( $template ) {
		global $wp_query;

		if ( $wp_query->is_single && ! empty( $wp_query->query_vars['lesson_video'] ) && 'true' === $wp_query->query_vars['lesson_video'] ) {

			$is_public_video = apply_filters( 'tutor_video_stream_is_public', false, get_the_ID() );
			if ( $is_public_video ) {
				$video_info = tutor_utils()->get_video_info();
				if ( $video_info ) {
					$stream = new Video_Stream( $video_info->path );
					$stream->start();
				}
				exit();
			}

			if ( tutor_utils()->is_course_enrolled_by_lesson() ) {
				$video_info = tutor_utils()->get_video_info();
				if ( $video_info ) {
					$stream = new Video_Stream( $video_info->path );
					$stream->start();
				}
			} else {
				esc_html_e( 'Permission denied', 'tutor' );
			}
			exit();
		}

		return $template;
	}

	/**
	 * Tutor Dashboard Page, Responsible to show dashboard stuffs
	 *
	 * @since v.1.0.0
	 *
	 * @param string $content page content.
	 *
	 * @return mixed
	 */
	public function convert_static_page_to_template( $content ) {
		// Dashboard Page.
		$student_dashboard_page_id = (int) tutor_utils()->get_option( 'tutor_dashboard_page_id' );
		if ( get_the_ID() === $student_dashboard_page_id ) {
			$shortcode = new Shortcode();
			return $shortcode->tutor_dashboard();
		}

		// Instructor Registration Page.
		$instructor_register_page_page_id = (int) tutor_utils()->get_option( 'instructor_register_page' );
		if ( get_the_ID() === $instructor_register_page_page_id ) {
			$shortcode = new Shortcode();
			return $shortcode->instructor_registration_form();
		}

		$student_register_page_id = (int) tutor_utils()->get_option( 'student_register_page' );
		if ( get_the_ID() === $student_register_page_id ) {
			$shortcode = new Shortcode();
			return $shortcode->student_registration_form();
		}

		return $content;
	}

	/**
	 * Tutor dashboard
	 *
	 * @since 1.0.0
	 *
	 * @param string $template template name.
	 *
	 * @return string
	 */
	public function tutor_dashboard( $template ) {
		global $wp_query;
		$is_page = apply_filters( 'tutor_determine_is_page', $wp_query->is_page, $template );
		if ( $is_page ) {
			$student_dashboard_page_id = (int) tutor_utils()->get_option( 'tutor_dashboard_page_id' );
			$student_dashboard_page_id = apply_filters( 'tutor_dashboard_page_id_filter', $student_dashboard_page_id );
			$is_dashboard_page         = apply_filters( 'tutor_determine_is_dashboard_page', get_the_ID() == $student_dashboard_page_id );

			if ( $is_dashboard_page ) {
				/**
				 * Handle if logout URL
				 *
				 * @since v.1.1.2
				 */
				if ( tutor_utils()->array_get( 'tutor_dashboard_page', $wp_query->query_vars ) === 'logout' ) {
					$redirect = apply_filters( 'tutor_dashboard_logout_redirect_url', get_permalink( $student_dashboard_page_id ) );
					wp_logout();
					wp_safe_redirect( $redirect );
					die();
				}

				$dashboard_page = tutor_utils()->array_get( 'tutor_dashboard_page', $wp_query->query_vars );

				$get_dashboard_config  = tutor_utils()->tutor_dashboard_permalinks();
				$target_dashboard_page = tutor_utils()->array_get( $dashboard_page, $get_dashboard_config );

				if ( isset( $target_dashboard_page['login_require'] ) && false === $target_dashboard_page['login_require'] ) {
					$template = tutor_load_template_part( "template-part.{$dashboard_page}" );
				} else {

					/**
					 * Load view page based on dashboard Endpoint
					 */
					if ( is_user_logged_in() ) {

						global $wp;
						$full_path = explode( '/', trim( str_replace( get_home_url(), '', home_url( $wp->request ) ), '/' ) );

						$template = tutor_get_template( 'create-course' === end( $full_path ) ? 'dashboard.create-course' : 'dashboard' );

						/**
						 * Check page page permission
						 *
						 * @since 1.3.4
						 */
						$query_var           = tutor_utils()->array_get( 'tutor_dashboard_page', $wp_query->query_vars );
						$dashboard_pages     = tutor_utils()->tutor_dashboard_pages();
						$dashboard_page_item = tutor_utils()->array_get( $query_var, $dashboard_pages );
						$auth_cap            = tutor_utils()->array_get( 'auth_cap', $dashboard_page_item );
						if ( $auth_cap && ! current_user_can( $auth_cap ) ) {
							$template = tutor_get_template( 'permission-denied' );
						}
					} else {
						$template = tutor_get_template( 'login' );
					}
				}
			}
		}
		return $template;
	}

	/**
	 * Load quiz template
	 *
	 * @since 1.0.0
	 *
	 * If course public then enrollment not required
	 *
	 * @since 2.0.2
	 *
	 * @param string $template template to load.
	 *
	 * @return bool|string
	 */
	public function load_quiz_template( $template ) {
		global $wp_query, $post;

		if ( $wp_query->is_single && ! empty( $wp_query->query_vars['post_type'] ) && 'tutor_quiz' === $wp_query->query_vars['post_type'] ) {
			if ( is_user_logged_in() ) {
				$has_content_access = tutor_utils()->has_enrolled_content_access( 'quiz' );
				$course_id          = tutor_utils()->get_course_id_by_content( $post );
				$is_public          = Course_List::is_public( $course_id );

				// if public course don't need to be enrolled.
				if ( $has_content_access || $is_public ) {
					$template = tutor_get_template( 'single-quiz' );
				} else {
					$template = tutor_get_template( 'single.lesson.required-enroll' ); // You need to enroll first.
				}
			} else {
				$template = tutor_get_template( 'login' );
			}
			return $template;
		}
		return $template;
	}

	/**
	 * Load assignment template
	 *
	 * @since 1.0.0
	 *
	 * @param string $template template file to load.
	 *
	 * @return string template path
	 */
	public function load_assignment_template( $template ) {
		global $wp_query;

		if ( $wp_query->is_single && ! empty( $wp_query->query_vars['post_type'] ) && 'tutor_assignments' === $wp_query->query_vars['post_type'] ) {
			if ( is_user_logged_in() ) {
				$has_content_access = tutor_utils()->has_enrolled_content_access( 'assignment' );
				if ( $has_content_access ) {
					$template = tutor_get_template( 'single-assignment' );
				} else {
					$template = tutor_get_template( 'single.lesson.required-enroll' ); // You need to enroll first
				}
			} else {
				$template = tutor_get_template( 'login' );
			}
			return $template;
		}

		return $template;
	}

	/**
	 * Student public profile
	 *
	 * @since 1.0.0
	 *
	 * @param string $template profile template.
	 *
	 * @return bool|string
	 */
	public function student_public_profile( $template ) {
		global $wp_query;
		$query_var = $wp_query->query_vars;
		if ( ! empty( $wp_query->query['tutor_profile_username'] ) ) {
			$template = tutor_get_template( 'public-profile' );
		}

		return $template;
	}

	/**
	 * Show student Profile
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function student_public_profile_title() {
		global $wp_query;

		if ( ! empty( $wp_query->query['tutor_profile_username'] ) ) {
			global $wpdb;

			$user_name = sanitize_text_field( $wp_query->query['tutor_profile_username'] );
			$user      = $wpdb->get_row( $wpdb->prepare( "SELECT display_name from {$wpdb->users} WHERE user_login = %s limit 1; ", $user_name ) );

			if ( ! empty( $user->display_name ) ) {
				return sprintf( "%s's Profile page ", $user->display_name );
			}
		}
		return '';
	}
}
