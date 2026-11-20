<?php
/**
 * Tutor MCP curriculum tools (topic + lesson + quiz).
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp\Abilities;

use Tutor\Models\QuizModel;
use Tutor\Mcp\Registrar;
use Tutor\Mcp\Support\AbilityFactory;
use Tutor\Mcp\Support\Pagination;
use Tutor\Mcp\Support\Permissions;
use Tutor\Mcp\Support\RestBridge;
use Tutor\Helpers\QueryHelper;
use TUTOR\Quiz;
use TUTOR\QuizBuilder;
use TUTOR\REST_Lesson;
use TUTOR\REST_Quiz;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Topic, lesson, and quiz tool registration and execute handlers.
 *
 * @since 4.3.0
 */
class CurriculumAbilities {

	/**
	 * Shared write-tool annotations.
	 *
	 * @var array<string, bool>
	 */
	private const WRITE_HINTS = array(
		'readOnlyHint'    => false,
		'destructiveHint' => false,
	);

	/**
	 * Destructive-write annotations.
	 *
	 * @since 4.3.0
	 *
	 * @var array<string, bool>
	 */
	private const DESTRUCTIVE_HINTS = array(
		'readOnlyHint'    => false,
		'destructiveHint' => true,
	);

	/**
	 * Register curriculum abilities.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function register(): void {
		AbilityFactory::tool(
			'tutor-mcp/create-topic',
			'tutor-mcp-curriculum',
			__( 'Create Topic', 'tutor' ),
			__( 'Create a course topic (same as course builder save topic).', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'course_id', 'title' ),
				'properties' => array(
					'course_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'title'     => array( 'type' => 'string' ),
					'summary'   => array( 'type' => 'string' ),
				),
			),
			static function ( array $input ): bool {
				return ! Registrar::is_read_only() && Permissions::can_edit_course( absint( $input['course_id'] ?? 0 ) );
			},
			array( $this, 'create_topic' ),
			self::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/update-topic',
			'tutor-mcp-curriculum',
			__( 'Update Topic', 'tutor' ),
			__( 'Update a course topic title/summary (same as course builder save topic).', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'topic_id' ),
				'properties' => array(
					'topic_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'title'    => array( 'type' => 'string' ),
					'summary'  => array( 'type' => 'string' ),
				),
			),
			static function ( array $input ): bool {
				if ( Registrar::is_read_only() ) {
					return false;
				}
				$course_id = (int) tutor_utils()->get_course_id_by( 'topic', absint( $input['topic_id'] ?? 0 ) );
				return $course_id && Permissions::can_edit_course( $course_id );
			},
			array( $this, 'update_topic' ),
			self::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/get-lesson',
			'tutor-mcp-curriculum',
			__( 'Get Lesson', 'tutor' ),
			__( 'Fetch a published lesson by ID (same as REST topic lessons).', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'lesson_id' ),
				'properties' => array(
					'lesson_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			),
			static function ( array $input ): bool {
				$lesson_id = absint( $input['lesson_id'] ?? 0 );
				$course_id = (int) tutor_utils()->get_course_id_by( 'lesson', $lesson_id );
				return $course_id && Permissions::can_edit_course( $course_id );
			},
			array( $this, 'get_lesson' )
		);

		AbilityFactory::tool(
			'tutor-mcp/create-lesson',
			'tutor-mcp-curriculum',
			__( 'Create Lesson', 'tutor' ),
			__( 'Create a lesson under a topic (same path as course builder tutor_save_lesson).', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'topic_id', 'title' ),
				'properties' => array(
					'topic_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'title'    => array( 'type' => 'string' ),
					'content'  => array( 'type' => 'string' ),
				),
			),
			static function ( array $input ): bool {
				if ( Registrar::is_read_only() ) {
					return false;
				}
				$course_id = (int) tutor_utils()->get_course_id_by( 'topic', absint( $input['topic_id'] ?? 0 ) );
				return $course_id && Permissions::can_edit_course( $course_id );
			},
			array( $this, 'create_lesson' ),
			self::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/update-lesson',
			'tutor-mcp-curriculum',
			__( 'Update Lesson', 'tutor' ),
			__( 'Update a lesson title/content (same path as course builder tutor_save_lesson).', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'lesson_id' ),
				'properties' => array(
					'lesson_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'topic_id'  => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'title'     => array( 'type' => 'string' ),
					'content'   => array( 'type' => 'string' ),
				),
			),
			static function ( array $input ): bool {
				if ( Registrar::is_read_only() ) {
					return false;
				}
				$course_id = (int) tutor_utils()->get_course_id_by( 'lesson', absint( $input['lesson_id'] ?? 0 ) );
				return $course_id && Permissions::can_edit_course( $course_id );
			},
			array( $this, 'update_lesson' ),
			self::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/get-quiz',
			'tutor-mcp-curriculum',
			__( 'Get Quiz', 'tutor' ),
			__( 'Fetch a published quiz by ID (same as REST quiz; answers gated).', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'quiz_id' ),
				'properties' => array(
					'quiz_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			),
			static function ( array $input ): bool {
				$quiz_id   = absint( $input['quiz_id'] ?? 0 );
				$course_id = (int) tutor_utils()->get_course_id_by( 'quiz', $quiz_id );
				return $course_id && Permissions::can_edit_course( $course_id );
			},
			array( $this, 'get_quiz' )
		);

		AbilityFactory::tool(
			'tutor-mcp/create-quiz',
			'tutor-mcp-curriculum',
			__( 'Create Quiz', 'tutor' ),
			__( 'Create a quiz under a topic. Optional quiz_option + questions (answers need is_correct).', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'topic_id', 'title' ),
				'properties' => array(
					'topic_id'    => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'title'       => array( 'type' => 'string' ),
					'content'     => array( 'type' => 'string' ),
					'quiz_option' => array( 'type' => 'object' ),
					'questions'   => array( 'type' => 'array' ),
				),
			),
			static function ( array $input ): bool {
				if ( Registrar::is_read_only() ) {
					return false;
				}
				$course_id = (int) tutor_utils()->get_course_id_by( 'topic', absint( $input['topic_id'] ?? 0 ) );
				return $course_id && Permissions::can_edit_course( $course_id );
			},
			array( $this, 'create_quiz' ),
			self::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/update-quiz',
			'tutor-mcp-curriculum',
			__( 'Update Quiz', 'tutor' ),
			__( 'Update a quiz. Optional quiz_option + questions (answers need is_correct).', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'quiz_id' ),
				'properties' => array(
					'quiz_id'     => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'topic_id'    => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'title'       => array( 'type' => 'string' ),
					'content'     => array( 'type' => 'string' ),
					'quiz_option' => array( 'type' => 'object' ),
					'questions'   => array( 'type' => 'array' ),
				),
			),
			static function ( array $input ): bool {
				if ( Registrar::is_read_only() ) {
					return false;
				}
				$course_id = (int) tutor_utils()->get_course_id_by( 'quiz', absint( $input['quiz_id'] ?? 0 ) );
				return $course_id && Permissions::can_edit_course( $course_id );
			},
			array( $this, 'update_quiz' ),
			self::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/attach-lesson-video',
			'tutor-mcp-curriculum',
			__( 'Attach Lesson Video', 'tutor' ),
			__( 'Attach a video source to a lesson. No media upload—reference by URL/ID.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'lesson_id', 'source' ),
				'properties' => array(
					'lesson_id'           => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'source'              => array(
						'type' => 'string',
						'enum' => array( 'youtube', 'vimeo', 'html5', 'external_url', 'embedded', 'shortcode' ),
					),
					'source_youtube'      => array( 'type' => 'string' ),
					'source_vimeo'        => array( 'type' => 'string' ),
					'source_html5'        => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'source_external_url' => array( 'type' => 'string' ),
					'source_embedded'     => array( 'type' => 'string' ),
					'source_shortcode'    => array( 'type' => 'string' ),
				),
			),
			static function ( array $input ): bool {
				if ( Registrar::is_read_only() ) {
					return false;
				}
				$course_id = (int) tutor_utils()->get_course_id_by( 'lesson', absint( $input['lesson_id'] ?? 0 ) );
				return $course_id && Permissions::can_edit_course( $course_id );
			},
			array( $this, 'attach_lesson_video' ),
			self::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/delete-topic',
			'tutor-mcp-curriculum',
			__( 'Delete Topic', 'tutor' ),
			__( 'Delete a topic (orphans children). Requires confirm=true.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'topic_id', 'confirm' ),
				'properties' => array(
					'topic_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'confirm'  => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
			),
			static function ( array $input ): bool {
				if ( Registrar::is_read_only() ) {
					return false;
				}
				$topic_id = absint( $input['topic_id'] ?? 0 );
				return $topic_id && tutor_utils()->can_user_manage( 'topic', $topic_id );
			},
			array( $this, 'delete_topic' ),
			self::DESTRUCTIVE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/delete-lesson',
			'tutor-mcp-curriculum',
			__( 'Delete Lesson', 'tutor' ),
			__( 'Permanently delete a lesson. Requires confirm=true.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'lesson_id', 'confirm' ),
				'properties' => array(
					'lesson_id' => array(
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
				if ( Registrar::is_read_only() ) {
					return false;
				}
				$lesson_id = absint( $input['lesson_id'] ?? 0 );
				return $lesson_id && tutor_utils()->can_user_manage( 'lesson', $lesson_id );
			},
			array( $this, 'delete_lesson' ),
			self::DESTRUCTIVE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/list-quizzes',
			'tutor-mcp-curriculum',
			__( 'List Quizzes', 'tutor' ),
			__( 'List quizzes for a course.', 'tutor' ),
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
			array( $this, 'list_quizzes' )
		);

		AbilityFactory::tool(
			'tutor-mcp/add-quiz-question',
			'tutor-mcp-curriculum',
			__( 'Add Quiz Question', 'tutor' ),
			__( 'Add a question to an existing quiz.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'quiz_id', 'question_title', 'question_type' ),
				'properties' => array(
					'quiz_id'          => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'question_title'   => array( 'type' => 'string' ),
					'question_type'    => array(
						'type' => 'string',
						'enum' => array( 'true_false', 'single_choice', 'multiple_choice', 'fill_in_the_blank', 'open_ended' ),
					),
					'question_mark'    => array(
						'type'    => 'number',
						'default' => 1,
					),
					'question_answers' => array( 'type' => 'array' ),
				),
			),
			static function ( array $input ): bool {
				if ( Registrar::is_read_only() ) {
					return false;
				}
				$course_id = (int) tutor_utils()->get_course_id_by( 'quiz', absint( $input['quiz_id'] ?? 0 ) );
				return $course_id && Permissions::can_edit_course( $course_id );
			},
			array( $this, 'add_quiz_question' ),
			self::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/delete-quiz',
			'tutor-mcp-curriculum',
			__( 'Delete Quiz', 'tutor' ),
			__( 'Delete a quiz and its questions/attempts. Requires confirm=true.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'quiz_id', 'confirm' ),
				'properties' => array(
					'quiz_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'confirm' => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
			),
			static function ( array $input ): bool {
				if ( Registrar::is_read_only() ) {
					return false;
				}
				$quiz_id = absint( $input['quiz_id'] ?? 0 );
				return $quiz_id && tutor_utils()->can_user_manage( 'quiz', $quiz_id );
			},
			array( $this, 'delete_quiz' ),
			self::DESTRUCTIVE_HINTS
		);
	}

	/**
	 * Create a course topic (same path as course builder tutor_save_topic).
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function create_topic( array $input ) {
		return $this->save_topic( $input );
	}

	/**
	 * Update a course topic.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function update_topic( array $input ) {
		return $this->save_topic( $input );
	}

	/**
	 * Create or update topic via wp_insert_post (same as tutor_save_topic).
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	private function save_topic( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		$topic_id  = absint( $input['topic_id'] ?? 0 );
		$course_id = absint( $input['course_id'] ?? 0 );
		if ( $topic_id ) {
			$course_id = (int) tutor_utils()->get_course_id_by( 'topic', $topic_id );
		}

		$title   = array_key_exists( 'title', $input )
			? sanitize_text_field( (string) $input['title'] )
			: ( $topic_id ? (string) get_the_title( $topic_id ) : '' );
		$summary = array_key_exists( 'summary', $input )
			? wp_kses_post( (string) $input['summary'] )
			: ( $topic_id ? (string) get_post_field( 'post_content', $topic_id ) : '' );

		if ( tutor()->course_post_type !== get_post_type( $course_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Course not found.', 'tutor' ) );
		}
		if ( '' === $title ) {
			return new \WP_Error( 'tutor_mcp_invalid', __( 'Topic title is required!', 'tutor' ) );
		}
		if ( ! tutor_utils()->can_user_manage( 'course', $course_id ) ) {
			return new \WP_Error( 'tutor_mcp_forbidden', __( 'Access Denied', 'tutor' ) );
		}
		if ( $topic_id && ( tutor()->topics_post_type !== get_post_type( $topic_id ) || ! tutor_utils()->can_user_manage( 'topic', $topic_id ) ) ) {
			return new \WP_Error( 'tutor_mcp_forbidden', __( 'Access Denied', 'tutor' ) );
		}

		$post_arr = array(
			'post_type'    => tutor()->topics_post_type,
			'post_title'   => $title,
			'post_content' => $summary,
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
			'post_parent'  => $course_id,
			'menu_order'   => tutor_utils()->get_next_topic_order_id( $course_id, $topic_id ),
		);
		if ( $topic_id ) {
			$post_arr['ID'] = $topic_id;
		}

		$saved_id = wp_insert_post( $post_arr, true );
		if ( is_wp_error( $saved_id ) ) {
			return $saved_id;
		}
		if ( ! $saved_id ) {
			return new \WP_Error( 'tutor_mcp_create_failed', __( 'Could not save topic.', 'tutor' ) );
		}

		return array(
			'id'        => (int) $saved_id,
			'course_id' => $course_id,
		);
	}

	/**
	 * Create lesson under a topic.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function create_lesson( array $input ) {
		return $this->save_lesson( $input );
	}

	/**
	 * Update lesson.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function update_lesson( array $input ) {
		return $this->save_lesson( $input );
	}

	/**
	 * Create/update lesson (core of ajax_save_lesson without JSON exit).
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	private function save_lesson( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		$lesson_id = absint( $input['lesson_id'] ?? 0 );
		$topic_id  = absint( $input['topic_id'] ?? 0 );
		if ( $lesson_id && ! $topic_id ) {
			$topic_id = (int) wp_get_post_parent_id( $lesson_id );
		}

		if ( ! $topic_id || tutor()->topics_post_type !== get_post_type( $topic_id ) || ! tutor_utils()->can_user_manage( 'topic', $topic_id ) ) {
			return new \WP_Error( 'tutor_mcp_forbidden', tutor_utils()->error_message() );
		}
		if ( $lesson_id && ( tutor()->lesson_post_type !== get_post_type( $lesson_id ) || ! tutor_utils()->can_user_manage( 'lesson', $lesson_id ) ) ) {
			return new \WP_Error( 'tutor_mcp_forbidden', tutor_utils()->error_message() );
		}

		$title   = array_key_exists( 'title', $input )
			? sanitize_text_field( (string) $input['title'] )
			: ( $lesson_id ? (string) get_the_title( $lesson_id ) : '' );
		$content = array_key_exists( 'content', $input )
			? wp_kses_post( (string) $input['content'] )
			: ( $lesson_id ? (string) get_post_field( 'post_content', $lesson_id ) : '' );

		if ( '' === $title ) {
			return new \WP_Error( 'tutor_mcp_invalid', __( 'Lesson title is required!', 'tutor' ) );
		}

		$lesson_data = array(
			'post_type'      => tutor()->lesson_post_type,
			'post_title'     => wp_slash( $title ),
			'post_name'      => sanitize_title( $title ),
			'post_content'   => wp_slash( $content ),
			'post_status'    => 'publish',
			'comment_status' => 'open',
			'post_author'    => get_current_user_id(),
			'post_parent'    => $topic_id,
		);

		if ( $lesson_id ) {
			$lesson_data['ID'] = $lesson_id;
			do_action( 'tutor/lesson_update/before', $lesson_id );
			$saved_id = wp_update_post( $lesson_data, true );
			if ( is_wp_error( $saved_id ) ) {
				return $saved_id;
			}
			do_action( 'tutor/lesson_update/after', $lesson_id );
		} else {
			$lesson_data['menu_order'] = tutor_utils()->get_next_course_content_order_id( $topic_id );
			$saved_id                  = wp_insert_post( $lesson_data, true );
			if ( is_wp_error( $saved_id ) ) {
				return $saved_id;
			}
			if ( ! $saved_id ) {
				return new \WP_Error( 'tutor_mcp_create_failed', __( 'Could not create lesson.', 'tutor' ) );
			}
			do_action( 'tutor/lesson/created', $saved_id );
		}

		return array(
			'id'       => (int) $saved_id,
			'topic_id' => $topic_id,
		);
	}

	/**
	 * Create quiz via QuizBuilder::save_quiz().
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function create_quiz( array $input ) {
		return $this->save_quiz( $input );
	}

	/**
	 * Update quiz via QuizBuilder::save_quiz().
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function update_quiz( array $input ) {
		return $this->save_quiz( $input );
	}

	/**
	 * Shared quiz save using QuizBuilder::save_quiz().
	 *
	 * Normalizes MCP-friendly input into the builder payload shape
	 * (`quiz_option` defaults, `_data_status` on questions/answers).
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	private function save_quiz( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		$quiz_id  = absint( $input['quiz_id'] ?? 0 );
		$topic_id = absint( $input['topic_id'] ?? 0 );
		if ( $quiz_id && ! $topic_id ) {
			$topic_id = (int) wp_get_post_parent_id( $quiz_id );
		}
		if ( ! $topic_id || tutor()->topics_post_type !== get_post_type( $topic_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Topic not found.', 'tutor' ) );
		}
		if ( $quiz_id && tutor()->quiz_post_type !== get_post_type( $quiz_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Quiz not found.', 'tutor' ) );
		}

		$title = array_key_exists( 'title', $input )
			? sanitize_text_field( (string) $input['title'] )
			: ( $quiz_id ? (string) get_the_title( $quiz_id ) : '' );
		if ( '' === $title ) {
			return new \WP_Error( 'tutor_mcp_invalid', __( 'Quiz title is required!', 'tutor' ) );
		}

		$existing_option = $quiz_id ? (array) get_post_meta( $quiz_id, Quiz::META_QUIZ_OPTION, true ) : array();
		$quiz_option     = isset( $input['quiz_option'] ) && is_array( $input['quiz_option'] )
			? $input['quiz_option']
			: $existing_option;

		$questions = $this->normalize_quiz_questions(
			isset( $input['questions'] ) && is_array( $input['questions'] ) ? $input['questions'] : array()
		);

		/*
		 * QuizBuilder::validate_payload() fatals on PHP 8+ when questions have
		 * empty answer lists (array_merge(...[])). Save the quiz shell first,
		 * then attach questions via the public save_questions() API.
		 */
		$payload = array(
			'post_title'   => $title,
			'post_content' => array_key_exists( 'content', $input )
				? wp_kses_post( (string) $input['content'] )
				: ( $quiz_id ? (string) get_post_field( 'post_content', $quiz_id ) : '' ),
			'quiz_option'  => Quiz::normalize_quiz_settings( $quiz_option ),
			'questions'    => array(),
		);
		if ( $quiz_id ) {
			$payload['ID'] = $quiz_id;
		}

		$builder = new QuizBuilder( false );
		$result  = $builder->save_quiz( $topic_id, $payload );
		if ( ! $result->success ) {
			$message = __( 'Quiz save failed.', 'tutor' );
			if ( ! empty( $result->errors ) ) {
				$encoded = wp_json_encode( $result->errors );
				if ( is_string( $encoded ) && '' !== $encoded ) {
					$message .= ' ' . $encoded;
				}
			}
			return new \WP_Error( 'tutor_mcp_quiz_failed', $message, $result->errors );
		}

		$saved_quiz_id = (int) $result->data;
		if ( count( $questions ) ) {
			try {
				$builder->save_questions( $saved_quiz_id, $questions );
			} catch ( \Throwable $th ) {
				return new \WP_Error( 'tutor_mcp_quiz_failed', $th->getMessage() );
			}
		}

		return array(
			'id'       => $saved_quiz_id,
			'topic_id' => $topic_id,
		);
	}

	/**
	 * Normalize questions/answers for QuizBuilder (_data_status + settings).
	 *
	 * @since 4.3.0
	 *
	 * @param array $questions Raw questions.
	 * @return array
	 */
	private function normalize_quiz_questions( array $questions ): array {
		$normalized = array();

		foreach ( $questions as $question ) {
			if ( ! is_array( $question ) ) {
				continue;
			}

			$question_id = absint( $question['question_id'] ?? 0 );
			$type        = sanitize_text_field( (string) ( $question['question_type'] ?? 'true_false' ) );

			if ( empty( $question[ QuizBuilder::TRACKING_KEY ] ) ) {
				$question[ QuizBuilder::TRACKING_KEY ] = $question_id
					? QuizBuilder::FLAG_UPDATE
					: QuizBuilder::FLAG_NEW;
			}

			if ( empty( $question['question_settings'] ) || ! is_array( $question['question_settings'] ) ) {
				$question['question_settings'] = Quiz::get_default_question_settings( $type );
			}

			$question['question_type'] = $type;
			if ( ! isset( $question['question_mark'] ) ) {
				$question['question_mark'] = 1;
			}

			$answers = isset( $question['question_answers'] ) && is_array( $question['question_answers'] )
				? $question['question_answers']
				: array();
			foreach ( $answers as $index => $answer ) {
				if ( ! is_array( $answer ) ) {
					unset( $answers[ $index ] );
					continue;
				}
				$answer_id = absint( $answer['answer_id'] ?? 0 );
				if ( empty( $answer[ QuizBuilder::TRACKING_KEY ] ) ) {
					$answer[ QuizBuilder::TRACKING_KEY ] = $answer_id
						? QuizBuilder::FLAG_UPDATE
						: QuizBuilder::FLAG_NEW;
				}
				$answers[ $index ] = $answer;
			}
			$question['question_answers'] = array_values( $answers );

			$normalized[] = $question;
		}

		return $normalized;
	}

	/**
	 * Attach a video source to a lesson.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function attach_lesson_video( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		$lesson_id = absint( $input['lesson_id'] ?? 0 );
		if ( tutor()->lesson_post_type !== get_post_type( $lesson_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Lesson not found.', 'tutor' ) );
		}

		$source = sanitize_text_field( (string) ( $input['source'] ?? '' ) );
		$video  = array( 'source' => $source );

		$source_key = 'source_' . $source;
		if ( 'html5' === $source ) {
			$attachment_id = absint( $input['source_html5'] ?? 0 );
			if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
				return new \WP_Error( 'tutor_mcp_invalid', __( 'Invalid attachment ID for html5 video.', 'tutor' ) );
			}
			$video['source_html5'] = $attachment_id;
		} elseif ( isset( $input[ $source_key ] ) ) {
			$video[ $source_key ] = sanitize_text_field( (string) $input[ $source_key ] );
		}

		$video = tutor_utils()->filter_video_meta( $video );
		update_post_meta( $lesson_id, '_video', $video );

		return array(
			'lesson_id' => $lesson_id,
			'video'     => $video,
		);
	}

	/**
	 * Delete a topic (mirrors Course::tutor_delete_topic logic).
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function delete_topic( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		if ( empty( $input['confirm'] ) ) {
			return new \WP_Error( 'tutor_mcp_confirm_required', __( 'Deleting requires confirm=true.', 'tutor' ) );
		}

		$topic_id = absint( $input['topic_id'] ?? 0 );
		if ( ! $topic_id || tutor()->topics_post_type !== get_post_type( $topic_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Topic not found.', 'tutor' ) );
		}

		global $wpdb;

		$course_id   = tutor_utils()->get_course_id_by( 'topic', $topic_id );
		$content_ids = tutor_utils()->get_course_content_ids_by( null, 'topic', $topic_id );
		foreach ( $content_ids as $content_id ) {
			update_post_meta( $content_id, '_tutor_course_id_for_lesson', $course_id );
		}

		$wpdb->update( $wpdb->posts, array( 'post_parent' => 0 ), array( 'post_parent' => $topic_id ) );
		$wpdb->delete( $wpdb->postmeta, array( 'post_id' => $topic_id ) );
		wp_delete_post( $topic_id );

		return array(
			'topic_id' => $topic_id,
			'deleted'  => true,
		);
	}

	/**
	 * Delete a lesson.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function delete_lesson( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		if ( empty( $input['confirm'] ) ) {
			return new \WP_Error( 'tutor_mcp_confirm_required', __( 'Deleting requires confirm=true.', 'tutor' ) );
		}

		$lesson_id = absint( $input['lesson_id'] ?? 0 );
		if ( ! $lesson_id || tutor()->lesson_post_type !== get_post_type( $lesson_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Lesson not found.', 'tutor' ) );
		}

		// Match Lesson::delete_lesson / CourseModel: ( course_id, lesson_id ). H5P listens with 2 args.
		do_action( 'tutor_before_delete_course_content', 0, $lesson_id );
		wp_delete_post( $lesson_id, true );

		return array(
			'lesson_id' => $lesson_id,
			'deleted'   => true,
		);
	}

	/**
	 * List quizzes for a course.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function list_quizzes( array $input ) {
		$course_id = absint( $input['course_id'] ?? 0 );
		if ( tutor()->course_post_type !== get_post_type( $course_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Course not found.', 'tutor' ) );
		}

		$topics = tutor_utils()->get_topics( $course_id );
		$quizzes = array();

		if ( $topics && $topics->have_posts() ) {
			foreach ( $topics->posts as $topic ) {
				$contents = get_posts(
					array(
						'post_type'      => tutor()->quiz_post_type,
						'post_parent'    => $topic->ID,
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'orderby'        => 'menu_order',
						'order'          => 'ASC',
					)
				);
				foreach ( $contents as $quiz ) {
					$quizzes[] = array(
						'id'       => (int) $quiz->ID,
						'title'    => $quiz->post_title,
						'topic_id' => (int) $topic->ID,
					);
				}
			}
		}

		return array(
			'course_id' => $course_id,
			'quizzes'   => $quizzes,
		);
	}

	/**
	 * Add a question to an existing quiz.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function add_quiz_question( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		$quiz_id = absint( $input['quiz_id'] ?? 0 );
		if ( ! $quiz_id || tutor()->quiz_post_type !== get_post_type( $quiz_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Quiz not found.', 'tutor' ) );
		}

		$type    = sanitize_text_field( (string) ( $input['question_type'] ?? 'true_false' ) );
		$allowed = array( 'true_false', 'single_choice', 'multiple_choice', 'fill_in_the_blank', 'open_ended' );
		if ( ! in_array( $type, $allowed, true ) ) {
			return new \WP_Error( 'tutor_mcp_invalid', __( 'Invalid question type.', 'tutor' ) );
		}

		$question = array(
			'question_title'    => sanitize_text_field( (string) ( $input['question_title'] ?? '' ) ),
			'question_type'     => $type,
			'question_mark'     => isset( $input['question_mark'] ) ? (float) $input['question_mark'] : 1,
			'question_settings' => Quiz::get_default_question_settings( $type ),
			QuizBuilder::TRACKING_KEY => QuizBuilder::FLAG_NEW,
		);

		$answers = isset( $input['question_answers'] ) && is_array( $input['question_answers'] )
			? $input['question_answers']
			: array();
		foreach ( $answers as $index => $answer ) {
			if ( ! is_array( $answer ) ) {
				unset( $answers[ $index ] );
				continue;
			}
			if ( empty( $answer[ QuizBuilder::TRACKING_KEY ] ) ) {
				$answer[ QuizBuilder::TRACKING_KEY ] = QuizBuilder::FLAG_NEW;
			}
			$answers[ $index ] = $answer;
		}
		$question['question_answers'] = array_values( $answers );

		$builder = new QuizBuilder( false );
		try {
			$builder->save_questions( $quiz_id, array( $question ) );
		} catch ( \Throwable $th ) {
			return new \WP_Error( 'tutor_mcp_quiz_failed', $th->getMessage() );
		}

		return array(
			'quiz_id' => $quiz_id,
			'added'   => true,
		);
	}

	/**
	 * Delete a quiz and its data (mirrors Quiz::ajax_quiz_delete).
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function delete_quiz( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		if ( empty( $input['confirm'] ) ) {
			return new \WP_Error( 'tutor_mcp_confirm_required', __( 'Deleting requires confirm=true.', 'tutor' ) );
		}

		$quiz_id = absint( $input['quiz_id'] ?? 0 );
		if ( ! $quiz_id || tutor()->quiz_post_type !== get_post_type( $quiz_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Quiz not found.', 'tutor' ) );
		}

		global $wpdb;

		do_action( 'tutor_delete_quiz_before', $quiz_id );

		$attempts       = QueryHelper::get_all( 'tutor_quiz_attempts', array( 'quiz_id' => $quiz_id ), 'attempt_id', -1 );
		$attempt_fpaths = array();
		if ( ! empty( $attempts ) ) {
			$attempt_ids    = array_map(
				static function ( $row ) {
					return (int) $row->attempt_id;
				},
				$attempts
			);
			$attempt_fpaths = apply_filters( 'tutor_quiz/attempt_file_paths_for_deletion', array(), $attempt_ids );
			$attempt_fpaths = is_array( $attempt_fpaths ) ? array_values( array_filter( array_unique( $attempt_fpaths ) ) ) : array();
		}

		$wpdb->delete( $wpdb->prefix . 'tutor_quiz_attempts', array( 'quiz_id' => $quiz_id ) );
		$wpdb->delete( $wpdb->prefix . 'tutor_quiz_attempt_answers', array( 'quiz_id' => $quiz_id ) );
		QuizModel::delete_files_by_paths( $attempt_fpaths );

		$quiz_fpaths   = apply_filters( 'tutor_quiz_quiz_file_paths_for_deletion', array(), $quiz_id );
		$questions_ids = $wpdb->get_col( $wpdb->prepare( "SELECT question_id FROM {$wpdb->prefix}tutor_quiz_questions WHERE quiz_id = %d", $quiz_id ) );

		if ( is_array( $questions_ids ) && count( $questions_ids ) ) {
			$in_ids = QueryHelper::prepare_in_clause( $questions_ids );
			$wpdb->query( "DELETE FROM {$wpdb->prefix}tutor_quiz_question_answers WHERE belongs_question_id IN({$in_ids})" ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		$wpdb->delete( $wpdb->prefix . 'tutor_quiz_questions', array( 'quiz_id' => $quiz_id ) );
		QuizModel::delete_files_by_paths( is_array( $quiz_fpaths ) ? $quiz_fpaths : array() );
		wp_delete_post( $quiz_id, true );

		do_action( 'tutor_delete_quiz_after', $quiz_id );

		return array(
			'quiz_id' => $quiz_id,
			'deleted' => true,
		);
	}

	/**
	 * Get lesson.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function get_lesson( array $input ) {
		$lesson_id = absint( $input['lesson_id'] ?? 0 );
		$post      = get_post( $lesson_id );
		if ( ! $post instanceof \WP_Post || tutor()->lesson_post_type !== $post->post_type ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Lesson not found.', 'tutor' ) );
		}

		$request = new \WP_REST_Request( 'GET' );
		$request->set_param( 'topic_id', (int) $post->post_parent );

		$lessons = RestBridge::unwrap_call(
			static function () use ( $request ) {
				return ( new REST_Lesson() )->topic_lesson( $request );
			}
		);
		if ( is_wp_error( $lessons ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Lesson not found.', 'tutor' ) );
		}

		foreach ( $lessons as $lesson ) {
			if ( (int) ( $lesson['ID'] ?? 0 ) === $lesson_id ) {
				return $lesson;
			}
		}

		return new \WP_Error( 'tutor_mcp_not_found', __( 'Lesson not found.', 'tutor' ) );
	}

	/**
	 * Get quiz (answer keys gated by REST).
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function get_quiz( array $input ) {
		$quiz_id = absint( $input['quiz_id'] ?? 0 );
		$request = new \WP_REST_Request( 'GET' );
		$request->set_param( 'id', $quiz_id );

		$data = RestBridge::unwrap_call(
			static function () use ( $request ) {
				return ( new REST_Quiz() )->get_quiz( $request );
			}
		);
		if ( is_wp_error( $data ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Quiz not found.', 'tutor' ) );
		}

		return $data;
	}
}
