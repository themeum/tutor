<?php
/**
 * Quiz Builder
 *
 * @package Tutor\Classes
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Tutor\Helpers\HttpHelper;
use Tutor\Helpers\QueryHelper;
use Tutor\Helpers\ValidationHelper;
use Tutor\Models\QuizModel;
use Tutor\Traits\JsonResponse;

/**
 * Class QuizBuilder
 *
 * @since 1.0.0
 */
class QuizBuilder {
	use JsonResponse;

	const TRACKING_KEY   = '_data_status';
	const FLAG_NEW       = 'new';
	const FLAG_UPDATE    = 'update';
	const FLAG_NO_CHANGE = 'no_change';

	/**
	 * Register hooks and dependencies.
	 *
	 * @param boolean $register_hooks register hooks or not.
	 */
	public function __construct( $register_hooks = true ) {
		if ( ! $register_hooks ) {
			return;
		}

		add_action( 'wp_ajax_tutor_quiz_builder_save', array( $this, 'ajax_quiz_builder_save' ) );
	}


	/**
	 * Prepare question data.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $quiz_id quiz id.
	 * @param array $input question data.
	 *
	 * @return array
	 */
	private function prepare_question_data( $quiz_id, $input ) {
		$question_title       = Input::sanitize( wp_slash( $input['question_title'] ), '' );
		$question_description = Input::sanitize( wp_slash( $input['question_description'] ) ?? '', '', Input::TYPE_KSES_POST );
		$question_type        = Input::sanitize( $input['question_type'], '' );
		$question_mark        = Input::sanitize( $input['question_mark'], 1, Input::TYPE_INT );
		$question_settings    = Input::sanitize_array( $input['question_settings'] );

		$data = array(
			'quiz_id'              => $quiz_id,
			'question_title'       => $question_title,
			'question_description' => $question_description,
			'question_type'        => $question_type,
			'question_mark'        => $question_mark,
			'question_settings'    => maybe_serialize( $question_settings ),
		);

		return apply_filters( 'tutor_quiz_question_data', $data, $input );
	}

	/**
	 * Prepare answer data.
	 *
	 * @param int    $question_id question id.
	 * @param string $question_type question type.
	 * @param array  $input answer data.
	 *
	 * @return array
	 */
	public function prepare_answer_data( $question_id, $question_type, $input ) {
		$answer_title         = Input::sanitize( wp_slash( $input['answer_title'] ) ?? '', '' );
		$is_correct           = Input::sanitize( $input['is_correct'] ?? 0, 0, Input::TYPE_INT );
		$image_id             = Input::sanitize( $input['image_id'] ?? null );
		$answer_two_gap_match = Input::sanitize( $input['answer_two_gap_match'] ?? '' );
		$answer_view_format   = Input::sanitize( $input['answer_view_format'] ?? '' );
		$answer_settings      = null;

		$answer_data = array(
			'belongs_question_id'   => $question_id,
			'belongs_question_type' => $question_type,
			'answer_title'          => $answer_title,
			'is_correct'            => $is_correct,
			'image_id'              => $image_id,
			'answer_two_gap_match'  => $answer_two_gap_match,
			'answer_view_format'    => $answer_view_format,
			'answer_settings'       => $answer_settings,
		);

		return $answer_data;
	}

	/**
	 * Save quiz questions.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $quiz_id quiz id.
	 * @param array $questions questions data.
	 *
	 * @return void
	 */
	public function save_questions( $quiz_id, $questions ) {
		global $wpdb;
		$questions_table = $wpdb->prefix . 'tutor_quiz_questions';
		$answers_table   = $wpdb->prefix . 'tutor_quiz_question_answers';

		$question_order = 0;
		foreach ( $questions as $question ) {
			$data_status      = isset( $question[ self::TRACKING_KEY ] ) ? $question[ self::TRACKING_KEY ] : self::FLAG_NO_CHANGE;
			$question_type    = Input::sanitize( $question['question_type'] );
			$question_data    = $this->prepare_question_data( $quiz_id, $question );
			$question_answers = isset( $question['question_answers'] ) ? $question['question_answers'] : array();

			// New question.
			if ( self::FLAG_NEW === $data_status ) {
				$wpdb->insert( $questions_table, $question_data );
				$question_id = $wpdb->insert_id;
			}

			// Update question.
			if ( self::FLAG_UPDATE === $data_status ) {
				$question_id = (int) $question['question_id'];
				$wpdb->update(
					$questions_table,
					$question_data,
					array( 'question_id' => $question_id )
				);
			}

			if ( self::FLAG_NO_CHANGE === $data_status ) {
				$question_id = $question['question_id'];
			}

			// Save sort order.
			$question_order++;
			$wpdb->update(
				$questions_table,
				array( 'question_order' => $question_order ),
				array( 'question_id' => $question_id )
			);

			// Save question's answers.
			$answer_order = 0;
			foreach ( $question_answers as $answer ) {
				$data_status = isset( $answer[ self::TRACKING_KEY ] ) ? $answer[ self::TRACKING_KEY ] : self::FLAG_NO_CHANGE;
				$answer_data = $this->prepare_answer_data( $question_id, $question_type, $answer );

				// New answer.
				if ( self::FLAG_NEW === $data_status ) {
					$wpdb->insert( $answers_table, $answer_data );
					$answer_id = $wpdb->insert_id;
				}

				// Update answer.
				if ( self::FLAG_UPDATE === $data_status ) {
					$answer_id = $answer['answer_id'];
					$wpdb->update(
						$answers_table,
						$answer_data,
						array( 'answer_id' => $answer_id )
					);
				}

				if ( self::FLAG_NO_CHANGE === $data_status ) {
					$answer_id = $answer['answer_id'];
				}

				// Save sort order.
				$answer_order++;
				$wpdb->update(
					$answers_table,
					array( 'answer_order' => $answer_order ),
					array( 'answer_id' => $answer_id )
				);
			}
		}
	}

	/**
	 * Validate payload.
	 *
	 * @since 3.0.0
	 *
	 * @param array $payload payload.
	 *
	 * @return object consist success, errors.
	 */
	public function validate_payload( $payload ) {
		$errors  = array();
		$success = true;

		if ( ! is_array( $payload ) ) {
			$success           = false;
			$errors['payload'] = __( 'Invalid payload', 'tutor' );
		}

		$rules = array(
			'post_title'  => 'required',
			'quiz_option' => 'required|is_array',
			'questions'   => 'required|is_array',
		);

		$validation = ValidationHelper::validate(
			$rules,
			$payload
		);

		if ( ! $validation->success ) {
			$success = false;
			$errors  = array_merge( $errors, $validation->errors );
		}

		foreach ( $payload['questions'] as $question ) {
			if ( ! isset( $question[ self::TRACKING_KEY ] ) ) {
				$success                        = false;
				$errors[ self::TRACKING_KEY ][] = sprintf( __( '%s is required for each question', 'tutor' ), self::TRACKING_KEY ); //phpcs:ignore
				break;
			}

			if ( ! in_array( $question[ self::TRACKING_KEY ], array( self::FLAG_NEW, self::FLAG_UPDATE, self::FLAG_NO_CHANGE ), true ) ) {
				$success                        = false;
				$errors[ self::TRACKING_KEY ][] = sprintf( __( 'Invalid value for %s', 'tutor' ), self::TRACKING_KEY ); //phpcs:ignore
				break;
			}

			if ( ! isset( $question['question_settings'] ) || ! is_array( $question['question_settings'] ) ) {
				$success                       = false;
				$errors['question_settings'][] = __( 'Question settings is required with array data', 'tutor' );
				break;
			}
		}

		return (object) array(
			'success' => $success,
			'errors'  => $errors,
		);
	}

	/**
	 * Handle delete questions and answers.
	 *
	 * @since 3.0.0
	 *
	 * @param array $deleted_question_ids question ids.
	 * @param array $deleted_answer_ids answer ids.
	 *
	 * @return void
	 */
	public function handle_delete( $deleted_question_ids = array(), $deleted_answer_ids = array() ) {
		global $wpdb;
		$deleted_question_ids = array_filter( $deleted_question_ids, 'is_numeric' );
		$deleted_answer_ids   = array_filter( $deleted_answer_ids, 'is_numeric' );

		if ( count( $deleted_question_ids ) ) {
			$id_str = QueryHelper::prepare_in_clause( $deleted_question_ids );
            //phpcs:ignore -- sanitized $id_str.
            $wpdb->query( "DELETE FROM {$wpdb->prefix}tutor_quiz_questions WHERE question_id IN (" . $id_str . ')' );
			do_action( 'tutor_deleted_quiz_question_ids', $deleted_question_ids );
		}

		if ( count( $deleted_answer_ids ) ) {
			$id_str = QueryHelper::prepare_in_clause( $deleted_answer_ids );
            //phpcs:ignore -- sanitized $id_str.
            $wpdb->query( "DELETE FROM {$wpdb->prefix}tutor_quiz_question_answers WHERE answer_id IN (" . $id_str . ')' );
		}
	}

	/**
	 * Create or update quiz.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $topic_id topic id.
	 * @param array $payload payload.
	 *
	 * @return object consist success, errors.
	 */
	public function save_quiz( $topic_id, $payload ) {
		$success = true;
		$data    = null;
		$errors  = array();

		$validation = $this->validate_payload( $payload );

		if ( ! $validation->success ) {
			return (object) array(
				'success' => false,
				'errors'  => $validation->errors,
			);
		}

		$is_update = isset( $payload['ID'] );
		$quiz_id   = $is_update ? $payload['ID'] : null;
		$questions = isset( $payload['questions'] ) ? $payload['questions'] : array();

		$menu_order = (int) ( isset( $payload['menu_order'] )
						? $payload['menu_order']
						: tutor_utils()->get_next_course_content_order_id( $topic_id, $quiz_id ) );

		$quiz_data = array(
			'post_type'    => tutor()->quiz_post_type,
			'post_title'   => Input::sanitize( wp_slash( $payload['post_title'] ?? '' ) ),
			'post_content' => Input::sanitize( wp_slash( $payload['post_content'] ?? '' ) ),
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
			'post_parent'  => $topic_id,
			'menu_order'   => $menu_order,
		);

		global $wpdb;
		$wpdb->query( 'START TRANSACTION' );

		try {
			// Add or update the quiz.
			if ( $is_update ) {
				$quiz_data['ID'] = $quiz_id;
			}

			$quiz_id = wp_insert_post( $quiz_data );
			do_action( ( $is_update ? 'tutor_quiz_updated' : 'tutor_initial_quiz_created' ), $quiz_id );

			// Save quiz settings.
			$quiz_option = Input::sanitize_array( $payload['quiz_option'] ?? array() ); //phpcs:ignore
			update_post_meta( $quiz_id, Quiz::META_QUIZ_OPTION, $quiz_option );
			do_action( 'tutor_quiz_settings_updated', $quiz_id );

			// Save quiz questions.
			if ( count( $questions ) ) {
				$this->save_questions( $quiz_id, $questions );
			}

			// Delete questions and answers.
			$deleted_question_ids = Input::post( 'deleted_question_ids', array(), Input::TYPE_ARRAY );
			$deleted_answer_ids   = Input::post( 'deleted_answer_ids', array(), Input::TYPE_ARRAY );
			$this->handle_delete( $deleted_question_ids, $deleted_answer_ids );

			$wpdb->query( 'COMMIT' );

			$data = $quiz_id;

		} catch ( \Throwable $th ) {
			$wpdb->query( 'ROLLBACK' );

			$success         = false;
			$errors['500'][] = $th->getMessage();
		}

		return (object) array(
			'success' => $success,
			'data'    => $data,
			'errors'  => $errors,
		);
	}

	/**
	 * Create or update quiz from new course builder.
	 *
	 * @since 3.0.0
	 *
	 * @return void json response.
	 */
	public function ajax_quiz_builder_save() {
		tutor_utils()->check_nonce();

		$payload    = $_POST['payload'] ?? array(); //phpcs:ignore
		if ( is_string( $payload ) ) {
			$payload = json_decode( wp_unslash( $payload ), true );
		}

		$course_id  = Input::post( 'course_id', 0, Input::TYPE_INT );
		$topic_id   = Input::post( 'topic_id', 0, Input::TYPE_INT );
		$course_cls = new Course( false );

		$course_cls->check_access( $course_id );

		$result = $this->save_quiz( $topic_id, wp_slash( $payload ) );
		if ( $result->success ) {
			$quiz_id      = $result->data;
			$quiz_details = QuizModel::get_quiz_details( $quiz_id );
			$this->json_response( __( 'Quiz saved successfully', 'tutor' ), $quiz_details );
		} else {
			$this->json_response( __( 'Error', 'tutor' ), $result->errors, HttpHelper::STATUS_BAD_REQUEST );
		}

	}
}
