<?php
/**
 * Attempt details page
 *
 * @package Tutor\Views
 * @subpackage Tutor\Quiz
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Tutor\Models\QuizModel;
use Tutor\Components\SvgIcon;
use TUTOR\Icon;

$enabled_hide_quiz_details = tutor_utils()->get_option( 'hide_quiz_details' );
if ( ! is_admin() && ! current_user_can( 'tutor_instructor' ) && true === $enabled_hide_quiz_details ) {
	exit;
}

//phpcs:ignore
extract( $data ); // $user_id, $attempt_id, $attempt_data(nullable), $context(nullable)

! isset( $attempt_data ) ? $attempt_data = tutor_utils()->get_attempt( $attempt_id ) : 0;
! isset( $context ) ? $context           = null : 0;

if ( ! $attempt_id || ! $attempt_data || $user_id != $attempt_data->user_id ) {
	tutor_utils()->tutor_empty_state( __( 'Attempt not found or access permission denied', 'tutor' ) );
	return;
}

if ( isset( $user_id ) && $user_id > 0 ) {
	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return;
	}
}

/**
 * Render answer list
 *
 * @param array   $answers answers.
 * @param boolean $dump_data dump data.
 *
 * @return void
 */
function tutor_render_answer_list( $answers = array(), $dump_data = false ) {
	if ( ! empty( $answers ) ) {

		echo '<div class="correct-answer-wrap">';

			$multi_texts = array();
		foreach ( $answers as $key => $ans ) {
			$type = isset( $ans->answer_view_format ) ? $ans->answer_view_format : 'text_image';

			if ( ! empty( $ans->answer_two_gap_match ) ) {
				echo '<div class="matching-type">';
			}

			switch ( $type ) {
				case 'text_image':
					echo '<div class="text-image-type tutor-mb-4">';
					if ( isset( $ans->image_id ) ) {
						$img_url = wp_get_attachment_image_url( $ans->image_id );
						if ( $img_url ) {
							echo '<span class="image"><img src="' . esc_url( $img_url ) . '" /></span>';
						}
					}
					if ( isset( $ans->answer_title ) ) {
						echo '<span class="caption">' . esc_html( stripslashes( $ans->answer_title ) ) . '</span>';
					}
						echo '</div>';
					break;

				case 'text':
					$ans_string = '<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">'
							. esc_html( stripslashes( $ans->answer_title ) ) .
						'</span>';

					if ( isset( $ans->answer_title ) && empty( $ans->answer_two_gap_match ) ) {
						$multi_texts[ $ans->answer_title ] = $ans_string;
					} else {
						echo $ans_string; //phpcs:ignore -- contain safe data
					}
					break;

				case 'image':
					echo '<div class="image-type">';
					if ( isset( $ans->image_id ) ) {
						$img_url = wp_get_attachment_image_url( $ans->image_id );
						if ( $img_url ) {
							echo '
                            <span class="image">
                            <img src="' . esc_url( $img_url ) . '" />
                            <span>';
						}
					}
						echo '</div>';
					break;
			}

			if ( ! empty( $ans->answer_two_gap_match ) ) {
					echo '<div class="image-match">' . esc_html( stripslashes( $ans->answer_two_gap_match ) ) . '</div>';
				echo '</div>';
			}
		}
            //phpcs:ignore
			echo count( $multi_texts ) ? implode( ', ', $multi_texts ) : '';

		echo '</div>';
	}
}

/**
 * Render fill in the blank answer
 *
 * @param mixed $get_db_answers_by_question get db answers by question.
 * @param mixed $answer_titles ans titles.
 *
 * @return void
 */
function tutor_render_fill_in_the_blank_answer( $get_db_answers_by_question, $answer_titles ) {

	$spaces = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

	// Loop through the answers.
	foreach ( $get_db_answers_by_question as $db_answer ) {
		$count_dash_fields = substr_count( $db_answer->answer_title, '{dash}' );

		if ( $count_dash_fields ) {
			$dash_string = array();
			$input_data  = array();
			for ( $i = 0; $i < $count_dash_fields; $i++ ) {
				$ans_title    = ( ! empty( $answer_titles[ $i ] ) && ! ctype_space( $answer_titles[ $i ] ) ) ? $answer_titles[ $i ] : null;
				$input_data[] = $ans_title ? "<span class='filled_dash_unser'>{$ans_title}</span>" : $spaces;
			}

			$answer_title = $db_answer->answer_title;

			foreach ( $input_data as $index => $replace ) {
				$replace      = '<span style="text-decoration:underline;">' . $replace . '</span>';
				$answer_title = preg_replace( '/{dash}/i', $replace, $answer_title, 1 );
			}
			echo wp_kses(
				str_replace( '{dash}', "<span class='filled_dash_unser'>{$spaces}</span>", stripslashes( $answer_title ) ),
				array(
					'span' => array(
						'style' => true,
						'class' => true,
					),
				)
			);
		}
	}
}

/**
 * Render question type icon using v4 icon component.
 *
 * @since 4.0.0
 *
 * @param string $question_type Question type slug.
 *
 * @return string
 */
if ( ! function_exists( 'tutor_render_question_type_icon' ) ) {
	/**
	 * Render question type icon using v4 icon component.
	 *
	 * @since 4.0.0
	 *
	 * @param string $question_type Question type slug.
	 *
	 * @return string
	 */
	function tutor_render_question_type_icon( $question_type ) {
		$normalized_type = (string) $question_type;

		if ( 'single_choice' === $normalized_type ) {
			$normalized_type = 'multiple_choice';
		}

		if ( 'image_matching' === $normalized_type ) {
			$normalized_type = 'matching';
		}

		$question_type_icon_map = array(
			'true_false'        => Icon::QUIZ_TRUE_FALSE,
			'multiple_choice'   => Icon::QUIZ_MULTI_CHOICE,
			'open_ended'        => Icon::QUIZ_ESSAY,
			'fill_in_the_blank' => Icon::QUIZ_FILL_IN_THE_BLANKS,
			'short_answer'      => Icon::QUIZ_SHORT_ANSWER,
			'matching'          => Icon::QUIZ_IMAGE_MATCHING,
			'image_answering'   => Icon::QUIZ_IMAGE_ANSWER,
			'ordering'          => Icon::QUIZ_ORDERING,
			'draw_image'        => Icon::QUIZ_MARK_IN_THE_IMAGE,
			'scale'             => Icon::QUIZ_RANGE,
			'pin_image'         => Icon::QUIZ_PIN,
			'puzzle'            => Icon::QUIZ_PUZZLE,
			'coordinates'       => Icon::QUIZ_GRAPH,
			'h5p'               => Icon::QUIZ_H5P,
		);

		return $question_type_icon_map[ $normalized_type ] ?? '';
	}
}

// Prepare student data.
if ( ! isset( $user_data ) ) {
	$user_data = get_userdata( $user_id );
}

//phpcs:ignore
extract( QuizModel::get_quiz_attempt_timing( $attempt_data ) ); // $attempt_duration, $attempt_duration_taken;

// Prepare the correct/incorrect answer count for the first summary table.
$answers       = QuizModel::get_quiz_answers_by_attempt_id( $attempt_id );
$answer_counts = QuizModel::get_attempt_answer_counts( $answers );
$correct       = $answer_counts['correct'];
$incorrect     = $answer_counts['incorrect'];

// Prepare the column list for the first summary table.
$page_key        = 'attempt-details-summary';
$table_1_columns = include __DIR__ . '/contexts.php';

// Prepare the column list for the second table (eery single answer list).
$page_key        = 'attempt-details-answers';
$table_2_columns = include __DIR__ . '/contexts.php';

require __DIR__ . '/header.php';

$attempt_info = @unserialize( $attempt_data->attempt_info );

if ( is_array( $attempt_info ) ) {
	$attempt_type = '';
	// Allowed duration.
	if ( isset( $attempt_info['time_limit'] ) ) {
		$attempt_duration = tutor_utils()->second_to_formated_time( $attempt_info['time_limit']['time_limit_seconds'], $attempt_info['time_limit']['time_type'] );
	}
	if ( 'days' === $attempt_info['time_limit']['time_type'] ) {
		$attempt_type = 'hours';
	}
	if ( 'hours' === $attempt_info['time_limit']['time_type'] ) {
		$attempt_type = 'minutes';
	}
	if ( 'minutes' === $attempt_info['time_limit']['time_type'] ) {
		$attempt_type = 'minutes';
	}

	// Taken duration.
	$seconds                = strtotime( $attempt_data->attempt_ended_at ) - strtotime( $attempt_data->attempt_started_at );
	$attempt_duration_taken = tutor_utils()->second_to_formated_time( $seconds, $attempt_type );
}
?>

<?php echo is_admin() ? '<div class="tutor-admin-body">' : ''; ?>
<div class="tutor-table-responsive tutor-table-mobile tutor-mb-32">
	<table class="tutor-table tutor-quiz-attempt-details">
		<thead>
			<tr>
				<?php foreach ( $table_1_columns as $key => $column ) : ?>
					<th><?php echo $column; //phpcs:ignore --contain safe data ?></th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<tbody>
			<tr>
				<?php foreach ( $table_1_columns as $key => $column ) : ?>
					<td data-title="<?php echo esc_attr( $column ); ?>">
						<?php if ( 'user' == $key ) : ?>
							<div class="tutor-d-flex tutor-align-center">
								<?php
								echo wp_kses(
									tutor_utils()->get_tutor_avatar( $user_id ),
									tutor_utils()->allowed_avatar_tags()
								);
								?>
								<div class="tutor-ml-16">
									<div>
										<?php
										echo esc_html(
											$user_data ? $user_data->display_name : ''
										);
										?>
									</div>
									<a href="<?php echo esc_url( tutor_utils()->profile_url( $user_id, false ) ); ?>" class="tutor-iconic-btn">
										<span class="tutor-icon-external-link"></span>
									</a>
								</div>
							</div>

						<?php elseif ( 'date' == $key ) : ?>
							<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $attempt_data->attempt_started_at ) ) ); ?>
						<?php elseif ( 'qeustion_count' === $key ) : ?>
							<?php echo esc_html( $attempt_data->total_questions ); ?>
						<?php elseif ( 'quiz_time' === $key ) : ?>
							<?php echo esc_html( $attempt_duration ); ?>
						<?php elseif ( 'attempt_time' === $key ) : ?>
							<?php echo esc_html( $attempt_duration_taken ); ?>
						<?php elseif ( 'total_marks' === $key ) : ?>
							<?php echo esc_html( $attempt_data->total_marks ); ?>
						<?php elseif ( 'pass_marks' === $key ) : ?>
							<?php
								$pass_marks = ( $total_marks * $passing_grade ) / 100;
								echo esc_html( number_format_i18n( $pass_marks, 2 ) );

								$pass_mark_percent = $passing_grade;
								echo esc_html( ' (' . $pass_mark_percent . '%)' );
							?>
						<?php elseif ( 'correct_answer' === $key ) : ?>
							<?php echo esc_html( $correct ); ?>
						<?php elseif ( 'incorrect_answer' === $key ) : ?>
							<?php echo esc_html( $incorrect ); ?>
						<?php elseif ( 'earned_marks' === $key ) : ?>
							<?php
							$earned_percentage = QuizModel::calculate_attempt_earned_percentage( $attempt_data );
							echo esc_html( $attempt_data->earned_marks );
							echo esc_html( ' (' . $earned_percentage . '%)' );
							?>
						<?php elseif ( 'result' === $key ) : ?>
							<?php
							$attempt_result = QuizModel::get_attempt_result( $attempt_data->attempt_id );

							if ( QuizModel::RESULT_PENDING === $attempt_result ) {
								echo '<span class="tutor-badge-label label-warning">' . esc_html__( 'Pending', 'tutor' ) . '</span>';
							} elseif ( QuizModel::RESULT_PASS === $attempt_result ) {
								echo '<span class="tutor-badge-label label-success">' . esc_html__( 'Pass', 'tutor' ) . '</span>';
							} else {
								echo '<span class="tutor-badge-label label-danger">' . esc_html__( 'Fail', 'tutor' ) . '</span>';
							}
							?>
						<?php endif; ?>
					</td>
				<?php endforeach; ?>
			</tr>
		</tbody>
	</table>
</div>

<?php
// instructor feedback.
global $wp_query;
$query_vars            = $wp_query->query_vars;
$page_name             = isset( $query_vars['tutor_dashboard_page'] ) ? $query_vars['tutor_dashboard_page'] : '';
$attempt_info          = maybe_unserialize( $attempt_data->attempt_info );
$feedback              = is_array( $attempt_info ) && isset( $attempt_info['instructor_feedback'] ) ? $attempt_info['instructor_feedback'] : '';
$question_feedback_map = is_array( $attempt_info ) && isset( $attempt_info['question_feedback'] ) && is_array( $attempt_info['question_feedback'] ) ? $attempt_info['question_feedback'] : array();
$manual_overrides_map  = is_array( $attempt_info ) && isset( $attempt_info['manual_overrides'] ) && is_array( $attempt_info['manual_overrides'] ) ? $attempt_info['manual_overrides'] : array();
$is_student_context   = in_array( $context, array( 'course-single-previous-attempts', 'frontend-dashboard-my-attempts' ), true );
$is_instructor_review = ! $is_student_context && (
	'frontend-dashboard-students-attempts' === $context ||
	'backend-dashboard-students-attempts' === $context ||
	( is_admin() && empty( $context ) )
) && tutor_utils()->can_user_manage( 'attempt', $attempt_id );
// don't show on instructor quiz attempt since below already have feedback box area.
if ( '' !== $feedback && 'my-quiz-attempts' === $page_name ) {
	?>
	<div class="tutor-quiz-attempt-note tutor-instructor-note tutor-my-32 tutor-py-20 tutor-px-24 tutor-py-sm-32 tutor-px-sm-36">
		<div class="tutor-in-title tutor-fs-6 tutor-fw-medium tutor-color-black">
			<?php esc_html_e( 'Instructor Note', 'tutor' ); ?>					
		</div>
		<div class="tutor-in-body tutor-fs-6 tutor-color-secondary tutor-pt-12 tutor-pt-sm-16">
			<?php echo wp_kses_post( $feedback ); ?>					
		</div>
	</div>
<?php } ?>

<?php
$answers = apply_filters( 'tutor_filter_attempt_answers', $answers );
$answers = QuizModel::filter_attempt_answers_for_details( $answers, $is_instructor_review );

if ( is_array( $answers ) && count( $answers ) ) {
	// Filter out not needed columns based on question type.
	$table_2_columns = apply_filters( 'tutor_filter_attempt_answer_column', $table_2_columns, $answers );
	echo 'course-single-previous-attempts' !== $context ? '<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mt-24">' . esc_html__( 'Quiz Overview', 'tutor' ) . '</div>' : '';
	?>
		<div class="tutor-table-responsive tutor-table-mobile tutor-mt-16">
			<table class="tutor-table tutor-quiz-attempt-details tutor-mb-32 tutor-table-data-td-target">
				<thead>
					<tr>
					<?php foreach ( $table_2_columns as $key => $column ) : ?>
							<th><?php echo $column; //phpcs:ignore --contain safe data ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>

				<tbody>
				<?php
					$answer_i = 0;
				foreach ( $answers as $answer ) {
					++$answer_i;
					$question_type     = QuizModel::get_question_types( $answer->question_type );
					$question_settings = maybe_unserialize( $answer->question_settings );
					$is_image_matching = isset( $question_settings['is_image_matching'] ) && '1' === $question_settings['is_image_matching'];
					$answer_status     = QuizModel::get_attempt_answer_status( $answer );

					// Allow Pro and add-ons to set answer status for custom question types.
					/**
					 * Filter to set answer status for custom question types.
					 * Pro handles draw_image via this filter.
					 *
					 * @param string|null $answer_status Current answer status (null if not set).
					 * @param object      $answer        Answer object.
					 *
					 * @return string|null Answer status or null to use default.
					 */
					$custom_status = apply_filters( 'tutor_quiz_answer_status_for_question_type', null, $answer );
					if ( null !== $custom_status ) {
						$answer_status = $custom_status;
					}

					$student_q_feedback = trim( (string) ( $question_feedback_map[ $answer->attempt_answer_id ] ?? ( $question_feedback_map[ $answer->question_id ] ?? '' ) ) );
					$feedback_dom_id    = 'tutor-question-feedback-' . ( ! empty( $answer->attempt_answer_id ) ? $answer->attempt_answer_id : $answer->question_id );
					?>

							<tr class="tutor-quiz-answer-status-<?php echo esc_html( $answer_status ); ?>">
						<?php foreach ( $table_2_columns as $key => $column ) : ?>
									<?php
									switch ( $key ) {
										case 'no':
											?>
												<td class="no" data-title="<?php echo esc_attr( $column ); ?>">
													<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
													<?php echo esc_html( $answer_i ); ?>
													</span>
												</td>
												<?php
											break;

										case 'type':
											?>
												<td class="type" data-title="<?php echo esc_attr( $column ); ?>">
													<div class="tooltip-wrap tutor-d-inline-flex tutor-align-center">
														<?php
														$question_icon_name = tutor_render_question_type_icon( $answer->question_type );
														if ( ! empty( $question_icon_name ) ) {
															SvgIcon::make()
																->name( $question_icon_name )
																->size( 32 )
																->render();
														}
														?>
														<span class="tooltip-txt tooltip-top">
															<?php echo esc_html( QuizModel::get_question_types( $answer->question_type )['name'] ?? '' ); ?>
														</span>
													</div>
												</td>
												<?php
											break;

										case 'questions':
											?>
												<td class="questions" data-title="<?php echo esc_attr( $column ); ?>">
													<span class="tutor-fs-7 tutor-fw-medium tutor-d-flex tutor-align-center">
													<?php echo esc_html( stripslashes( $answer->question_title ) ); ?>
													</span>
												</td>
												<?php
											break;

										case 'given_answer':
											?>
												<td class="given-answer" data-title="<?php echo esc_attr( $column ); ?>">
												<div>
												<?php
													// Single choice.
												if ( 'single_choice' === $answer->question_type ) {
													$get_answers = tutor_utils()->get_answer_by_id( $answer->given_answer );
													tutor_render_answer_list( $get_answers );
												}


													// True false or single choice.
												if ( 'true_false' === $answer->question_type ) {
													$get_answers   = tutor_utils()->get_answer_by_id( $answer->given_answer );
													$answer_titles = wp_list_pluck( $get_answers, 'answer_title' );
													$answer_titles = array_map( 'stripslashes', $answer_titles );

													echo '<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">' .
															implode( '</p><p>', $answer_titles ) .  //phpcs:ignore
														'</span>';
												}

												// Multiple choice.
												elseif ( 'multiple_choice' === $answer->question_type ) {
													$get_answers = tutor_utils()->get_answer_by_id( maybe_unserialize( $answer->given_answer ) );
													tutor_render_answer_list( $get_answers );
												}

													// Fill in the blank.
												elseif ( 'fill_in_the_blank' === $answer->question_type ) {
													$answer_titles              = maybe_unserialize( $answer->given_answer );
													$get_db_answers_by_question = QuizModel::get_answers_by_quiz_question( $answer->question_id );

													echo tutor_render_fill_in_the_blank_answer( $get_db_answers_by_question, $answer_titles ); //phpcs:ignore --contain safe data
												}

													// Open ended or short answer.
												elseif ( 'open_ended' === $answer->question_type || 'short_answer' === $answer->question_type ) {
													if ( $answer->given_answer ) {
														echo wp_kses(
															wpautop( stripslashes( $answer->given_answer ) ),
															array(
																'p' => true,
																'span' => true,
															)
														);
													}
												}

													// Ordering.
												elseif ( 'ordering' === $answer->question_type ) {
													$ordering_ids = maybe_unserialize( $answer->given_answer );
													foreach ( $ordering_ids as $ordering_id ) {
														$get_answers = tutor_utils()->get_answer_by_id( $ordering_id );
														tutor_render_answer_list( $get_answers );
													}
												}

												// Matching.
												elseif ( 'matching' === $answer->question_type ) {

													$ordering_ids           = maybe_unserialize( $answer->given_answer );
													$original_saved_answers = QuizModel::get_answers_by_quiz_question( $answer->question_id );

													$answers = array();

													foreach ( $original_saved_answers as $key => $original_saved_answer ) {
														$provided_answer_order_id = isset( $ordering_ids[ $key ] ) ? $ordering_ids[ $key ] : 0;
														$provided_answer_order    = tutor_utils()->get_answer_by_id( $provided_answer_order_id );
														if ( tutor_utils()->count( $provided_answer_order ) ) {
															foreach ( $provided_answer_order as $provided_answer_order ) {
																if ( $is_image_matching ) {
																	$original_saved_answer->answer_view_format   = 'text_image';
																	$original_saved_answer->answer_title         = $provided_answer_order->answer_title;
																	$original_saved_answer->answer_two_gap_match = '';
																	$answers[]                                   = $original_saved_answer;
																} else {
																	$original_saved_answer->answer_two_gap_match = $provided_answer_order->answer_two_gap_match;
																	$answers[]                                   = $original_saved_answer;
																}
															}
														}
													}

													tutor_render_answer_list( $answers );
												} elseif ( 'image_matching' === $answer->question_type ) {

													$ordering_ids           = maybe_unserialize( $answer->given_answer );
													$original_saved_answers = QuizModel::get_answers_by_quiz_question( $answer->question_id );

													$answers = array();

													foreach ( $original_saved_answers as $key => $original_saved_answer ) {
														$provided_answer_order_id = isset( $ordering_ids[ $key ] ) ? $ordering_ids[ $key ] : 0;
														$provided_answer_order    = tutor_utils()->get_answer_by_id( $provided_answer_order_id );
														foreach ( $provided_answer_order as $p_answer ) {
															if ( $p_answer->answer_title ) {
																$original_saved_answer->answer_view_format = 'text_image';
																$original_saved_answer->answer_title       = $p_answer->answer_title;
																$answers[]                                 = $original_saved_answer;
															}
														}
													}

													tutor_render_answer_list( $answers );
												} elseif ( 'image_answering' === $answer->question_type ) {

													$ordering_ids = maybe_unserialize( $answer->given_answer );

													$answers = array();

													foreach ( $ordering_ids as $answer_id => $image_answer ) {
														$db_answers = tutor_utils()->get_answer_by_id( $answer_id );
														foreach ( $db_answers as $db_answer ) {
														}
														$db_answer->answer_title       = $image_answer;
														$db_answer->answer_view_format = 'text_image';
														$answers[]                     = $db_answer;

													}

													tutor_render_answer_list( $answers );
												} else {
													/**
													 * Allow Pro and add-ons to render given answer for custom question types.
													 * Pro handles draw_image and pin_image via this action.
													 *
													 * @param object $answer Answer object.
													 */
													do_action( 'tutor_quiz_render_given_answer_for_question_type', $answer );
												}
												?>
												</div>
												</td>
												<?php
											break;

										case 'correct_answer':
											?>
												<td class="correct-answer" data-title="<?php echo esc_attr( $column ); ?>">
												<div>
												<?php
												if ( ( $answer->question_type != 'open_ended' && $answer->question_type != 'short_answer' ) ) {

													global $wpdb;

													// True false.
													if ( 'true_false' === $answer->question_type ) {
														$correct_answer = $wpdb->get_var(
															$wpdb->prepare(
																"SELECT answer_title FROM {$wpdb->prefix}tutor_quiz_question_answers
                                                                WHERE belongs_question_id = %d
                                                                    AND belongs_question_type='true_false'
                                                                    AND is_correct = 1",
																$answer->question_id
															)
														);

														echo '<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">' .
																esc_html( $correct_answer ) .
															'</span>';
													}

													// Single choice.
													elseif ( 'single_choice' === $answer->question_type ) {
														$correct_answer = $wpdb->get_results(
															$wpdb->prepare(
																"SELECT answer_title, image_id, answer_view_format
                                                                FROM {$wpdb->prefix}tutor_quiz_question_answers
                                                                WHERE belongs_question_id = %d
                                                                    AND belongs_question_type='single_choice' AND 
                                                                    is_correct = 1",
																$answer->question_id
															)
														);

														tutor_render_answer_list( $correct_answer );
													}

													// Multiple choice.
													elseif ( 'multiple_choice' === $answer->question_type ) {
														$correct_answer = $wpdb->get_results(
															$wpdb->prepare(
																"SELECT answer_title, image_id, answer_view_format
                                                                FROM {$wpdb->prefix}tutor_quiz_question_answers
                                                                WHERE belongs_question_id = %d
                                                                    AND belongs_question_type='multiple_choice'
                                                                    AND is_correct = 1 ;",
																$answer->question_id
															)
														);

														tutor_render_answer_list( $correct_answer );
													}

													// Fill in the blanks.
													elseif ( 'fill_in_the_blank' === $answer->question_type ) {
														$correct_answer = $wpdb->get_var(
															$wpdb->prepare(
																"SELECT answer_two_gap_match FROM {$wpdb->prefix}tutor_quiz_question_answers
                                                                WHERE belongs_question_id = %d
                                                                    AND belongs_question_type='fill_in_the_blank'",
																$answer->question_id
															)
														);

														$answer_titles              = explode( '|', stripslashes( $correct_answer ) );
														$get_db_answers_by_question = QuizModel::get_answers_by_quiz_question( $answer->question_id );

														echo tutor_render_fill_in_the_blank_answer( $get_db_answers_by_question, $answer_titles ); //phpcs:ignore --contain safe data
													}

													// Ordering.
													elseif ( 'ordering' === $answer->question_type ) {
														$correct_answer = $wpdb->get_results(
															$wpdb->prepare(
																"SELECT answer_title, image_id, answer_view_format
                                                                FROM {$wpdb->prefix}tutor_quiz_question_answers
                                                                WHERE belongs_question_id = %d
                                                                    AND belongs_question_type='ordering'
                                                                ORDER BY answer_order ASC;",
																$answer->question_id
															)
														);

														foreach ( $correct_answer as $ans ) {
															tutor_render_answer_list( array( $ans ) );
														}
													}

													// Matching.
													elseif ( 'matching' === $answer->question_type ) {
														$correct_answer = $wpdb->get_results(
															$wpdb->prepare(
																"SELECT answer_title, image_id, answer_two_gap_match, answer_view_format
                                                                FROM {$wpdb->prefix}tutor_quiz_question_answers
                                                                WHERE belongs_question_id = %d
                                                                    AND belongs_question_type='matching'
                                                                ORDER BY answer_order ASC;",
																$answer->question_id
															)
														);

														if ( $is_image_matching ) {
															array_map(
																function ( $ans ) {
																	$ans->answer_view_format   = 'text_image';
																	$ans->answer_two_gap_match = '';
																},
																$correct_answer
															);
														}

														tutor_render_answer_list( $correct_answer );
													}

													// Image matching.
													elseif ( 'image_matching' === $answer->question_type ) {
														$correct_answer = $wpdb->get_results(
															$wpdb->prepare(
																"SELECT answer_title, image_id, answer_two_gap_match
                                                                FROM {$wpdb->prefix}tutor_quiz_question_answers
                                                                WHERE belongs_question_id = %d
                                                                    AND belongs_question_type='image_matching'
                                                                ORDER BY answer_order ASC;",
																$answer->question_id
															)
														);

														tutor_render_answer_list( $correct_answer, true );
													}

													// Image Answering.
													elseif ( 'image_answering' === $answer->question_type ) {

														$correct_answer = $wpdb->get_results(
															$wpdb->prepare(
																"SELECT answer_title, image_id, answer_two_gap_match
                                                                FROM {$wpdb->prefix}tutor_quiz_question_answers
                                                                WHERE belongs_question_id = %d
                                                                    AND belongs_question_type='image_answering'
                                                                ORDER BY answer_order ASC;",
																$answer->question_id
															)
														);

														! is_array( $correct_answer ) ? $correct_answer = array() : 0;

														echo '<div class="answer-image-matched-wrap">';
														foreach ( $correct_answer as $image_answer ) {
															?>
																	<div class="image-matching-item">
																		<p class="dragged-img-rap"><img src="<?php echo esc_url( wp_get_attachment_image_url( $image_answer->image_id ) ); ?>" /> </p>
																		<p class="dragged-caption"><?php echo esc_html( $image_answer->answer_title ); ?></p>
																	</div>
																<?php
														}
														echo '</div>';
													} else {
														/**
														 * Allow Pro and add-ons to render correct answer for custom question types.
														 * Pro handles draw_image and pin_image via this action.
														 *
														 * @param object $answer Answer object.
														 */
														do_action( 'tutor_quiz_render_correct_answer_for_question_type', $answer );
													}
												}
												?>
												</div>
												</td>
												<?php
											break;

										case 'result':
											?>
												<td class="result" data-title="<?php echo esc_attr( $column ); ?>">
													<div class="tutor-quiz-attempt-result-col">
														<div class="tutor-quiz-result-wrap">
															<?php do_action( 'tutor_quiz_attempt_after_result_column', $answer, $answer_status ); ?>

															<?php
															if ( 'h5p' !== $answer->question_type ) {
																$achieved_val  = (float) ( $answer->achieved_mark ?? 0 );
																$question_mark = (float) ( $answer->question_mark ?? 0 );
																$qmark_str     = ( floor( $question_mark ) === $question_mark ) ? (string) (int) $question_mark : (string) round( $question_mark, 2 );
																$achieved_str  = (string) round( $achieved_val, 2 );
																if ( floor( $achieved_val ) === $achieved_val ) {
																	$achieved_str = number_format( $achieved_val, 1, '.', '' );
																}
																$score_label = sprintf(
																	/* translators: 1: achieved marks, 2: total marks. */
																	esc_html__( 'Score: %1$s/%2$s', 'tutor' ),
																	$achieved_str,
																	$qmark_str
																);

																$badge_info  = QuizModel::get_attempt_answer_badge( $answer );
																$badge_class = $badge_info['class'] ?? 'label-default';

																echo '<span class="tutor-badge-label ' . esc_attr( $badge_class ) . '">' . esc_html( $badge_info['label'] ?? '' ) . '</span>';

																do_action( 'tutor_quiz_attempt_details_result_badge_after', $answer, $answer_status);

																if ( 'pending' !== $answer_status && 'skipped' !== $answer_status ) {
																	echo '<div class="tutor-quiz-result-score">' . esc_html( $score_label ) . '</div>';
																}
															}
															?>
														</div>

														<div class="tutor-d-flex tutor-align-center tutor-gap-1">
															<?php do_action( 'tutor_quiz_attempt_details_after_result', $answer, $answer_status ); ?>

															<?php if ( ! $is_instructor_review && '' !== $student_q_feedback ) : ?>
																<div class="tooltip-wrap">
																	<span class="tooltip-txt <?php echo esc_attr( is_rtl() ? 'tooltip-right' : 'tooltip-left' ); ?>"><?php esc_html_e( 'Show Feedback', 'tutor' ); ?></span>
																	<button type="button" class="tutor-quiz-feedback-toggle-button" data-td-target="<?php echo esc_attr( $feedback_dom_id ); ?>" aria-label="<?php esc_attr_e( 'Show Feedback', 'tutor' ); ?>">
																		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0049F8" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
																			<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
																		</svg>
																	</button>
																</div>
															<?php endif; ?>
														</div>
													</div>
												</td>
												<?php
											break;

										case 'manual_review':
											?>
												<td class="tutor-text-center tutor-nowrap-ellipsis" data-title="<?php echo esc_attr( $column ); ?>">
													<div class="tutor-manual-review-wrapper">
													<?php if ( in_array( $answer->question_type, QuizModel::get_manual_review_types(), true ) && 'skipped' !== $answer_status ) : ?>
														<div class="tutor-d-inline-flex tutor-align-center tutor-justify-center">
															<input class="tutor-form-control tutor-form-control-sm quiz-manual-mark-input" style="width: 72px; height: 32px; text-align: center;" type="number" min="0" max="<?php echo esc_attr( (string) $answer->question_mark ); ?>" step="0.01" value="<?php echo esc_attr( (string) $answer->achieved_mark ); ?>" aria-label="<?php esc_attr_e( 'Obtained marks', 'tutor' ); ?>" />
															<span class="tutor-fs-7 tutor-color-muted tutor-ml-4">/ <?php echo esc_html( (string) $answer->question_mark ); ?></span>
															<a href="javascript:;" data-back-url="<?php echo esc_url( $back_url ); ?>" data-attempt-id="<?php echo esc_attr( $attempt_id ); ?>" data-attempt-answer-id="<?php echo esc_attr( $answer->attempt_answer_id ); ?>" data-question-id="<?php echo esc_attr( $answer->question_id ); ?>" data-context="<?php echo esc_attr( $context ); ?>" title="<?php esc_attr_e( 'Save mark', 'tutor' ); ?>" class="quiz-manual-mark-save tutor-ml-8 tutor-icon-rounded tutor-color-success">
																<i class="tutor-icon-mark"></i>
															</a>
														</div>
														<?php if ( $is_instructor_review ) : ?>
															<?php
															$has_question_feedback = '' !== trim( (string) ( $question_feedback_map[ $answer->attempt_answer_id ] ?? '' ) );
															?>
															<div class="tutor-mt-4">
																<a href="javascript:;" data-attempt-id="<?php echo esc_attr( $attempt_id ); ?>" data-attempt-answer-id="<?php echo esc_attr( $answer->attempt_answer_id ); ?>" data-question-id="<?php echo esc_attr( $answer->question_id ); ?>" data-feedback="<?php echo esc_attr( $has_question_feedback ? $question_feedback_map[ $answer->attempt_answer_id ] : '' ); ?>" title="<?php echo $has_question_feedback ? esc_attr__( 'Show feedback', 'tutor' ) : esc_attr__( 'Add feedback', 'tutor' ); ?>" class="quiz-question-feedback-action tutor-fs-7 tutor-text-primary">
																	<span class="<?php echo $has_question_feedback ? 'tutor-icon-eye-line' : 'tutor-icon-comment'; ?> tutor-mr-4"></span><?php echo $has_question_feedback ? esc_html__( 'Show Feedback', 'tutor' ) : esc_html__( 'Add Feedback', 'tutor' ); ?>
																</a>
															</div>
														<?php endif; ?>
													<?php elseif ( 'skipped' !== $answer_status ) : ?>
														<a href="javascript:;" data-back-url="<?php echo esc_url( $back_url ); ?>" data-attempt-id="<?php echo esc_attr( $attempt_id ); ?>" data-attempt-answer-id="<?php echo esc_attr( $answer->attempt_answer_id ); ?>" data-question-id="<?php echo esc_attr( $answer->question_id ); ?>" data-mark-as="correct" data-context="<?php echo esc_attr( $context ); ?>" title="<?php esc_attr_e( 'Mark as correct', 'tutor' ); ?>" class="quiz-manual-review-action tutor-mr-12 tutor-icon-rounded tutor-color-success">
															<i class="tutor-icon-mark"></i>
														</a>

														<a href="javascript:;" data-back-url="<?php echo esc_url( $back_url ); ?>" data-attempt-id="<?php echo esc_attr( $attempt_id ); ?>" data-attempt-answer-id="<?php echo esc_attr( $answer->attempt_answer_id ); ?>" data-question-id="<?php echo esc_attr( $answer->question_id ); ?>" data-mark-as="incorrect" data-context="<?php echo esc_attr( $context ); ?>" title="<?php esc_attr_e( 'Mark as incorrect', 'tutor' ); ?>" class="quiz-manual-review-action tutor-icon-rounded tutor-color-danger">
															<i class="tutor-icon-times"></i>
														</a>
														<?php if ( ! empty( $manual_overrides_map[ $answer->question_id ] ) ) : ?>
															<div class="tutor-fs-8 tutor-color-muted tutor-mt-4">
																<?php esc_html_e( '(Overrides the auto-graded result)', 'tutor' ); ?>
															</div>
														<?php endif; ?>
													<?php endif; ?>
													</div>
												</td>
												<?php
									}
									?>
								<?php endforeach; ?>
							</tr>

							<?php
							if ( ! $is_instructor_review && '' !== $student_q_feedback ) :
								?>
								<tr class="tutor-quiz-question-feedback-row">
									<td colspan="<?php echo esc_attr( count( $table_2_columns ) ); ?>" class="column-empty-state data-td-content" id="<?php echo esc_attr( $feedback_dom_id ); ?>" style="display:none;">
										<div class="tutor-quiz-question-feedback-card">
											<div class="tutor-d-flex tutor-gap-1 tutor-align-center tutor-mb-8">
												<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4B505C" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
													<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
												</svg>
												<span class="tutor-quiz-question-feedback-title"><?php esc_html_e( 'Feedback from instructor', 'tutor' ); ?></span>
											</div>
											<div class="tutor-quiz-question-feedback-body">
												<?php echo nl2br( esc_html( $student_q_feedback ) ); ?>
											</div>
										</div>
									</td>
								</tr>
							<?php endif; ?>

							<?php do_action( 'tutor_quiz_attempt_details_loop_after_row', $answer, $answer_status, $table_2_columns ); ?>

							<?php
				}
				?>
				</tbody>
			</table>
		</div>
		<?php
		do_action( 'tutor_quiz_attempt_details_loop_after' );
} else {
	echo 'course-single-previous-attempts' !== $context ? '<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mt-24">' . esc_html__( 'Quiz Overview', 'tutor' ) . '</div>' : '';
	tutor_utils()->tutor_empty_state( __( 'No answered questions to display', 'tutor' ) );
}
?>

<?php echo is_admin() ? '</div>' : ''; ?>

<?php if ( $is_instructor_review ) : ?>
	<div class="tutor-modal" id="tutor-question-feedback-modal" role="dialog" aria-modal="true" aria-labelledby="tutor-question-feedback-modal-title" aria-hidden="true">
		<div class="tutor-modal-overlay"></div>
		<div class="tutor-modal-window">
			<div class="tutor-modal-content tutor-modal-content-white">
				<button type="button" class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close aria-label="<?php esc_attr_e( 'Close', 'tutor' ); ?>">
					<span class="tutor-icon-times" aria-hidden="true"></span>
				</button>
				<div class="tutor-modal-header">
					<h3 id="tutor-question-feedback-modal-title" class="tutor-modal-title tutor-fs-5 tutor-fw-medium">
						<?php esc_html_e( 'Write feedback', 'tutor' ); ?>
					</h3>
				</div>
				<div class="tutor-modal-body">
					<div>
						<input type="hidden" id="tutor-question-feedback-attempt-id">
						<input type="hidden" id="tutor-question-feedback-answer-id">
						<textarea id="tutor-question-feedback-content" class="tutor-form-control tutor-form-control-auto-height" rows="5" placeholder="<?php esc_attr_e( 'Write feedback for the student...', 'tutor' ); ?>"></textarea>
					</div>
				</div>
				<div class="tutor-modal-footer">
					<button type="button" id="tutor-question-feedback-delete" class="tutor-btn tutor-btn-outline-danger" style="border-color: var(--tutor-color-danger); color: var(--tutor-color-danger); display: none;">
						<?php esc_html_e( 'Delete', 'tutor' ); ?>
					</button>
					<div class="tutor-d-flex tutor-align-center tutor-gap-2 tutor-ml-auto">
						<button type="button" class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
							<?php esc_html_e( 'Cancel', 'tutor' ); ?>
						</button>
						<button type="button" id="tutor-question-feedback-save" class="tutor-btn tutor-btn-primary">
							<?php esc_html_e( 'Save', 'tutor' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tutor-modal tutor-modal-confirmation" id="tutor-question-feedback-delete-modal" role="dialog" aria-modal="true" aria-labelledby="tutor-question-feedback-delete-modal-title" aria-hidden="true">
		<div class="tutor-modal-overlay"></div>
		<div class="tutor-modal-window tutor-modal-window-sm">
			<div class="tutor-modal-content tutor-modal-content-white">
				<button type="button" class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close aria-label="<?php esc_attr_e( 'Close', 'tutor' ); ?>">
					<span class="tutor-icon-times" aria-hidden="true"></span>
				</button>
				<div class="tutor-modal-body tutor-text-center tutor-py-24 tutor-px-32">
					<div class="tutor-mb-16">
						<img src="<?php echo esc_url( tutor()->url . 'assets/images/illustrations/delete.svg' ); ?>" style="width: 80px; height: 80px;" alt="" class="tutor-d-inline-block" aria-hidden="true" />
					</div>
					<h4 id="tutor-question-feedback-delete-modal-title" class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-0">
						<?php esc_html_e( 'Are you sure you want to delete this feedback?', 'tutor' ); ?>
					</h4>
				</div>
				<div class="tutor-modal-footer tutor-d-flex tutor-justify-center tutor-gap-2">
					<button type="button" class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
						<?php esc_html_e( 'No, keep it', 'tutor' ); ?>
					</button>
					<button type="button" id="tutor-question-feedback-delete-confirm" class="tutor-btn tutor-btn-danger">
						<i class="tutor-icon-trash-can-bold tutor-mr-4"></i><?php esc_html_e( 'Yes, delete', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
