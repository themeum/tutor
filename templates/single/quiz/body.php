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
		$question_layout_view = tutor_utils()->avalue_dot('question_layout_view', $quiz_attempt_info);

		$hide_quiz_time_display = (bool) tutor_utils()->avalue_dot('hide_quiz_time_display', $quiz_attempt_info);
		$hide_question_number_overview = (bool) tutor_utils()->avalue_dot('hide_question_number_overview', $quiz_attempt_info);

		$remaining_time_secs = (strtotime($is_started_quiz->attempt_started_at) + $time_limit_seconds ) - strtotime($quiz_attempt_info['date_time_now']);

		$remaining_time_context = tutor_utils()->seconds_to_time_context($remaining_time_secs);
		$questions = tutor_utils()->get_random_questions_by_quiz();
		?>

        <div class="quiz-head-meta-info">
			<?php
			if ( ! $hide_quiz_time_display){
				?>
                <div class="time-remaining">
					<?php _e('Time remaining : '); ?> <span id="tutor-quiz-time-update" data-attempt-settings="<?php echo esc_attr(json_encode($is_started_quiz)) ?>" data-attempt-meta="<?php echo esc_attr(json_encode($quiz_attempt_info)) ?>"><?php echo $remaining_time_context; ?></span>
                </div>
			<?php } ?>
        </div>

		<?php
		if (is_array($questions) && count($questions)) {
			?>
            <div id="tutor-quiz-attempt-questions-wrap" data-question-layout-view="<?php echo $question_layout_view; ?>">

				<?php
				if ($question_layout_view === 'question_pagination'){
					$question_i = 0;
					?>
                    <div class="tutor-quiz-questions-pagination">
                        <ul>
							<?php
							foreach ($questions as $question) {
								$question_i++;
								echo "<li><a href='#quiz-attempt-single-question-{$question->question_id}' class='tutor-quiz-question-paginate-item'>{$question_i}</a> </li>";
							}
							?>
                        </ul>
                    </div>
					<?php
				}
				?>

                <form id="tutor-answering-quiz" method="post">
					<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
                    <input type="hidden" value="<?php echo $is_started_quiz->attempt_id; ?>" name="attempt_id"/>
                    <input type="hidden" value="tutor_answering_quiz_question" name="tutor_action"/>
					<?php
					$question_i = 0;
					foreach ($questions as $question) {
						$question_i++;
						$question_settings = maybe_unserialize($question->question_settings);

						$style_display = ($question_layout_view !== 'question_below_each_other' && $question_i == 1) ? 'block' : 'none';
						if ($question_layout_view === 'question_below_each_other'){
							$style_display = 'block';
						}

						$next_question = isset($questions[$question_i]) ? $questions[$question_i] : false;
						?>
                        <div id="quiz-attempt-single-question-<?php echo $question->question_id; ?>" class="quiz-attempt-single-question quiz-attempt-single-question-<?php echo $question_i; ?>" style="display: <?php echo $style_display; ?> ;" <?php echo $next_question ? "data-next-question-id='#quiz-attempt-single-question-{$next_question->question_id}'" : '' ; ?> >

							<?php echo "<input type='hidden' name='attempt[{$is_started_quiz->attempt_id}][quiz_question_ids][]' value='{$question->question_id}' />";


							$question_type = $question->question_type;
							$answers = tutor_utils()->get_answers_by_quiz_question($question->question_id);
							$show_question_mark = (bool) tutor_utils()->avalue_dot('show_question_mark', $question_settings);

							echo '<h4 class="question-text">';
							if ( ! $hide_question_number_overview){
								echo $question_i;
							}
							echo $question->question_title;
							echo '</h4>';

							if ($show_question_mark){
								echo '<p class="question-marks"> '.__('Marks : ', 'tutor').$question->question_mark.' </p>';
							}
							?>
                            <p class="question-description"><?php echo $question->question_description; ?></p>

                            <div class="tutor-quiz-answers-wrap question-type-<?php echo $question_type; ?>">
								<?php
								if ( is_array($answers) && count($answers) ) {
									foreach ($answers as $answer){
										if ( $question_type === 'true_false' || $question_type === 'single_choice' ) {
											?>
                                            <label class="answer-view-<?php echo $answer->answer_view_format; ?>">
                                                <div class="quiz-answer-input-field">
                                                    <input name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>]" type="radio" value="<?php echo $answer->answer_id; ?>">
                                                    <span>&nbsp;</span>
                                                </div>

                                                <div class="quiz-answer-input-body">
													<?php
													if ($answer->answer_view_format !== 'image'){
														echo "<p class='tutor-quiz-answer-title'>{$answer->answer_title}</p>";
													}
													if ($answer->answer_view_format === 'image' || $answer->answer_view_format === 'text_image'){
														?>
                                                        <div class="quiz-answer-image-wrap">
                                                            <img src="<?php echo wp_get_attachment_image_url($answer->image_id, 'full') ?>" />
                                                        </div>
														<?php
													}
													?>
                                                </div>
                                            </label>
											<?php
										}elseif ($question_type === 'multiple_choice'){
											?>
                                            <label class="answer-view-<?php echo $answer->answer_view_format; ?>">
                                                <div class="quiz-answer-input-field">
                                                    <input name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>][]" type="checkbox" value="<?php echo $answer->answer_id; ?>">
                                                    <span>&nbsp;</span>
                                                </div>

                                                <div class="quiz-answer-input-body">
													<?php
													if ($answer->answer_view_format !== 'image'){
														echo "<p class='tutor-quiz-answer-title'>{$answer->answer_title}</p>";
													}
													if ($answer->answer_view_format === 'image' || $answer->answer_view_format === 'text_image'){
														?>
                                                        <div class="quiz-answer-image-wrap">
                                                            <img src="<?php echo wp_get_attachment_image_url($answer->image_id, 'full') ?>" />
                                                        </div>
														<?php
													}
													?>
                                                </div>
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
                                                <div class="answer-title">
													<?php
													if ($answer->answer_view_format !== 'image'){
														echo "<p class='tutor-quiz-answer-title'>{$answer->answer_title}</p>";
													}
													if ($answer->answer_view_format === 'image' || $answer->answer_view_format === 'text_image'){
														?>
                                                        <div class="quiz-answer-image-wrap">
                                                            <img src="<?php echo wp_get_attachment_image_url($answer->image_id, 'full') ?>" />
                                                        </div>
														<?php
													}
													?>
                                                </div>
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

																if ($answer->answer_view_format !== 'image'){
																	echo "<p class='tutor-quiz-answer-title'>{$answer->answer_title}</p>";
																}
																if ($answer->answer_view_format === 'image' || $answer->answer_view_format === 'text_image'){
																	?>
                                                                    <div class="quiz-answer-image-wrap">
                                                                        <img src="<?php echo wp_get_attachment_image_url($answer->image_id, 'full') ?>" />
                                                                    </div>
																	<?php
																}
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
								if ($question_type === 'open_ended' || $question_type === 'short_answer'){
									?>
                                    <textarea class="question_type_<?php echo $question_type; ?>" name="attempt[<?php echo
									$is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>]"></textarea>
									<?php

									if ($question_type === 'short_answer'){
										$characters_limit = tutor_utils()->avalue_dot('short_answer_characters_limit', $quiz_attempt_info);
										echo '<p class="answer_limit_desc">  characters remaining <span class="characters_remaining">'.$characters_limit.'</span> </p>';
									}

								}


								if ($question_type === 'image_answering'){
									?>
                                    <div class="quiz-image-answering-wrap">
										<?php
										foreach ($answers as $answer){
											?>
                                            <div class="quiz-image-answering-answer">
												<?php
												if (intval($answer->image_id)){
													?>
                                                    <div class="quiz-image-answering-image-wrap">
														<?php echo '<img src="'.wp_get_attachment_image_url($answer->image_id, 'full').'" />'; ?>
                                                    </div>

                                                    <div class="quiz-image-answering-input-field-wrap">
                                                        <input type="text"  name="attempt[<?php echo $is_started_quiz->attempt_id; ?>][quiz_question][<?php echo $question->question_id; ?>][answer_id][<?php echo $answer->answer_id; ?>]" >
                                                    </div>
													<?php
												}
												?>
                                            </div>
											<?php
										}
										?>
                                    </div>
									<?php
								}
								?>
                            </div>

							<?php
							if ($question_layout_view !== 'question_below_each_other'){
								if ($next_question){
									?>
                                    <div class="quiz-answer-footer-bar">
                                        <div class="quiz-footer-button">
                                            <button type="button" value="quiz_answer_submit" class="tutor-button
                                        tutor-success tutor-quiz-answer-next-btn"><?php _e( 'Answer &amp; Next Question', 'tutor' ); ?></button>
                                        </div>
                                    </div>
									<?php
								}else{
									?>
                                    <div class="quiz-answer-footer-bar">
                                        <div class="quiz-footer-button">
                                            <button type="submit" name="quiz_answer_submit_btn" value="quiz_answer_submit" class="tutor-button tutor-success"><?php
												_e( 'Submit Quiz', 'tutor' ); ?></button>
                                        </div>
                                    </div>
									<?php
								}
							}
							?>
                        </div>

						<?php
					}

					if ($question_layout_view === 'question_below_each_other'){
						?>
                        <div class="quiz-answer-footer-bar">
                            <div class="quiz-footer-button">
                                <button type="submit" name="quiz_answer_submit_btn" value="quiz_answer_submit" class="tutor-button
                                tutor-success"><?php _e( 'Submit Quiz', 'tutor' ); ?></button>
                            </div>
                        </div>
					<?php } ?>

                </form>
            </div>

			<?php

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
                        <th><?php _e('Pass Mark', 'tutor'); ?></th>
                        <th><?php _e('Result', 'tutor'); ?></th>
                    </tr>
					<?php
					foreach ( $previous_attempts as $attempt){
						?>

                        <tr>
                            <td>
								<?php
								echo date_i18n(get_option('date_format'), strtotime($attempt->attempt_started_at)).' '.date_i18n(get_option('time_format'), strtotime($attempt->attempt_started_at));

								if ($attempt->is_manually_reviewed){
									?>
                                    <p class="attempt-reviewed-text">
										<?php
										echo __('Manually reviewed at', 'tutor').' <br /> '.date_i18n(get_option('date_format', strtotime($attempt->manually_reviewed_at))).' '.date_i18n(get_option('time_format', strtotime($attempt->manually_reviewed_at)));
										?>
                                    </p>
									<?php
								}
								?>
                            </td>
                            <td>
								<?php echo $attempt->total_questions; ?>
                            </td>

                            <td>
								<?php echo $attempt->total_marks; ?>
                            </td>

                            <td>
								<?php
								$earned_percentage = $attempt->earned_marks > 0 ? ( number_format(($attempt->earned_marks * 100) / $attempt->total_marks)) : 0;
								echo $attempt->earned_marks."({$earned_percentage}%)";
								?>
                            </td>

                            <td>
								<?php

								$pass_marks = ($attempt->total_marks * $passing_grade) / 100;
								if ($pass_marks > 0){
									echo number_format_i18n($pass_marks, 2);
								}
								echo "({$passing_grade}%)";
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