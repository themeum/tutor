<?php
/**
 * Tutor MCP course tools.
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp\Abilities;

defined( 'ABSPATH' ) || exit;

use Tutor\Helpers\QueryHelper;
use Tutor\Models\CourseModel;
use Tutor\Mcp\Support\AbilityFactory;
use Tutor\Mcp\Support\Pagination;
use Tutor\Mcp\Support\Permissions;
use Tutor\Mcp\Support\RestBridge;
use TUTOR\Course;
use TUTOR\REST_Course;
use TUTOR\User;

/**
 * Course tool registration and execute handlers.
 *
 * @since 4.3.0
 */
class CourseAbilities {

	/**
	 * Register course abilities.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function register(): void {
		AbilityFactory::tool(
			'tutor-mcp/get-course',
			'tutor-mcp-courses',
			__( 'Get Course', 'tutor' ),
			__( 'Fetch course additional info by ID (same as REST course detail).', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'course_id' ),
				'properties' => array(
					'course_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			),
			static function ( array $input ): bool {
				return Permissions::can_edit_course( absint( $input['course_id'] ?? 0 ) );
			},
			array( $this, 'get_course' )
		);

		AbilityFactory::tool(
			'tutor-mcp/search-courses',
			'tutor-mcp-courses',
			__( 'Search Courses', 'tutor' ),
			__( 'Paginated course list. Supports status, search, author, orderby, and comma-separated category/tag names.', 'tutor' ),
			array(
				'type'       => 'object',
				'properties' => array(
					'page'       => array(
						'type'    => 'integer',
						'minimum' => 1,
						'default' => 1,
					),
					'per_page'   => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 50,
						'default' => 20,
					),
					'status'     => array(
						'type' => 'string',
						'enum' => array( 'publish', 'draft', 'pending', 'private', 'trash', 'any' ),
					),
					'search'     => array( 'type' => 'string' ),
					'author'     => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'order'      => array(
						'type' => 'string',
						'enum' => array( 'ASC', 'DESC', 'asc', 'desc' ),
					),
					'orderby'    => array(
						'type' => 'string',
						'enum' => array( 'title', 'date', 'modified', 'ID', 'name', 'author', 'menu_order' ),
					),
					'categories' => array(
						'type'        => 'string',
						'description' => __( 'Comma-separated course category names.', 'tutor' ),
					),
					'tags'       => array(
						'type'        => 'string',
						'description' => __( 'Comma-separated course tag names.', 'tutor' ),
					),
				),
			),
			static function (): bool {
				return Permissions::can_manage_courses();
			},
			array( $this, 'search_courses' )
		);

		AbilityFactory::tool(
			'tutor-mcp/get-course-contents',
			'tutor-mcp-courses',
			__( 'Get Course Contents', 'tutor' ),
			__( 'Topics and nested lessons/quizzes/assignments (same as REST course contents).', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'course_id' ),
				'properties' => array(
					'course_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			),
			static function ( array $input ): bool {
				return Permissions::can_edit_course( absint( $input['course_id'] ?? 0 ) );
			},
			array( $this, 'get_course_contents' )
		);

		AbilityFactory::tool(
			'tutor-mcp/create-course',
			'tutor-mcp-courses',
			__( 'Create Course', 'tutor' ),
			__( 'Create a draft/pending course (never publishes). Do not publish unless the user explicitly asks — then use set-course-status with confirm=true. Always tell the user the course is in draft/pending mode.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'title' ),
				'properties' => array(
					'title'   => array( 'type' => 'string' ),
					'content' => array( 'type' => 'string' ),
					'excerpt' => array( 'type' => 'string' ),
				),
			),
			static function (): bool {
				return Permissions::can_manage_courses();
			},
			array( $this, 'create_course' ),
			AbilityFactory::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/update-course',
			'tutor-mcp-courses',
			__( 'Update Course', 'tutor' ),
			__( 'Update course title/content/excerpt/taxonomies. Does not change publish status.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'course_id' ),
				'properties' => array(
					'course_id'         => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'title'             => array( 'type' => 'string' ),
					'content'           => array( 'type' => 'string' ),
					'excerpt'           => array( 'type' => 'string' ),
					'course_categories' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'integer' ),
					),
					'course_tags'       => array(
						'type'  => 'array',
						'items' => array( 'type' => 'integer' ),
					),
					'thumbnail_id'      => array(
						'type'    => 'integer',
						'minimum' => 0,
					),
				),
			),
			static function ( array $input ): bool {
				return Permissions::can_edit_course( absint( $input['course_id'] ?? 0 ) );
			},
			array( $this, 'update_course' ),
			AbilityFactory::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/set-course-status',
			'tutor-mcp-courses',
			__( 'Set Course Status', 'tutor' ),
			__( 'Set course status. Only publish when the user explicitly asks. Publishing requires confirm=true and publish capability.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'course_id', 'status' ),
				'properties' => array(
					'course_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'status'    => array(
						'type' => 'string',
						'enum' => array(
							CourseModel::STATUS_DRAFT,
							CourseModel::STATUS_PENDING,
							CourseModel::STATUS_PRIVATE,
							CourseModel::STATUS_PUBLISH,
						),
					),
					'confirm'   => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
			),
			static function ( array $input ): bool {
				return Permissions::can_edit_course( absint( $input['course_id'] ?? 0 ) );
			},
			array( $this, 'set_course_status' ),
			AbilityFactory::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/trash-course',
			'tutor-mcp-courses',
			__( 'Trash Course', 'tutor' ),
			__( 'Move a course to trash. Requires confirm=true. Does NOT permanently delete.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'course_id', 'confirm' ),
				'properties' => array(
					'course_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'confirm'   => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
			),
			static function ( array $input ): bool {
				return Permissions::can_trash_course( absint( $input['course_id'] ?? 0 ) );
			},
			array( $this, 'trash_course' ),
			AbilityFactory::DESTRUCTIVE_HINTS
		);
	}

	/**
	 * Get course.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function get_course( array $input ) {
		$course_id = absint( $input['course_id'] ?? 0 );
		$request   = new \WP_REST_Request( 'GET' );
		$request->set_param( 'id', $course_id );

		$data = RestBridge::unwrap_call(
			static function () use ( $request ) {
				return ( new REST_Course() )->course_detail( $request );
			}
		);
		if ( is_wp_error( $data ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Course not found.', 'tutor' ) );
		}

		return $data;
	}

	/**
	 * Search courses with status/search/author/taxonomy support.
	 *
	 * Admins: all courses. Instructors: own/co-instructed courses only
	 * (scoped via CourseModel::get_courses_by_instructor IDs), then filtered
	 * with the same WP_Query args as admins so author/orderby/categories/tags apply.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array
	 */
	public function search_courses( array $input ): array {
		$bounds   = Pagination::bounds( $input );
		$status   = sanitize_text_field( (string) ( $input['status'] ?? 'any' ) );
		$search   = sanitize_text_field( (string) ( $input['search'] ?? '' ) );
		$order    = QueryHelper::get_valid_sort_order( (string) ( $input['order'] ?? 'DESC' ) );
		$orderby  = $this->sanitize_search_orderby( $input['orderby'] ?? 'date' );
		$statuses = 'any' === $status
			? array( 'publish', 'draft', 'pending', 'private', 'trash' )
			: array( $status );

		$args = array(
			'post_type'      => tutor()->course_post_type,
			'post_status'    => $statuses,
			'posts_per_page' => $bounds['per_page'],
			'paged'          => $bounds['page'],
			'orderby'        => $orderby,
			'order'          => $order,
		);

		if ( '' !== $search ) {
			$args['s'] = $search;
		}
		if ( ! empty( $input['author'] ) ) {
			$args['author'] = absint( $input['author'] );
		}

		$tax_query = $this->build_search_tax_query( $input );
		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Intentional taxonomy filter for course search.
		}

		if ( ! User::is_admin() ) {
			$scoped_ids = $this->get_instructor_scoped_course_ids( get_current_user_id(), $statuses );
			if ( empty( $scoped_ids ) ) {
				return array(
					'posts'        => array(),
					'total_course' => 0,
					'total_page'   => 0,
				);
			}
			$args['post__in'] = $scoped_ids;
		}

		$query = new \WP_Query( $args );
		$posts = array();
		foreach ( $query->posts as $post ) {
			$posts[] = array(
				'ID'          => (int) $post->ID,
				'post_title'  => $post->post_title,
				'post_status' => $post->post_status,
				'post_date'   => $post->post_date,
				'post_author' => (int) $post->post_author,
			);
		}

		return array(
			'posts'        => $posts,
			'total_course' => (int) $query->found_posts,
			'total_page'   => (int) $query->max_num_pages,
		);
	}

	/**
	 * Whitelist WP_Query orderby for MCP course search.
	 *
	 * @since 4.3.0
	 *
	 * @param mixed $orderby Raw orderby value.
	 * @return string
	 */
	private function sanitize_search_orderby( $orderby ): string {
		$orderby = sanitize_text_field( (string) $orderby );
		$allowed = array( 'title', 'date', 'modified', 'ID', 'name', 'author', 'menu_order' );

		if ( in_array( $orderby, $allowed, true ) ) {
			return $orderby;
		}

		return 'date';
	}

	/**
	 * Build tax_query from comma-separated category/tag names.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Ability input.
	 * @return array
	 */
	private function build_search_tax_query( array $input ): array {
		$categories = $this->parse_term_names( $input['categories'] ?? '' );
		$tags       = $this->parse_term_names( $input['tags'] ?? '' );
		$tax_query  = array();

		if ( ! empty( $categories ) ) {
			$tax_query[] = array(
				'taxonomy' => CourseModel::COURSE_CATEGORY,
				'field'    => 'name',
				'terms'    => $categories,
			);
		}

		if ( ! empty( $tags ) ) {
			$tax_query[] = array(
				'taxonomy' => CourseModel::COURSE_TAG,
				'field'    => 'name',
				'terms'    => $tags,
			);
		}

		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'OR';
		}

		return $tax_query;
	}

	/**
	 * Parse comma-separated taxonomy term names.
	 *
	 * @since 4.3.0
	 *
	 * @param mixed $raw Raw input value.
	 * @return array<int, string>
	 */
	private function parse_term_names( $raw ): array {
		if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
			return array();
		}

		$parts = array_map( 'trim', explode( ',', $raw ) );
		$names = array();
		foreach ( $parts as $part ) {
			$name = sanitize_text_field( $part );
			if ( '' !== $name ) {
				$names[] = $name;
			}
		}

		return array_values( array_unique( $names ) );
	}

	/**
	 * Course IDs an instructor owns or co-instructs (My Courses scope).
	 *
	 * @since 4.3.0
	 *
	 * @param int          $instructor_id Instructor user ID.
	 * @param array|string $statuses      Post statuses.
	 * @return array<int, int>
	 */
	private function get_instructor_scoped_course_ids( int $instructor_id, $statuses ): array {
		$courses = CourseModel::get_courses_by_instructor(
			$instructor_id,
			$statuses,
			0,
			PHP_INT_MAX,
			false,
			array(),
			'',
			'DESC'
		);

		if ( ! is_array( $courses ) || empty( $courses ) ) {
			return array();
		}

		$ids = array();
		foreach ( $courses as $course ) {
			if ( isset( $course->ID ) ) {
				$ids[] = (int) $course->ID;
			}
		}

		return array_values( array_unique( array_filter( $ids ) ) );
	}

	/**
	 * Course contents tree.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function get_course_contents( array $input ) {
		$course_id = absint( $input['course_id'] ?? 0 );
		if ( tutor()->course_post_type !== get_post_type( $course_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Course not found.', 'tutor' ) );
		}

		$request = new \WP_REST_Request( 'GET' );
		$request->set_param( 'id', $course_id );

		$topics = RestBridge::unwrap_call(
			static function () use ( $request ) {
				return ( new REST_Course() )->course_contents( $request );
			}
		);
		// REST returns 404 when a course has no topics; empty outline is a valid MCP result.
		if ( is_wp_error( $topics ) ) {
			return array(
				'course_id' => $course_id,
				'topics'    => array(),
			);
		}

		return array(
			'course_id' => $course_id,
			'topics'    => is_array( $topics ) ? $topics : array(),
		);
	}

	/**
	 * Create draft/pending course (mirrors tutor_create_new_draft_course + architecture draft policy).
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function create_course( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		$title   = sanitize_text_field( (string) ( $input['title'] ?? '' ) );
		$content = wp_kses_post( (string) ( $input['content'] ?? '' ) );
		$excerpt = sanitize_textarea_field( (string) ( $input['excerpt'] ?? '' ) );
		if ( '' === $title ) {
			return new \WP_Error( 'tutor_mcp_invalid', __( 'Course title is required!', 'tutor' ) );
		}

		// Match Course create flow: admins get draft; instructors pending unless they can publish.
		$status = CourseModel::STATUS_DRAFT;
		if ( ! User::is_admin() && ! tutor_utils()->get_option( 'instructor_can_publish_course', false ) ) {
			$status = CourseModel::STATUS_PENDING;
		}

		$course_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_content' => $content,
				'post_excerpt' => $excerpt,
				'post_type'    => tutor()->course_post_type,
				'post_status'  => $status,
				'post_author'  => get_current_user_id(),
			),
			true
		);
		if ( is_wp_error( $course_id ) ) {
			return $course_id;
		}
		if ( ! $course_id ) {
			return new \WP_Error( 'tutor_mcp_create_failed', __( 'Could not create course.', 'tutor' ) );
		}

		update_post_meta( $course_id, Course::COURSE_PRICE_TYPE_META, Course::PRICE_TYPE_FREE );
		do_action( 'tutor_draft_course_created', $course_id );

		$status_label = CourseModel::STATUS_PENDING === $status
			? __( 'pending review', 'tutor' )
			: __( 'draft', 'tutor' );

		return array(
			'id'          => (int) $course_id,
			'post_status' => $status,
			'message'     => sprintf(
				/* translators: %s: draft or pending review */
				__( 'Course created in %s mode (not published). Tell the user it is unpublished. Only call set-course-status to publish if they explicitly asked.', 'tutor' ),
				$status_label
			),
		);
	}

	/**
	 * Update course fields (no status change).
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function update_course( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		$course_id = absint( $input['course_id'] ?? 0 );
		if ( tutor()->course_post_type !== get_post_type( $course_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Course not found.', 'tutor' ) );
		}

		$post = array( 'ID' => $course_id );
		if ( isset( $input['title'] ) ) {
			$post['post_title'] = sanitize_text_field( (string) $input['title'] );
		}
		if ( isset( $input['content'] ) ) {
			$post['post_content'] = wp_kses_post( (string) $input['content'] );
		}
		if ( isset( $input['excerpt'] ) ) {
			$post['post_excerpt'] = sanitize_textarea_field( (string) $input['excerpt'] );
		}

		if ( count( $post ) > 1 ) {
			$updated = wp_update_post( $post, true );
			if ( is_wp_error( $updated ) ) {
				return $updated;
			}
		}

		if ( isset( $input['course_categories'] ) && is_array( $input['course_categories'] ) ) {
			wp_set_object_terms( $course_id, array_map( 'absint', $input['course_categories'] ), CourseModel::COURSE_CATEGORY );
		}
		if ( isset( $input['course_tags'] ) && is_array( $input['course_tags'] ) ) {
			wp_set_object_terms( $course_id, array_map( 'absint', $input['course_tags'] ), CourseModel::COURSE_TAG );
		}
		if ( array_key_exists( 'thumbnail_id', $input ) ) {
			$thumbnail_id = absint( $input['thumbnail_id'] );
			if ( $thumbnail_id ) {
				set_post_thumbnail( $course_id, $thumbnail_id );
			} else {
				delete_post_meta( $course_id, '_thumbnail_id' );
			}
		}

		return array( 'id' => $course_id );
	}

	/**
	 * Set course status with publish confirm gate.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function set_course_status( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		$course_id = absint( $input['course_id'] ?? 0 );
		$status    = sanitize_text_field( (string) ( $input['status'] ?? '' ) );
		$allowed   = array(
			CourseModel::STATUS_DRAFT,
			CourseModel::STATUS_PENDING,
			CourseModel::STATUS_PRIVATE,
			CourseModel::STATUS_PUBLISH,
		);
		if ( tutor()->course_post_type !== get_post_type( $course_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Course not found.', 'tutor' ) );
		}
		if ( ! in_array( $status, $allowed, true ) ) {
			return new \WP_Error( 'tutor_mcp_invalid', __( 'Invalid course status.', 'tutor' ) );
		}

		if ( CourseModel::STATUS_PUBLISH === $status ) {
			if ( empty( $input['confirm'] ) ) {
				return new \WP_Error( 'tutor_mcp_confirm_required', __( 'Publishing requires confirm=true.', 'tutor' ) );
			}
			if ( ! current_user_can( 'publish_tutor_courses' ) && ! User::is_admin() ) {
				return new \WP_Error( 'tutor_mcp_forbidden', __( 'Access Denied', 'tutor' ) );
			}
			$instructor_can_publish = (bool) tutor_utils()->get_option( 'instructor_can_publish_course', false );
			if ( ! User::is_admin() && ! $instructor_can_publish ) {
				$status = CourseModel::STATUS_PENDING;
			}
		}

		$updated = wp_update_post(
			array(
				'ID'          => $course_id,
				'post_status' => $status,
			),
			true
		);
		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		return array(
			'id'          => $course_id,
			'post_status' => get_post_status( $course_id ),
		);
	}

	/**
	 * Trash a course (soft delete).
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function trash_course( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		if ( empty( $input['confirm'] ) ) {
			return new \WP_Error( 'tutor_mcp_confirm_required', __( 'Trashing requires confirm=true.', 'tutor' ) );
		}

		$course_id = absint( $input['course_id'] ?? 0 );
		if ( tutor()->course_post_type !== get_post_type( $course_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Course not found.', 'tutor' ) );
		}

		$result = wp_trash_post( $course_id );
		if ( ! $result ) {
			return new \WP_Error( 'tutor_mcp_trash_failed', __( 'Could not trash course.', 'tutor' ) );
		}

		return array(
			'id'          => $course_id,
			'post_status' => 'trash',
		);
	}
}
