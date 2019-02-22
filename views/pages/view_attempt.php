<?php
$attempt_id = (int) sanitize_text_field($_GET['attempt_id']);
$attempt = tutor_utils()->get_attempt($attempt_id);

$quiz_attempt_info = tutor_utils()->quiz_attempt_info($attempt->attempt_info);
$answers = tutor_utils()->get_quiz_answers_by_attempt_id($attempt->attempt_id);
?>

<div class="wrap">
    <h2><?php _e('View Attempts', 'tutor'); ?></h2>

    <div class="tutor-quiz-attempt-info-row">
        <div class="tutor-attempt-student-info">
			<?php
			$user_id = tutor_utils()->avalue_dot('user_id', $attempt);
			$user = get_userdata($user_id);
			?>

            <p class="quiz-attempt-info-row">
				<?php echo '<span class="attempt-property-name">'.__('Attempt By', 'tutor').' </span><span> : '. $user->display_name.'</span>'; ?>
            </p>

            <p class="quiz-attempt-info-row">
				<?php echo '<span class="attempt-property-name">'.__('Attempt At', 'tutor').'</span> <span> : '. date_i18n(get_option('date_format'), strtotime($attempt->attempt_started_at)).' '.date_i18n(get_option('time_format'), strtotime($attempt->attempt_started_at)).'</span>'; ?>
            </p>

            <p class="quiz-attempt-info-row">
                <span class="attempt-property-name">
                    <?php echo __('Status', 'tutor'); ?>
                </span>

                <span class="attempt-property-value"> :
					<?php
					$status = ucwords(str_replace('quiz_', '', $attempt->attempt_status));
					echo "<span class='tutor-status-context {$attempt->attempt_status}'>{$status}</span>";
					?>
                </span>
            </p>

			<?php if ((bool) $attempt->is_manually_reviewed ){
				?>
                <p class="quiz-attempt-info-row text-notified">
                    <span class="attempt-property-name"><?php _e('Manually reviewed at', 'tutor'); ?></span>

                    <span class="attempt-property-value"> :
						<?php echo date_i18n(get_option('date_format'), strtotime($attempt->manually_reviewed_at)).' '.date_i18n(get_option('time_format'), strtotime($attempt->manually_reviewed_at)); ?>
                    </span>
                </p>
				<?php
			} ?>

        </div>

        <div class="quiz-attempt-student-info">
            <p class="quiz-attempt-info-row">
				<?php
				echo '<span class="attempt-property-name">'. __('Quiz', 'tutor').' </span> <span class="attempt-property-value">  : '."<a href='".admin_url("post.php?post={$attempt->quiz_id}&action=edit")."'>".get_the_title($attempt->quiz_id)."</a> </span> ";
				?>
            </p>

            <p class="quiz-attempt-info-row">
				<?php
				$quiz = tutor_utils()->get_course_by_quiz($attempt->quiz_id);
				if ($quiz) {
					echo '<span class="attempt-property-name">' . __( 'Course', 'tutor' ) . '</span> <span class="attempt-property-value"> : ' . "<a href='" . admin_url( "post.php?post={$quiz->ID}&action=edit" ) . "'>" . get_the_title( $quiz->ID ) . "</a> </span>";
				}
				?>
            </p>

            <p class="quiz-attempt-info-row">
                <span class="attempt-property-name"><?php _e('Result', 'tutor'); ?></span>

                <span class="attempt-property-value"> :
					<?php
					$pass_mark_percent = tutor_utils()->get_quiz_option($attempt->quiz_id, 'passing_grade', 0);
					$earned_percentage = $attempt->earned_marks > 0 ? ( number_format(($attempt->earned_marks * 100) / $attempt->total_marks)) : 0;

					$output = $attempt->earned_marks." out of {$attempt->total_marks} <br />";
					$output .= "({$earned_percentage}%) out of ({$pass_mark_percent}%) <br />";

					if ($earned_percentage >= $pass_mark_percent){
						$output .= '<span class="result-pass">'.__('Pass', 'tutor').'</span>';
					}else{
						$output .= '<span class="result-fail">'.__('Fail', 'tutor').'</span>';
					}

					echo $output;
					?>
                </span>
            </p>

            <p class="quiz-attempt-info-row">
                <span class="attempt-property-name"><?php _e('Quiz Time', 'tutor'); ?></span>
                <span class="attempt-property-value"> :
					<?php
					$time_limit_seconds = tutor_utils()->avalue_dot('time_limit.time_limit_seconds', $quiz_attempt_info);
					echo tutor_utils()->seconds_to_time_context($time_limit_seconds);
					?>
                </span>
            </p>
        </div>
    </div>

	<?php
	if (is_array($answers) && count($answers)){
		?>
        <table class="wp-list-table widefat striped">
            <tr>
                <th width="200"><?php _e('Question', 'tutor'); ?></th>
                <th><?php _e('Correct', 'tutor'); ?></th>
                <th><?php _e('Given Answers', 'tutor'); ?></th>
                <th><?php _e('Review', 'tutor'); ?></th>
            </tr>
			<?php
			foreach ($answers as $answer){
				?>

                <tr>
                    <td><?php echo $answer->question_title; ?></td>
                    <td>
						<?php
						if ((bool) $answer->is_correct) {
							echo '<span class="tutor-status-approved-context"><i class="dashicons dashicons-yes"></i> </span>';
						}else{
							echo '<span class="tutor-status-blocked-context"><i class="dashicons dashicons-no-alt"></i> </span>';
						}
						?>
                    </td>

                    <td>
						<?php
						if ($answer->question_type === 'true_false' || $answer->question_type === 'single_choice' ){

							$get_answers = tutor_utils()->get_answer_by_id($answer->given_answer);
							$answer_titles = wp_list_pluck($get_answers, 'answer_title');
							echo '<p>'.implode('</p><p>', $answer_titles).'</p>';

						}elseif ($answer->question_type === 'multiple_choice'){

							$get_answers = tutor_utils()->get_answer_by_id(maybe_unserialize($answer->given_answer));
							$answer_titles = wp_list_pluck($get_answers, 'answer_title');
							echo '<p>'.implode('</p><p>', $answer_titles).'</p>';

						}elseif ($answer->question_type === 'fill_in_the_blank'){

							$answer_titles = maybe_unserialize($answer->given_answer);
							echo '<p>'.implode('</p><p>', $answer_titles).'</p>';

						}elseif ($answer->question_type === 'open_ended'){

							if ($answer->given_answer){
								echo wpautop(stripslashes($answer->given_answer));
							}

						}elseif ($answer->question_type === 'ordering'){

							$ordering_ids = maybe_unserialize($answer->given_answer);
							foreach ($ordering_ids as $ordering_id){
								$get_answers = tutor_utils()->get_answer_by_id($ordering_id);
								$answer_titles = wp_list_pluck($get_answers, 'answer_title');
								echo '<p>'.implode('</p><p>', $answer_titles).'</p>';
							}

						}elseif ($answer->question_type === 'matching'){

							$ordering_ids = maybe_unserialize($answer->given_answer);
							$original_saved_answers = tutor_utils()->get_answers_by_quiz_question($answer->question_id);

							foreach ($original_saved_answers as $key => $original_saved_answer){
								$provided_answer_order_id = isset($ordering_ids[$key]) ? $ordering_ids[$key] : 0;
								$provided_answer_order = tutor_utils()->get_answer_by_id($provided_answer_order_id);
								foreach ($provided_answer_order as $provided_answer_order);

								echo $original_saved_answer->answer_title  ." - {$provided_answer_order->answer_two_gap_match} <br />";
							}



						}elseif ($answer->question_type === 'image_matching'){

							$ordering_ids = maybe_unserialize($answer->given_answer);
							$original_saved_answers = tutor_utils()->get_answers_by_quiz_question($answer->question_id);

							echo '<div class="answer-image-matched-wrap">';
							foreach ($original_saved_answers as $key => $original_saved_answer){
								$provided_answer_order_id = isset($ordering_ids[$key]) ? $ordering_ids[$key] : 0;
								$provided_answer_order = tutor_utils()->get_answer_by_id($provided_answer_order_id);
								foreach ($provided_answer_order as $provided_answer_order);
								?>
                                <div class="image-matching-item">
                                    <p class="dragged-img-rap"><img src="<?php echo wp_get_attachment_image_url( $original_saved_answer->image_id); ?>" /> </p>
                                    <p class="dragged-caption"><?php echo $provided_answer_order->answer_title; ?></p>
                                </div>
								<?php
							}
							echo '</div>';
						}

						?>
                    </td>

                    <td>
                        <a href="<?php echo admin_url("admin.php?action=review_quiz_answer&attempt_id={$attempt_id}&attempt_answer_id={$answer->attempt_answer_id}&mark_as=correct"); ?>" title="<?php _e('Mark as correct', 'tutor'); ?>" class="tutor-button button button-success"><i class="dashicons dashicons-yes"></i> </a>

                        <a href="<?php echo admin_url("admin.php?action=review_quiz_answer&attempt_id={$attempt_id}&attempt_answer_id={$answer->attempt_answer_id}&mark_as=incorrect"); ?>" title="<?php _e('Mark as In correct', 'tutor'); ?>" class="tutor-button button button-danger"><i class="dashicons dashicons-no-alt"></i></a>
                    </td>
                </tr>
				<?php
			}
			?>
        </table>
		<?php
	}
	?>
</div>