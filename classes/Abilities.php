<?php
/**
 * WordPress Abilities API integration.
 *
 * @package Tutor
 * @since 4.1.1
 */

namespace TUTOR;

defined( 'ABSPATH' ) || exit;

/**
 * Registers Tutor LMS capabilities with the WordPress Abilities API.
 *
 * The integration is optional and only becomes active when the WordPress
 * Abilities API is available.
 *
 * @since 4.1.1
 */
final class Abilities {

	/**
	 * Register WordPress Abilities API hooks.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ) );
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
	}

	/**
	 * Register the Tutor LMS ability category.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function register_category() {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		wp_register_ability_category(
			'tutor-lms',
			array(
				'label'       => __( 'Tutor LMS', 'tutor' ),
				'description' => __( 'Course, curriculum, learner progress, and Tutor LMS environment information.', 'tutor' ),
			)
		);
	}

	/**
	 * Register Tutor LMS abilities.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function register_abilities() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		$this->register_site_info();
		$this->register_list_courses();
		$this->register_get_course();
		$this->register_course_structure();
		$this->register_student_progress();
	}

	/**
	 * Common metadata for read-only abilities.
	 *
	 * @since 4.1.1
	 *
	 * @return array
	 */
	private function readonly_meta() {
		return array(
			'public'      => true,
			'annotations' => array(
				'readonly'      => true,
				'destructive'   => false,
				'idempotent'    => true,
				'openWorldHint' => false,
			),
		);
	}

	/**
	 * Whether the current user may read Tutor LMS information.
	 *
	 * @since 4.1.1
	 *
	 * @return bool
	 */
	public function can_read() {
		return is_user_logged_in() && current_user_can( 'read' );
	}

	/**
	 * Register site information ability.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	private function register_site_info() {
		wp_register_ability(
			'tutor-lms/site-info',
			array(
				'label'               => __( 'Tutor LMS Site Information', 'tutor' ),
				'description'         => __( 'Retrieves Tutor LMS and WordPress environment information relevant to the active learning-management installation.', 'tutor' ),
				'category'            => 'tutor-lms',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(),
				),
				'execute_callback'    => array( $this, 'site_info' ),
				'permission_callback' => array( $this, 'can_read' ),
				'meta'                => $this->readonly_meta(),
			)
		);
	}

	/**
	 * Return Tutor LMS site information.
	 *
	 * @since 4.1.1
	 *
	 * @return array
	 */
	public function site_info() {
		return array(
			'tutor_lms_version' => defined( 'TUTOR_VERSION' ) ? TUTOR_VERSION : null,
			'wordpress_version' => get_bloginfo( 'version' ),
			'site_url'          => get_site_url(),
			'course_post_type'  => tutor()->course_post_type,
		);
	}

	/**
	 * Register course listing ability.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	private function register_list_courses() {
		wp_register_ability(
			'tutor-lms/list-courses',
			array(
				'label'               => __( 'List Tutor LMS Courses', 'tutor' ),
				'description'         => __( 'Lists Tutor LMS courses with pagination, optional text search, and status filtering.', 'tutor' ),
				'category'            => 'tutor-lms',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'page'     => array(
							'type'    => 'integer',
							'minimum' => 1,
							'default' => 1,
						),
						'per_page' => array(
							'type'    => 'integer',
							'minimum' => 1,
							'maximum' => 100,
							'default' => 20,
						),
						'search'   => array(
							'type' => 'string',
						),
						'status'   => array(
							'type'    => 'string',
							'default' => 'publish',
						),
					),
				),
				'execute_callback'    => array( $this, 'list_courses' ),
				'permission_callback' => array( $this, 'can_read' ),
				'meta'                => $this->readonly_meta(),
			)
		);
	}

	/**
	 * List Tutor LMS courses.
	 *
	 * @since 4.1.1
	 *
	 * @param array $input Ability input.
	 *
	 * @return array
	 */
	public function list_courses( array $input ) {
		$page     = isset( $input['page'] ) ? max( 1, (int) $input['page'] ) : 1;
		$per_page = isset( $input['per_page'] ) ? min( 100, max( 1, (int) $input['per_page'] ) ) : 20;
		$status   = isset( $input['status'] ) ? sanitize_key( $input['status'] ) : 'publish';
		$search   = isset( $input['search'] ) ? sanitize_text_field( $input['search'] ) : '';

		if ( 'publish' !== $status && ! current_user_can( 'edit_tutor_courses' ) && ! current_user_can( 'manage_tutor' ) ) {
			$status = 'publish';
		}

		$query = new \WP_Query(
			array(
				'post_type'      => tutor()->course_post_type,
				'post_status'    => $status,
				'posts_per_page' => $per_page,
				'paged'          => $page,
				's'              => $search,
			)
		);

		$courses = array_map(
			static function ( \WP_Post $post ) {
				return array(
					'id'        => $post->ID,
					'title'     => get_the_title( $post ),
					'status'    => $post->post_status,
					'author_id' => (int) $post->post_author,
					'url'       => get_permalink( $post ),
				);
			},
			$query->posts
		);

		return array(
			'page'        => $page,
			'per_page'    => $per_page,
			'total'       => (int) $query->found_posts,
			'total_pages' => (int) $query->max_num_pages,
			'courses'     => $courses,
		);
	}

	/**
	 * Register course detail ability.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	private function register_get_course() {
		wp_register_ability(
			'tutor-lms/get-course',
			array(
				'label'               => __( 'Get Tutor LMS Course', 'tutor' ),
				'description'         => __( 'Retrieves a Tutor LMS course and its primary content, publishing status, author, URL, and thumbnail information.', 'tutor' ),
				'category'            => 'tutor-lms',
				'input_schema'        => $this->course_id_schema(),
				'execute_callback'    => array( $this, 'get_course' ),
				'permission_callback' => array( $this, 'can_read' ),
				'meta'                => $this->readonly_meta(),
			)
		);
	}

	/**
	 * Return a Tutor LMS course.
	 *
	 * @since 4.1.1
	 *
	 * @param array $input Ability input.
	 *
	 * @return array|\WP_Error
	 */
	public function get_course( array $input ) {
		$course_id = (int) $input['course_id'];
		$post      = get_post( $course_id );

		if ( ! $post || tutor()->course_post_type !== $post->post_type ) {
			return new \WP_Error( 'tutor_course_not_found', __( 'Tutor LMS course not found.', 'tutor' ) );
		}

		if ( 'publish' !== $post->post_status && ! current_user_can( 'edit_post', $course_id ) ) {
			return new \WP_Error( 'tutor_course_forbidden', __( 'You do not have permission to read this course.', 'tutor' ) );
		}

		$thumbnail = get_the_post_thumbnail_url( $post, 'full' );

		return array(
			'id'        => $post->ID,
			'title'     => get_the_title( $post ),
			'content'   => apply_filters( 'the_content', $post->post_content ),
			'excerpt'   => $post->post_excerpt,
			'status'    => $post->post_status,
			'author_id' => (int) $post->post_author,
			'url'       => get_permalink( $post ),
			'thumbnail' => $thumbnail ? $thumbnail : null,
		);
	}

	/**
	 * Register curriculum structure ability.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	private function register_course_structure() {
		wp_register_ability(
			'tutor-lms/get-course-structure',
			array(
				'label'               => __( 'Get Tutor LMS Course Structure', 'tutor' ),
				'description'         => __( 'Retrieves the ordered curriculum structure of a Tutor LMS course, including topics and their supported child content.', 'tutor' ),
				'category'            => 'tutor-lms',
				'input_schema'        => $this->course_id_schema(),
				'execute_callback'    => array( $this, 'get_course_structure' ),
				'permission_callback' => array( $this, 'can_read' ),
				'meta'                => $this->readonly_meta(),
			)
		);
	}

	/**
	 * Return the ordered course curriculum.
	 *
	 * @since 4.1.1
	 *
	 * @param array $input Ability input.
	 *
	 * @return array|\WP_Error
	 */
	public function get_course_structure( array $input ) {
		$course_id = (int) $input['course_id'];
		$course    = get_post( $course_id );

		if ( ! $course || tutor()->course_post_type !== $course->post_type ) {
			return new \WP_Error( 'tutor_course_not_found', __( 'Tutor LMS course not found.', 'tutor' ) );
		}

		$can_edit = current_user_can( 'edit_post', $course_id );

		if ( 'publish' !== $course->post_status && ! $can_edit ) {
			return new \WP_Error( 'tutor_course_forbidden', __( 'You do not have permission to read this course.', 'tutor' ) );
		}

		$post_status = $can_edit ? 'any' : 'publish';
		$topics      = get_posts(
			array(
				'post_type'      => tutor()->topics_post_type,
				'post_parent'    => $course_id,
				'post_status'    => $post_status,
				'posts_per_page' => -1,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'ID'         => 'ASC',
				),
			)
		);

		$structure = array();

		foreach ( $topics as $topic ) {
			$children = get_posts(
				array(
					'post_type'      => apply_filters(
						'tutor_course_contents_post_types',
						array(
							tutor()->lesson_post_type,
							tutor()->quiz_post_type,
							'tutor_assignments',
						)
					),
					'post_parent'    => $topic->ID,
					'post_status'    => $post_status,
					'posts_per_page' => -1,
					'orderby'        => array(
						'menu_order' => 'ASC',
						'ID'         => 'ASC',
					),
				)
			);

			$structure[] = array(
				'id'       => $topic->ID,
				'title'    => get_the_title( $topic ),
				'children' => array_map(
					static function ( \WP_Post $child ) {
						return array(
							'id'        => $child->ID,
							'title'     => get_the_title( $child ),
							'post_type' => $child->post_type,
							'status'    => $child->post_status,
						);
					},
					$children
				),
			);
		}

		return array(
			'course_id' => $course_id,
			'title'     => get_the_title( $course ),
			'topics'    => $structure,
		);
	}

	/**
	 * Register learner progress ability.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	private function register_student_progress() {
		wp_register_ability(
			'tutor-lms/get-student-progress',
			array(
				'label'               => __( 'Get Tutor LMS Student Progress', 'tutor' ),
				'description'         => __( 'Retrieves a learner\'s completion progress for a Tutor LMS course.', 'tutor' ),
				'category'            => 'tutor-lms',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'course_id' => array(
							'type'    => 'integer',
							'minimum' => 1,
						),
						'user_id'   => array(
							'type'    => 'integer',
							'minimum' => 1,
						),
					),
					'required'   => array( 'course_id', 'user_id' ),
				),
				'execute_callback'    => array( $this, 'get_student_progress' ),
				'permission_callback' => array( $this, 'can_read_progress' ),
				'meta'                => $this->readonly_meta(),
			)
		);
	}

	/**
	 * Check whether the current user may read learner progress.
	 *
	 * @since 4.1.1
	 *
	 * @param array $input Ability input.
	 *
	 * @return bool
	 */
	public function can_read_progress( array $input ) {
		$user_id = isset( $input['user_id'] ) ? (int) $input['user_id'] : 0;

		return get_current_user_id() === $user_id
			|| current_user_can( 'edit_users' )
			|| current_user_can( 'manage_tutor' )
			|| current_user_can( 'manage_options' );
	}

	/**
	 * Return course completion progress for a learner.
	 *
	 * @since 4.1.1
	 *
	 * @param array $input Ability input.
	 *
	 * @return array
	 */
	public function get_student_progress( array $input ) {
		$course_id = (int) $input['course_id'];
		$user_id   = (int) $input['user_id'];
		$progress  = tutor_utils()->get_course_completed_percent( $course_id, $user_id );

		return array(
			'course_id'        => $course_id,
			'user_id'          => $user_id,
			'progress_percent' => (float) $progress,
		);
	}

	/**
	 * Course ID input schema.
	 *
	 * @since 4.1.1
	 *
	 * @return array
	 */
	private function course_id_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'course_id' => array(
					'type'    => 'integer',
					'minimum' => 1,
				),
			),
			'required'   => array( 'course_id' ),
		);
	}
}
