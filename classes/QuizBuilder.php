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
use TUTOR_PRO\QuizImageStorage;

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
	public function prepare_question_data( $quiz_id, $input ) {
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
	 * @param string $data_status answer data status.
	 *
	 * @return array
	 */
	public function prepare_answer_data( $question_id, $question_type, $input, $data_status = self::FLAG_NO_CHANGE ) {
		$answer_title = Input::sanitize( wp_slash( $input['answer_title'] ) ?? '', '' );
		$is_correct   = Input::sanitize( $input['is_correct'] ?? 0, 0, Input::TYPE_INT );
		$image_id     = Input::sanitize( $input['image_id'] ?? null );
		// Let the hook handle special cases (e.g. draw_image, pin_image) and return a normalized value (URL).
		$answer_two_gap_match_raw = isset( $input['answer_two_gap_match'] ) ? wp_unslash( $input['answer_two_gap_match'] ) : '';
		$answer_two_gap_match_raw = apply_filters(
			'tutor_save_quiz_draw_image_mask',
			$answer_two_gap_match_raw,
			$question_type,
			array(
				'data_status' => $data_status,
			)
		);
		$answer_two_gap_match     = Input::sanitize( $answer_two_gap_match_raw ?? '', '' );
		$answer_view_format       = Input::sanitize( $input['answer_view_format'] ?? '' );
		$answer_settings          = null;

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
	 * Save question answers.
	 *
	 * @since 3.7.0
	 *
	 * @param int    $question_id question id.
	 * @param string $question_type question type.
	 * @param array  $question_answers question answers.
	 *
	 * @return void
	 */
	public function save_question_answers( $question_id, $question_type, $question_answers ) {
		global $wpdb;
		$answers_table = $wpdb->prefix . 'tutor_quiz_question_answers';

		$answer_order = 0;
		foreach ( $question_answers as $answer ) {
			$data_status = isset( $answer[ self::TRACKING_KEY ] ) ? $answer[ self::TRACKING_KEY ] : self::FLAG_NO_CHANGE;
			$answer_data = $this->prepare_answer_data( $question_id, $question_type, $answer, $data_status );

			// New answer.
			if ( self::FLAG_NEW === $data_status ) {
				$wpdb->insert( $answers_table, $answer_data );
				$answer_id = $wpdb->insert_id;
			}

			// Update answer.
			if ( self::FLAG_UPDATE === $data_status ) {
				$answer_id = $answer['answer_id'];
				$old_mask  = '';
				if ( $this->is_mask_image_question_type( $question_type ) ) {
					$old_answer = QueryHelper::get_row( $answers_table, array( 'answer_id' => (int) $answer_id ), 'answer_id' );
					$old_mask   = is_object( $old_answer ) ? (string) ( $old_answer->answer_two_gap_match ?? '' ) : '';
				}
				$wpdb->update(
					$answers_table,
					$answer_data,
					array( 'answer_id' => $answer_id )
				);
				if ( $this->is_mask_image_question_type( $question_type ) ) {
					$new_mask = (string) ( $answer_data['answer_two_gap_match'] ?? '' );
					$this->delete_replaced_mask_file( $old_mask, $new_mask );
				}
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

	/**
	 * Whether question type stores a mask image URL in answer_two_gap_match.
	 *
	 * @since 4.0.0
	 *
	 * @param string $question_type Question type.
	 *
	 * @return bool
	 */
	private function is_mask_image_question_type( $question_type ) {
		return in_array( $question_type, array( 'draw_image', 'pin_image' ), true );
	}

	/**
	 * Delete old mask file if it was replaced with a different mask URL.
	 *
	 * @since 4.0.0
	 *
	 * @param string $old_mask Previous mask URL.
	 * @param string $new_mask New mask URL.
	 *
	 * @return void
	 */
	private function delete_replaced_mask_file( $old_mask, $new_mask ) {
		$old_mask = trim( (string) $old_mask );
		$new_mask = trim( (string) $new_mask );

		if ( '' === $old_mask || $old_mask === $new_mask ) {
			return;
		}

		if ( ! class_exists( '\TUTOR_PRO\QuizImageStorage' ) ) {
			return;
		}

		$upload_dir = wp_upload_dir();
		if ( ! empty( $upload_dir['error'] ) ) {
			return;
		}

		$old_path = QuizImageStorage::quiz_image_stored_value_to_path( $old_mask );
		if ( '' === $old_path || ! is_file( $old_path ) || ! is_readable( $old_path ) ) {
			return;
		}

		$quiz_dir = trailingslashit( $upload_dir['basedir'] ) . QuizImageStorage::QUIZ_IMAGES_SUBDIR . '/';
		if ( 0 !== strpos( $old_path, $quiz_dir ) ) {
			return;
		}

		wp_delete_file( $old_path );
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
	 *
	 * @throws \Exception When saving a draw_image question while Legacy learning mode is enabled.
	 */
	public function save_questions( $quiz_id, $questions ) {
		global $wpdb;
		$questions_table = $wpdb->prefix . 'tutor_quiz_questions';

		$question_order = 0;
		foreach ( $questions as $question ) {
			$data_status = isset( $question[ self::TRACKING_KEY ] ) ? $question[ self::TRACKING_KEY ] : self::FLAG_NO_CHANGE;
			$question_order++;
			if ( isset( $question['is_cb_question'], $question['cb_action'] ) && 'link' === $question['cb_action'] ) {
				$question['question_order'] = $question_order;
				do_action( 'tutor_content_bank_question_linked_to_quiz', $quiz_id, (object) $question );
				continue;
			}

			$question_type = Input::sanitize( $question['question_type'] );
			if ( 'draw_image' === $question_type && tutor_utils()->is_legacy_learning_mode() ) {
				throw new \Exception( esc_html__( 'Image Marking questions are not available when Legacy learning mode is enabled.', 'tutor' ) );
			}
			if ( 'pin_image' === $question_type && tutor_utils()->is_legacy_learning_mode() ) {
				throw new \Exception( esc_html__( 'Pin questions are not available when Legacy learning mode is enabled.', 'tutor' ) );
			}
			if ( 'scale' === $question_type && tutor_utils()->is_legacy_learning_mode() ) {
				throw new \Exception( esc_html__( 'Range questions are not available when Legacy learning mode is enabled.', 'tutor' ) );
			}
			if ( 'puzzle' === $question_type && tutor_utils()->is_legacy_learning_mode() ) {
				throw new \Exception( esc_html__( 'Puzzle questions are not available when Legacy learning mode is enabled.', 'tutor' ) );
			}
			$question_data    = $this->prepare_question_data( $quiz_id, $question );
			$question_answers = isset( $question['question_answers'] ) ? $question['question_answers'] : array();

			// New question.
			if ( self::FLAG_NEW === $data_status ) {
				$wpdb->insert( $questions_table, $question_data );
				$question_id = $wpdb->insert_id;

				if ( isset( $question['is_cb_question'] ) ) {
					$question['question_order']  = $question_order;
					$question['new_question_id'] = $question_id;
					do_action( 'tutor_content_bank_question_added_to_quiz', $quiz_id, (object) $question );
				}
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
			$wpdb->update(
				$questions_table,
				array( 'question_order' => $question_order ),
				array( 'question_id' => $question_id )
			);

			// Save question's answers.
			$this->save_question_answers( $question_id, $question_type, $question_answers );
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

		if ( isset( $payload['ID'] ) && is_numeric( $payload['ID'] ) ) {
			if ( ! current_user_can( 'edit_post', $payload['ID'] ) ) {
				$success                = false;
				$errors['permission'][] = __( 'You do not have permission to edit this quiz', 'tutor' );
			} else {
				$quiz = get_post( $payload['ID'] );
				if ( ! $quiz || tutor()->quiz_post_type !== $quiz->post_type ) {
					$success        = false;
					$errors['ID'][] = __( 'Invalid quiz id provided', 'tutor' );
				}
			}
		}

		foreach ( $payload['questions'] as $question ) {
			if ( ! isset( $question[ self::TRACKING_KEY ] ) ) {
				$success = false;
				// translators: %s is the tracking key required for each question.
				$errors[ self::TRACKING_KEY ][] = sprintf( __( '%s is required for each question', 'tutor' ), self::TRACKING_KEY );
				break;
			}

			if ( ! in_array( $question[ self::TRACKING_KEY ], array( self::FLAG_NEW, self::FLAG_UPDATE, self::FLAG_NO_CHANGE ), true ) ) {
				$success = false;
				// translators: %s is the tracking key for which the value is invalid.
				$errors[ self::TRACKING_KEY ][] = sprintf( __( 'Invalid value for %s', 'tutor' ), self::TRACKING_KEY );
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
	 * @param array $deleted_temp_mask_values unsaved draw/pin/puzzle mask values.
	 *
	 * @return void
	 */
	public function handle_delete( $deleted_question_ids = array(), $deleted_answer_ids = array(), $deleted_temp_mask_values = array() ) {
		global $wpdb;
		$deleted_question_ids     = array_filter( $deleted_question_ids, 'is_numeric' );
		$deleted_answer_ids       = array_filter( $deleted_answer_ids, 'is_numeric' );
		$deleted_temp_mask_values = is_array( $deleted_temp_mask_values ) ? array_values( array_filter( array_map( 'strval', $deleted_temp_mask_values ) ) ) : array();
		$question_file_paths      = array();
		$mask_question_ids        = array();

		if ( count( $deleted_question_ids ) ) {
			$mask_question_ids = $this->get_deletable_mask_question_ids( $deleted_question_ids );

			if ( count( $mask_question_ids ) ) {
				$question_file_paths = $this->get_question_file_paths_for_deletion( $mask_question_ids );

				$in_clause = QueryHelper::prepare_in_clause( $mask_question_ids );
				// Only remove answers automatically for file-based quiz types (draw/pin/puzzle).
				//phpcs:ignore -- sanitized $in_clause.
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}tutor_quiz_question_answers WHERE belongs_question_id IN ({$in_clause})" ) );
			}

			$in_clause = QueryHelper::prepare_in_clause( $deleted_question_ids );
			// Preserve previous behavior for all non file-based quiz types and linked-content hooks.
			//phpcs:ignore -- sanitized $in_clause.
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}tutor_quiz_questions WHERE content_id IS NULL AND question_id IN ({$in_clause})" ) );
			do_action( 'tutor_deleted_quiz_question_ids', $deleted_question_ids );
		}

		if ( count( $deleted_answer_ids ) ) {
			$in_clause = QueryHelper::prepare_in_clause( $deleted_answer_ids );
            //phpcs:ignore -- sanitized $in_clause.
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}tutor_quiz_question_answers WHERE answer_id IN ({$in_clause})" ) );
		}

		if ( count( $question_file_paths ) ) {
			QuizModel::delete_files_by_paths( $question_file_paths );
		}

		if ( count( $deleted_temp_mask_values ) ) {
			$temp_file_paths = $this->get_temp_mask_file_paths_for_deletion( $deleted_temp_mask_values );
			if ( count( $temp_file_paths ) ) {
				QuizModel::delete_files_by_paths( $temp_file_paths );
			}
		}
	}

	/**
	 * Get deletable question IDs for file-based question types.
	 *
	 * Only draw/pin/puzzle are selected to keep behavior unchanged for other quiz types.
	 *
	 * @since 4.0.0
	 *
	 * @param array $question_ids Question IDs from payload.
	 *
	 * @return int[]
	 */
	private function get_deletable_mask_question_ids( array $question_ids ) {
		$question_ids = array_map( 'intval', array_filter( $question_ids, 'is_numeric' ) );
		if ( empty( $question_ids ) ) {
			return array();
		}

		$question_rows = QueryHelper::get_all(
			QueryHelper::prepare_table_name( 'tutor_quiz_questions' ),
			array(
				'question_id'   => array( 'IN', $question_ids ),
				'content_id'    => array( 'IS', 'NULL' ),
				'question_type' => array( 'IN', array( 'draw_image', 'pin_image', 'puzzle' ) ),
			),
			'question_id',
			-1
		);

		if ( empty( $question_rows ) ) {
			return array();
		}

		return array_values(
			array_unique(
				array_map(
					static function ( $row ) {
						return (int) ( $row->question_id ?? 0 );
					},
					$question_rows
				)
			)
		);
	}

	/**
	 * Collect draw/pin/puzzle files linked to question answers before question deletion.
	 *
	 * @since 4.0.0
	 *
	 * @param array $question_ids Question IDs that will be deleted.
	 *
	 * @return string[]
	 */
	private function get_question_file_paths_for_deletion( array $question_ids ) {
		$paths = array();

		if ( empty( $question_ids ) || ! class_exists( '\TUTOR_PRO\QuizImageStorage' ) ) {
			return $paths;
		}

		$answers = QueryHelper::get_all(
			QueryHelper::prepare_table_name( 'tutor_quiz_question_answers' ),
			array(
				'belongs_question_id'   => array( 'IN', $question_ids ),
				'belongs_question_type' => array( 'IN', array( 'draw_image', 'pin_image', 'puzzle' ) ),
			),
			'answer_id',
			-1
		);

		if ( empty( $answers ) ) {
			return $paths;
		}

		$upload_dir = wp_upload_dir();
		if ( ! empty( $upload_dir['error'] ) ) {
			return $paths;
		}

		$quiz_dir = trailingslashit( $upload_dir['basedir'] ) . QuizImageStorage::QUIZ_IMAGES_SUBDIR . '/';

		foreach ( $answers as $answer ) {
			$stored = isset( $answer->answer_two_gap_match ) ? trim( (string) $answer->answer_two_gap_match ) : '';
			if ( '' === $stored ) {
				continue;
			}

			$path = $this->resolve_mask_stored_value_to_path( $stored );

			if ( '' === $path && 'puzzle' === ( $answer->belongs_question_type ?? '' ) ) {
				$payload = json_decode( stripslashes( $stored ), true );
				if ( is_array( $payload ) && ! empty( $payload['playground_snapshot_file'] ) ) {
					$path = $this->resolve_mask_stored_value_to_path( (string) $payload['playground_snapshot_file'] );
				}
			}

			if ( '' === $path || ! is_readable( $path ) ) {
				continue;
			}

			if ( 0 !== strpos( $path, $quiz_dir ) ) {
				continue;
			}

			$paths[] = $path;
		}

		return array_values( array_unique( $paths ) );
	}

	/**
	 * Resolve a stored draw/pin/puzzle mask value to local file path.
	 *
	 * Supports basename, uploads-relative path, uploads URL, and absolute uploads path.
	 *
	 * @since 4.0.0
	 *
	 * @param string $stored Stored mask value from answer_two_gap_match.
	 *
	 * @return string
	 */
	private function resolve_mask_stored_value_to_path( $stored ) {
		$stored = is_string( $stored ) ? trim( stripslashes( $stored ) ) : '';
		$stored = trim( $stored, "\"' \t\n\r\0\x0B" );
		$stored = str_replace( '\\/', '/', $stored );

		if ( '' === $stored ) {
			return '';
		}

		$path = QuizImageStorage::quiz_image_stored_value_to_path( $stored );
		if ( '' !== $path && is_file( $path ) && is_readable( $path ) ) {
			return $path;
		}

		$basename = QuizImageStorage::sanitize_quiz_image_filename( wp_basename( str_replace( '\\', '/', $stored ) ) );
		if ( '' !== $basename ) {
			$path = QuizImageStorage::quiz_image_stored_value_to_path( $basename );
			if ( '' !== $path && is_file( $path ) && is_readable( $path ) ) {
				return $path;
			}
		}

		$upload_dir = wp_upload_dir();
		if ( ! empty( $upload_dir['error'] ) ) {
			return '';
		}

		$uploads_base_dir = trailingslashit( str_replace( '\\', '/', (string) $upload_dir['basedir'] ) );
		$uploads_marker   = '/wp-content/uploads/';

		$url_path   = wp_parse_url( $stored, PHP_URL_PATH );
		$url_path   = is_string( $url_path ) ? $url_path : '';
		$marker_pos = '' !== $url_path ? strpos( $url_path, $uploads_marker ) : false;
		if ( false !== $marker_pos ) {
			$relative = ltrim( substr( $url_path, $marker_pos + strlen( $uploads_marker ) ), '/' );
			$relative = QuizImageStorage::normalize_uploads_relative_store_value( $relative );
			if ( '' !== $relative ) {
				$resolved = $uploads_base_dir . $relative;
				if ( is_file( $resolved ) && is_readable( $resolved ) ) {
					return $resolved;
				}
			}
		}

		$is_abs_path = '/' === substr( $stored, 0, 1 );
		if ( ! $is_abs_path ) {
			return '';
		}

		$marker_pos = strpos( $stored, $uploads_marker );
		if ( false === $marker_pos ) {
			return '';
		}

		$relative = ltrim( substr( $stored, $marker_pos + strlen( $uploads_marker ) ), '/' );
		$relative = QuizImageStorage::normalize_uploads_relative_store_value( $relative );
		if ( '' === $relative ) {
			return '';
		}

		$resolved = $uploads_base_dir . $relative;
		return ( is_file( $resolved ) && is_readable( $resolved ) ) ? $resolved : '';
	}

	/**
	 * Collect temp draw/pin/puzzle file paths from unsaved question deletions.
	 *
	 * @since 4.0.0
	 *
	 * @param string[] $stored_values Stored values coming from quiz builder payload.
	 *
	 * @return string[]
	 */
	private function get_temp_mask_file_paths_for_deletion( array $stored_values ) {
		$paths = array();

		if ( empty( $stored_values ) || ! class_exists( '\TUTOR_PRO\QuizImageStorage' ) ) {
			return $paths;
		}

		$upload_dir = wp_upload_dir();
		if ( ! empty( $upload_dir['error'] ) ) {
			return $paths;
		}

		$quiz_dir = trailingslashit( $upload_dir['basedir'] ) . QuizImageStorage::QUIZ_IMAGES_SUBDIR . '/';

		foreach ( $stored_values as $stored ) {
			$stored = is_string( $stored ) ? trim( $stored ) : '';
			if ( '' === $stored ) {
				continue;
			}

			$path = $this->resolve_mask_stored_value_to_path( $stored );

			if ( '' === $path && ( false !== strpos( $stored, '{' ) || false !== strpos( $stored, 'playground_snapshot_file' ) ) ) {
				$payload = json_decode( stripslashes( $stored ), true );
				if ( is_array( $payload ) && ! empty( $payload['playground_snapshot_file'] ) ) {
					$path = $this->resolve_mask_stored_value_to_path( (string) $payload['playground_snapshot_file'] );
				}
			}

			if ( '' === $path || ! is_readable( $path ) ) {
				continue;
			}

			if ( 0 !== strpos( $path, $quiz_dir ) ) {
				continue;
			}

			$paths[] = $path;
		}

		return array_values( array_unique( $paths ) );
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
		if ( ! tutor_utils()->can_user_manage( 'topic', $topic_id ) ) {
			$validation->success              = false;
			$validation->errors['topic_id'][] = tutor_utils()->error_message();
		}

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
			$deleted_question_ids     = Input::post( 'deleted_question_ids', array(), Input::TYPE_ARRAY );
			$deleted_answer_ids       = Input::post( 'deleted_answer_ids', array(), Input::TYPE_ARRAY );
			$deleted_temp_mask_values = Input::post( 'deleted_temp_mask_values', array(), Input::TYPE_ARRAY );
			$this->handle_delete( $deleted_question_ids, $deleted_answer_ids, $deleted_temp_mask_values );

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
