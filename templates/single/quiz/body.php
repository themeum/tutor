<?php

global $post;
$currentPost = $post;

$is_started_quiz = tutor_utils()->is_started_quiz();
$previous_attempts = tutor_utils()->quiz_attempts();
$attempted_count = is_array($previous_attempts) ? count($previous_attempts) : 0;

$attempts_allowed = tutor_utils()->get_quiz_option(get_the_ID(), 'attempts_allowed', 0);
$passing_grade = tutor_utils()->get_quiz_option(get_the_ID(), 'passing_grade', 0);

$attempt_remaining = $attempts_allowed - $attempted_count;
?>

<?php do_action('tutor_quiz/single/before/body'); ?>

<div id="tutor-quiz-body" class="tutor-quiz-body tutor-quiz-body-<?php the_ID(); ?>">
	<?php
	if ($is_started_quiz){
		$quiz_attempt_info = tutor_utils()->quiz_attempt_info($is_started_quiz->comment_ID);
		$quiz_attempt_info['date_time_now'] = date("Y-m-d H:i:s");

		$time_limit_seconds = tutor_utils()->avalue_dot('time_limit_seconds', $quiz_attempt_info);
		$remaining_time_secs = (strtotime($is_started_quiz->quiz_started_at) + $time_limit_seconds ) - strtotime($quiz_attempt_info['date_time_now']);

		$remaining_time_context = tutor_utils()->seconds_to_time_context($remaining_time_secs);
		$question = tutor_utils()->get_rand_single_question_by_quiz_for_student();
		do_action('tutor_quiz/single/before/meta'); ?>

		<div class="quiz-head-meta-info">
			<div class="time-remaining">
				<?php _e('Time remaining : '); ?> <span id="tutor-quiz-time-update" data-attempt-settings="<?php echo esc_attr(json_encode($is_started_quiz)) ?>" data-attempt-meta="<?php echo esc_attr(json_encode($quiz_attempt_info)) ?>"><?php echo $remaining_time_context; ?></span>
			</div>
		</div>

		<?php do_action('tutor_quiz/single/after/meta');

		if ($question) {
			do_action('tutor_quiz/single/before/question'); ?>

			<div id="tutor-quiz-single-wrap">
				<?php
				$question_type = get_post_meta( $question->ID, '_question_type', true );
				$answers       = tutor_utils()->get_quiz_answer_options_by_question( $question->ID );
				?>
				<p class="question-text"><?php echo $question->post_title; ?></p>

				<div class="quiz-answers">

					<?php do_action('tutor_quiz/single/before/question/form'); ?>

					<form id="tutor-answering-quiz" method="post">
						<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>

						<input type="hidden" value="<?php echo $is_started_quiz->comment_ID; ?>" name="attempt_id"/>
						<input type="hidden" value="<?php echo $question->ID; ?>" name="quiz_question_id"/>
						<input type="hidden" value="tutor_answering_quiz_question" name="tutor_action"/>

						<?php do_action('tutor_quiz/single/before/question/form_field'); ?>

						<?php
						if ( $answers ) {
							if ( $question_type === 'true_false' || $question_type === 'single_choice' ) {
								echo '<p>' . __( 'select one :', 'tutor' ) . '</p>';
								foreach ( $answers as $answer ) {
									$answer_content = json_decode( $answer->comment_content, true );
									?>
									<label>
										<input name="attempt[<?php echo $is_started_quiz->comment_ID; ?>][quiz_question][<?php echo $question->ID; ?>]"
										       type="radio" value="<?php echo $answer->comment_ID; ?>">
										<?php
										if ( isset( $answer_content['answer_option_text'] ) ) {
											echo $answer_content['answer_option_text'];
										}
										?>
									</label>
									<?php
								}
							}elseif($question_type === 'multiple_choice' ){
								foreach ( $answers as $answer ) {
									$answer_content = json_decode( $answer->comment_content, true );
									?>
									<label>
										<input name="attempt[<?php echo $is_started_quiz->comment_ID; ?>][quiz_question][<?php echo $question->ID; ?>][]" type="checkbox" value="<?php echo $answer->comment_ID; ?>">
										<?php
										if ( isset( $answer_content['answer_option_text'] ) ) {
											echo $answer_content['answer_option_text'];
										}
										?>
									</label>
									<?php
								}
							}
						}
						?>

						<?php do_action('tutor_quiz/single/after/question/form_field'); ?>

						<?php do_action('tutor_quiz/single/before/question/form_submit_btn'); ?>

						<div class="quiz-answer-footer-bar">
							<div class="quiz-footer-button">
								<button type="submit" name="quiz_answer_submit_btn" value="quiz_answer_submit"><?php _e( 'Answer and Next Question', 'tutor' ); ?></button>
							</div>
						</div>

						<?php do_action('tutor_quiz/single/after/question/form_submit_btn'); ?>

					</form>

					<?php do_action('tutor_quiz/single/after/question/form'); ?>


				</div>
			</div>

			<?php do_action('tutor_quiz/single/after/question'); ?>


			<?php
		}else{
			do_action('tutor_quiz/single/before/finish-quiz'); ?>

			<div class="start-quiz-wrap">
				<form id="tutor-finish-quiz" method="post">
					<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>

					<input type="hidden" value="<?php echo get_the_ID(); ?>" name="quiz_id"/>
					<input type="hidden" value="tutor_finish_quiz_attempt" name="tutor_action"/>

					<button type="submit" class="tutor-button" name="finish_quiz_btn" value="finish_quiz">
						<i class="icon-floppy"></i> <?php _e( 'Finish', 'tutor' ); ?>
					</button>
				</form>
			</div>

			<?php do_action('tutor_quiz/single/after/finish-quiz');
		}
	}else{
		if ($attempt_remaining > 0) {
			do_action('tutor_quiz/single/before/start-quiz'); ?>

			<div class="start-quiz-wrap">
				<form id="tutor-start-quiz" method="post">
					<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>

					<input type="hidden" value="<?php echo get_the_ID(); ?>" name="quiz_id"/>
					<input type="hidden" value="tutor_start_quiz" name="tutor_action"/>

					<button type="submit" class="tutor-button" name="start_quiz_btn" value="start_quiz">
						<i class="icon-hourglass-1"></i> <?php _e( 'Start Quiz', 'tutor' ); ?>
					</button>
				</form>
			</div>

			<?php do_action('tutor_quiz/single/after/start-quiz');
		}
		if ($previous_attempts){
			do_action('tutor_quiz/single/before/quiz-attempted-table');
			?>

			<h3><?php _e('Previous attempts', 'tutor'); ?></h3>

			<div class="quiz-attempts-wrap">
				<table>
					<tr>
						<th><?php _e('Time', 'tutor'); ?></th>
						<th><?php _e('Questions', 'tutor'); ?></th>
						<th><?php _e('Total Marks', 'tutor'); ?></th>
						<th><?php _e('Earned Marks', 'tutor'); ?></th>
						<th><?php _e('Result', 'tutor'); ?></th>
					</tr>
					<?php
					foreach ( $previous_attempts as $attempt){
						$attempt_info = maybe_unserialize($attempt->quiz_attempt_info);
						?>

						<tr>
							<td>
								<?php
								echo date_i18n(get_option('date_format'), strtotime($attempt->quiz_started_at)).' '.date_i18n(get_option('time_format'), strtotime($attempt->quiz_started_at));

								$manual_reviewed = tutor_utils()->avalue_dot('manual_reviewed', $attempt_info);
								if ($manual_reviewed){
									if ($manual_reviewed){
										?>
										<p class="attempt-reviewed-text">
											<?php
											echo __('Manually reviewed at', 'tutor').' <br /> '.date_i18n(get_option('date_format', strtotime($manual_reviewed))).' '.date_i18n(get_option('time_format', strtotime
												($manual_reviewed)));
											?>
										</p>
										<?php
									}
								}
								?>



							</td>
							<td>
								<?php
								echo tutor_utils()->avalue_dot('total_question', $attempt_info)
								?>
							</td>

							<td>
								<?php
								$answers_mark = wp_list_pluck(tutor_utils()->avalue_dot('answers', $attempt_info), 'question_mark' );
								$total_marks = array_sum($answers_mark);
								echo $total_marks;
								?>
							</td>

							<td>
								<?php
								$earned_marks = tutor_utils()->avalue_dot('marks_earned', $attempt_info);
								$earned_percentage = $earned_marks > 0 ? ( number_format(($earned_marks * 100) / $total_marks)) : 0;
								echo $earned_marks."({$earned_percentage}%)";
								?>
							</td>

							<td>
								<?php
								if ($earned_percentage >= $passing_grade){
									echo '<span class="result-pass">'.__('Pass', 'tutor').'</span>';
								}else{
									echo '<span class="result-fail">'.__('Fail', 'tutor').'</span>';
								}
								?>
							</td>
						</tr>

						<?php
					}
					?>

				</table>
			</div>
			<?php do_action('tutor_quiz/single/after/quiz-attempted-table'); ?>
			<?php
		}
	}
	?>
</div>

<?php do_action('tutor_quiz/single/after/body'); ?>
