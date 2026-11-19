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

use Tutor\Mcp\Registrar;
use Tutor\Mcp\Support\AbilityFactory;
use Tutor\Mcp\Support\Permissions;
use Tutor\Mcp\Support\RestBridge;
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
				return $course_id && Permissions::can_view_course( $course_id );
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
				return $course_id && Permissions::can_view_course( $course_id );
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
