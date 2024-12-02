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

			if ( isset( $ans->answer_two_gap_match ) ) {
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

					if ( isset( $ans->answer_title ) && ! isset( $ans->answer_two_gap_match ) ) {
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

			if ( isset( $ans->answer_two_gap_match ) ) {
					echo '<div class="image-match">' . esc_html( stripslashes( $ans->answer_two_gap_match ) ) . '</div>';
				echo '</div>';
			}
		}
            //phpcs:ignore
			echo count( $multi_texts ) ? implode( ', ', wp_unslash( $multi_texts ) ) : '';

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

// Prepare student data.
if ( ! isset( $user_data ) ) {
	$user_data = get_userdata( $user_id );
}

// Prepare attempt meta info.
extract( QuizModel::get_quiz_attempt_timing( $attempt_data ) ); // $attempt_duration, $attempt_duration_taken;

// Prepare the correct/incorrect answer count for the first summary table.
$answers   = QuizModel::get_quiz_answers_by_attempt_id( $attempt_id );
$correct   = 0;
$incorrect = 0;
if ( is_array( $answers ) && count( $answers ) > 0 ) {
	foreach ( $answers as $answer ) {
		if ( (bool) isset( $answer->is_correct ) ? $answer->is_correct : '' ) {
			$correct++;
		} elseif ( 'open_ended' === $answer->question_type || 'short_answer' === $answer->question_type ) {
		} else {
			$incorrect++;
		}
	}
}

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
								echo esc_html( $attempt_data->earned_marks );
								$earned_percentage = $attempt_data->earned_marks > 0 ? ( number_format( ( $attempt_data->earned_marks * 100 ) / $attempt_data->total_marks ) ) : 0;
								echo esc_html( ' (' . $earned_percentage . '%)' );
							?>
						<?php elseif ( 'result' === $key ) : ?>
							<?php
								$ans_array   = is_array( $answers ) ? $answers : array();
								$has_pending = count(
									array_filter(
										$ans_array,
										function ( $ans ) {
											return null === $ans->is_correct;
										}
									)
								);

							if ( $has_pending ) {
								echo '<span class="tutor-badge-label label-warning">' . esc_html__( 'Pending', 'tutor' ) . '</span>';
							} elseif ( $earned_percentage >= $pass_mark_percent ) {
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
$query_vars   = $wp_query->query_vars;
$page_name    = isset( $query_vars['tutor_dashboard_page'] ) ? $query_vars['tutor_dashboard_page'] : '';
$attempt_info = unserialize( $attempt_data->attempt_info );
$feedback     = is_array( $attempt_info ) && isset( $attempt_info['instructor_feedback'] ) ? $attempt_info['instructor_feedback'] : '';
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
if ( is_array( $answers ) && count( $answers ) ) {
	// Filter out not needed columns based on question type.
	$table_2_columns = apply_filters( 'tutor_filter_attempt_answer_column', $table_2_columns, $answers );
	$answers         = apply_filters( 'tutor_filter_attempt_answers', $answers );
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
					$answer_i++;
					$question_type          = tutor_utils()->get_question_types( $answer->question_type );
					$question_settings = maybe_unserialize( $answer->question_settings );
					$is_image_matching = isset( $question_settings['is_image_matching'] ) && '1' === $question_settings['is_image_matching'];
					$answer_status      = 'wrong';

					// If already correct, then show it.
					if ( (bool) $answer->is_correct ) {
						$answer_status = 'correct';
					}

					// Image answering also needs review since the answer texts are not meant to match exactly.
					elseif ( in_array( $answer->question_type, array( 'open_ended', 'short_answer', 'image_answering' ), true ) ) {
						$answer_status = null === $answer->is_correct ? 'pending' : 'wrong';
					}
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
												<?php $type = tutor_utils()->get_question_types( $answer->question_type ); ?>
													<div class="tooltip-wrap tooltip-icon tutor-d-flex tutor-align-center">
													<?php
													if ( 'h5p' === $answer->question_type ) {
														?>
														<span class="tooltip-btn tutor-d-flex tutor-align-center">
															<svg width="2e3" height="2e3" class="tutor-quiz-type-icon" version="1.1" viewBox="0 0 2e3 2e3" xmlns="http://www.w3.org/2000/svg" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
															 <metadata>
															  <rdf:RDF>
															   <cc:Work rdf:about="">
															    <dc:format>image/svg+xml</dc:format>
															    <dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage"/>
															    <dc:title/>
															   </cc:Work>
															  </rdf:RDF>
															 </metadata>
															 <path d="m-5.2836 1004.2v-1e3h2009.7v2e3h-2009.7zm602.92 150v-90h160.78v180l185.4-0.166-5.0243-2.3086c-2.7634-1.2697-11.693-5.0995-19.843-8.5106-24.366-10.198-36.891-18.724-54.121-36.843-10.05-10.568-20.873-25.967-27.462-39.076-5.0966-10.139-13.346-31.394-13.809-35.581l-0.32475-2.9363 63.809-9.2284c35.095-5.0757 64.939-9.2468 66.321-9.2693 1.9741-0.032 3.1578 1.0489 5.5268 5.0472 9.524 16.075 29.045 28.375 48.934 30.835 39.402 4.8726 74.132-24.163 75.907-63.462 1.4168-31.358-16.291-56.906-46.942-67.723-8.1892-2.8901-25.607-3.543-35.585-1.3338-16.562 3.6667-34.105 15.999-41.818 29.397-1.9314 3.355-3.8993 6.1134-4.3731 6.1298-1.8758 0.065-131.66-18.014-132.04-18.393-0.33622-0.3346 56.264-250.94 59.022-261.33l0.86285-3.25h-124.44v208h-160.78v-208h-152.74v488h152.74v-90zm691.35 0.086v-89.914l61.046-0.4487c50.137-0.3686 63.102-0.7486 72.556-2.1265 45.532-6.6369 75.163-20.01 98.924-44.645 23.057-23.906 35.045-51.976 38.844-90.951 1.4392-14.766 0.7197-39.174-1.5623-53-10.033-60.784-46.103-98.57-106.52-111.58-22.373-4.8192-20.773-4.7584-135.41-5.1478l-108.27-0.3678v100.1h-220.85l-3.2413 13.75c-1.7827 7.5625-5.8428 24.699-9.0225 38.082-4.7451 19.971-5.4901 24.239-4.1566 23.813 0.89355-0.2855 6.3726-2.3074 12.176-4.4931 13.294-5.0069 37.711-11.701 47.748-13.09 9.7858-1.3545 38.914-1.3666 53.241-0.022 30.235 2.8372 56.58 11.691 79.31 26.652 12.878 8.4771 32.803 28.1 41.436 40.809 32.824 48.318 35.187 112.49 6.3454 172.29-3.2595 6.7578-8.3491 15.983-11.31 20.5-19.02 29.016-48.23 53.062-72.572 59.742-8.0702 2.2146-18.898 7.0389-20.084 8.9486-0.4308 0.6937 28.626 1.0224 90.365 1.0224h91zm0-244.09v-54h30.424c48.304 0 63.803 3.0493 76.569 15.065 11.117 10.463 16.624 23.326 16.581 38.724-0.045 16.066-3.6793 25.057-14.466 35.791-14.862 14.79-30.913 18.42-81.434 18.42h-27.673z" stroke-width="1.0024"/>
															 <path d="m445.74 1000.3v-243.31h150.85v209.25h163.02v-209.25h121.75l-1.307 5.4745c-2.5392 10.635-47.071 207.77-52.49 232.36-3.0226 13.718-4.9042 25.533-4.1815 26.256s30.515 5.3399 66.205 10.26l64.891 8.9462 9.8954-11.577c27.937-32.684 75.421-33.24 102.84-1.2047 35.938 41.985 4.7303 107.54-51.078 107.29-19.803-0.087-35.659-7.3743-50.547-23.231l-11.784-12.551-64.806 9.1844c-35.644 5.0515-65.351 9.7291-66.017 10.395-2.5401 2.5401 10.5 34.529 20.974 51.451 19.244 31.091 42.436 50.852 76.365 65.064 8.6293 3.6149 16.146 7.0049 16.703 7.5333 0.55756 0.5285-39.132 0.9609-88.2 0.9609h-89.213v-180.05h-163.02v180.05h-150.85z" fill="#fff" stroke="#fff" stroke-width="2.4331"/>
															 <path d="m1113.7 1241.5c1.2712-1.1737 7.9008-3.9462 14.732-6.1611 58.229-18.879 103.08-90.97 103.13-165.77 0.03-43.193-12.994-76.78-41.351-106.63-19.256-20.271-39.728-33.177-66.904-42.178-17.859-5.9149-22.878-6.4619-60.827-6.6287-38.795-0.17059-42.686 0.23875-62.676 6.5945-11.722 3.727-22.957 7.4073-24.967 8.1784-2.3603 0.90573-3.2737 0.22951-2.5802-1.9102 0.59044-1.8218 4.5292-18.367 8.7528-36.767l7.6793-33.455h220.99v-100.35l113.75 1.2124c106.04 1.1303 115.07 1.5628 133.21 6.383 50.455 13.403 80.167 40.402 95.377 86.669 5.6162 17.083 6.4237 23.632 6.601 53.528 0.1509 25.428-0.8842 38.073-4.0828 49.878-13.928 51.405-51.002 87.442-103.98 101.07-19.479 5.0099-65.583 8.1775-121.05 8.3168l-41.971 0.1053v180.05h-88.078c-50.624 0-87.095-0.9075-85.766-2.1341zm261.63-281.61c16.962-5.037 32.185-19.719 36.303-35.012 7.7315-28.713-8.2767-57.752-36.215-65.695-4.8411-1.3763-26.594-3.2014-48.34-4.0558l-39.538-1.5534v113.44l37.321-1.6092c22.552-0.97241 42.524-3.1542 50.468-5.5133z" fill="#fff" stroke="#fff" stroke-width="2.4331"/>
															</svg>
														</span>
														<?php
													} else {
														echo wp_kses(
															$question_type['icon'] ?? '',
															tutor_utils()->allowed_icon_tags()
														);
													}
													?>
														<span class="tooltip-txt tooltip-top">
														<?php
														if ( 'h5p' === $answer->question_type ) {
																echo esc_html( 'H5P' );
														} else {
																echo esc_html( $type['name'] ?? '' );
														}
														?>
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
																function( $ans ) {
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
												<?php do_action( 'tutor_quiz_attempt_after_result_column', $answer, $answer_status ); ?>

												<?php
												if ( 'h5p' !== $answer->question_type ) {
													switch ( $answer_status ) {
														case 'correct':
															echo '<span class="tutor-badge-label label-success">' . esc_html__( 'Correct', 'tutor' ) . '</span>';
															break;

														case 'pending':
															echo '<span class="tutor-badge-label label-warning">' . esc_html__( 'Pending', 'tutor' ) . '</span>';
															break;

														case 'wrong':
															echo '<span class="tutor-badge-label label-danger">' . esc_html__( 'Incorrect', 'tutor' ) . '</span>';
															break;
													}
												}
												?>
												</td>
												<?php
											break;

										case 'manual_review':
											?>
												<td class="tutor-text-center tutor-nowrap-ellipsis" data-title="<?php echo esc_attr( $column ); ?>">
													<div class="tutor-manual-review-wrapper">
													<a href="javascript:;" data-back-url="<?php echo esc_url( $back_url ); ?>" data-attempt-id="<?php echo esc_attr( $attempt_id ); ?>" data-attempt-answer-id="<?php echo esc_attr( $answer->attempt_answer_id ); ?>" data-mark-as="correct" data-context="<?php echo esc_attr( $context ); ?>" title="<?php esc_attr_e( 'Mark as correct', 'tutor' ); ?>" class="quiz-manual-review-action tutor-mr-12 tutor-icon-rounded tutor-color-success">
														<i class="tutor-icon-mark"></i>
													</a>

													<a href="javascript:;" data-back-url="<?php echo esc_url( $back_url ); ?>" data-attempt-id="<?php echo esc_attr( $attempt_id ); ?>" data-attempt-answer-id="<?php echo esc_attr( $answer->attempt_answer_id ); ?>" data-mark-as="incorrect" data-context="<?php echo esc_attr( $context ); ?>" title="<?php esc_attr_e( 'Mark as In correct', 'tutor' ); ?>" class="quiz-manual-review-action tutor-icon-rounded tutor-color-danger">
														<i class="tutor-icon-times"></i>
													</a>
													</div>
												</td>
												<?php
									}
									?>
								<?php endforeach; ?>
							</tr>

							<?php do_action( 'tutor_quiz_attempt_details_loop_after_row', $answer, $answer_status ); ?>

							<?php
				}
				?>
				</tbody>
			</table>
		</div>
		<?php
		do_action( 'tutor_quiz_attempt_details_loop_after' );
}
?>

<?php echo is_admin() ? '</div>' : ''; ?>
