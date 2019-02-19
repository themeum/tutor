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

<div id="tutor-quiz-body" class="tutor-quiz-body tutor-quiz-body-<?php the_ID(); ?>">
	<?php
	if ($is_started_quiz){
		$quiz_attempt_info = tutor_utils()->quiz_attempt_info($is_started_quiz->attempt_info);
		$quiz_attempt_info['date_time_now'] = date("Y-m-d H:i:s");

		$time_limit_seconds = tutor_utils()->avalue_dot('time_limit.time_limit_seconds', $quiz_attempt_info);


		$remaining_time_secs = (strtotime($is_started_quiz->attempt_started_at) + $time_limit_seconds ) - strtotime($quiz_attempt_info['date_time_now']);

		$remaining_time_context = tutor_utils()->seconds_to_time_context($remaining_time_secs);
		$questions = tutor_utils()->get_random_question_by_quiz();


		//echo '<pre>';
		//die(print_r($questions));

		?>

        <div class="quiz-head-meta-info">
            <div class="time-remaining">
				<?php _e('Time remaining : '); ?> <span id="tutor-quiz-time-update" data-attempt-settings="<?php echo esc_attr(json_encode($is_started_quiz)) ?>" data-attempt-meta="<?php echo esc_attr(json_encode($quiz_attempt_info)) ?>"><?php echo $remaining_time_context; ?></span>
            </div>
        </div>

		<?php do_action('tutor_quiz/single/after/meta');

		if (is_array($questions) && count($questions)) {
			foreach ($questions as $question) {

				echo "<p>{$question->question_type}</p>";

				?>

                <div id="tutor-quiz-attempt-questions-wrap">
                    <form id="tutor-answering-quiz" method="post">

                        <div class="quiz-attempt-single-question">

							<?php
							$question_type = $question->question_type;
							$answers = tutor_utils()->get_answers_by_quiz_question($question->question_id);

							//echo '<pre>';
							//die(print_r($answers));

							echo '<h4 class="question-text">'.$question->question_title.'</h4>';

							?>

                            <p class="question-description"><?php echo $question->question_description; ?></p>

							<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>

                            <input type="hidden" value="<?php echo $is_started_quiz->attempt_id; ?>" name="attempt_id"/>
                            <input type="hidden" value="<?php echo $question->question_id; ?>" name="quiz_question_id"/>
                            <input type="hidden" value="tutor_answering_quiz_question" name="tutor_action"/>
							<?php do_action( 'tutor_quiz/single/before/question/form_field' ); ?>

                            <div class="tutor-quiz-answers-wrap">
								<?php
								if ( is_array($answers) && count($answers) ) {
									foreach ($answers as $answer){
										if ( $question_type === 'true_false' || $question_type === 'single_choice' ) {
											?>
                                            <label>
                                                <input name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo
												$question->question_id; ?>]" type="radio" value="<?php echo $answer->answer_id; ?>">
                                                <span><?php echo $answer->answer_title; ?></span>
                                            </label>
											<?php
										}elseif ($question_type === 'multiple_choice'){
											?>
                                            <label>
                                                <input name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo
												$question->question_id; ?>][]" type="checkbox" value="<?php echo $answer->answer_id; ?>">
                                                <span><?php echo $answer->answer_title; ?></span>
                                            </label>
											<?php
										}
                                        elseif ($question_type === 'fill_in_the_blank'){
											?>
                                            <p class="fill-in-the-blank-field">
												<?php
												$count_dash_fields = substr_count($answer->answer_title, '{dash}');
												if ($count_dash_fields){

													$dash_string = array();
													$input_data = array();
													for($i=1; $i <=$count_dash_fields; $i ++){
														$dash_string[] = '{dash}';
														$input_data[] = "<input type='text' name='attempt[{$is_started_quiz->attempt_id}][quiz_question][{$question->question_id}][]' class='fill-in-the-blank-text-input' />";
													}
													echo str_replace($dash_string, $input_data, $answer->answer_title);
												}
												?>
                                            </p>

											<?php
										}
                                        elseif ($question_type === 'ordering'){
											?>
                                            <div class="question-type-ordering-item">
                                                <span class="answer-title">
	                                                <?php echo $answer->answer_title; ?>
                                                </span>
                                                <span class="answer-sorting-bar"><i class="tutor-icon-menu-2"></i> </span>
                                                <input type="hidden" name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>][answers][]" value="<?php echo $answer->answer_id; ?>" >
                                            </div>
											<?php
										}
									}

									/**
									 * Question type matchind and image matching
									 */
									if ($question_type === 'matching' || $question_type === 'image_matching'){
										?>
                                        <div class="quiz-answers-type-matching-wrap <?php echo 'answer-type-'.$question_type ?> ">
                                            <div class="quiz-draggable-rand-answers">
												<?php
												$rand_answers = tutor_utils()->get_answers_by_quiz_question($question->question_id, true);
												foreach ($rand_answers as $rand_answer){
													?>
                                                    <div class="quiz-draggable-answer-item">
														<?php
														if ($question_type === 'matching'){
															echo "<span class='draggable-answer-title'>{$rand_answer->answer_two_gap_match}</span>";
														}else{
															echo "<span class='draggable-answer-title'>{$rand_answer->answer_title}</span>";
														}
														?>
                                                        <span class="draggable-answer-icon"> <i class="tutor-icon-menu-2"></i> </span>
                                                        <input type="hidden" name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>][answers][]" value="<?php echo $rand_answer->answer_id; ?>" >
                                                    </div>
													<?php
												}
												?>
                                            </div>

											<div class="quiz-answer-matching-items-wrap">

												<?php
												foreach ($answers as $answer){
													?>
                                                    <div class="quiz-answer-item-matching">
                                                        <div class="quiz-answer-matching-title">
															<?php
															if ($question_type === 'matching') {
																echo $answer->answer_title;
															}elseif (intval($answer->image_id)){
																echo '<img src="'.wp_get_attachment_image_url($answer->image_id, 'full').'" />';
															}

															?>
                                                        </div>
                                                        <div class="quiz-answer-matching-droppable"></div>
                                                    </div>
													<?php
												}
												?>

                                            </div>
                                        </div>
										<?php
									}


								}

								/**
								 * For Open Ended Question Type
								 */
								if ($question_type === 'open_ended'){
									?>
                                    <textarea name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>]"></textarea>
									<?php
								}

								?>
                            </div>
                            <div class="quiz-answer-footer-bar">
                                <div class="quiz-footer-button">
                                    <button type="submit" name="quiz_answer_submit_btn" value="quiz_answer_submit"
                                            class="tutor-button tutor-success"><?php _e( 'Answer and Next Question', 'tutor' ); ?></button>
                                </div>
                            </div>

                        </div>
                    </form>


                </div>

				<?php
			}

		}else{
			?>
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

			<?php
		}
	}else{
		if ($attempt_remaining > 0) {
			?>
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

			<?php
		}



		if ($previous_attempts){
			?>
            <h4 class="tutor-quiz-attempt-history-title"><?php _e('Previous attempts', 'tutor'); ?></h4>

            <div class="tutor-quiz-attempt-history">
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
			<?php
		}
	}
	?>
</div>