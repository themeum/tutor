<?php
$attempt_id = (int) sanitize_text_field($_GET['attempt_id']);
$attempt = tutor_utils()->get_attempt($attempt_id);
$quiz_attempt_info = tutor_utils()->quiz_attempt_info($attempt_id);
$answers = tutor_utils()->avalue_dot('answers', $quiz_attempt_info);
$manual_reviewed = tutor_utils()->avalue_dot('manual_reviewed', $quiz_attempt_info);
?>

<div class="wrap">
    <h2><?php _e('View Attempts', 'tutor'); ?></h2>

    <div class="tutor-quiz-attempt-info-row">
        <div class="tutor-attempt-student-info">
            <?php
            $user_id = tutor_utils()->avalue_dot('user_id', $attempt);
            $user = get_userdata($user_id);

            $quiz_started_at = tutor_utils()->avalue_dot('quiz_started_at', $attempt);
            $quiz_attempt_status = tutor_utils()->avalue_dot('quiz_attempt_status', $attempt);
            $comment_post_ID = tutor_utils()->avalue_dot('comment_post_ID', $attempt);

            ?>

            <p class="quiz-attempt-info-row"> <?php echo '<span class="attempt-property-name">'.__('Attempt By', 'tutor').' </span><span> : '. $user->display_name.'</span>'; ?></p>

            <p class="quiz-attempt-info-row"> <?php echo '<span class="attempt-property-name">'.__('Attempt At', 'tutor').'</span> <span> : '. date_i18n(get_option('date_format', strtotime($quiz_started_at))).' '.date_i18n(get_option('time_format', strtotime($quiz_started_at))).'</span>'; ?></p>

            <p class="quiz-attempt-info-row">
                <span class="attempt-property-name">
                    <?php echo __('Status', 'tutor'); ?>
                </span>

                <span class="attempt-property-value"> :
                <?php
                $status = ucwords(str_replace('quiz_', '', $quiz_attempt_status));
                echo "<span class='tutor-status-context {$quiz_attempt_status}'>{$status}</span>";
                ?>
                </span>
            </p>



            <?php if ($manual_reviewed){
                ?>
            <p class="quiz-attempt-info-row text-notified">
                 <span class="attempt-property-name">
                <?php _e('Manually reviewed at', 'tutor'); ?>
                </span>

                <span class="attempt-property-value"> :
                    <?php echo date_i18n(get_option('date_format', strtotime($manual_reviewed))).' '.date_i18n(get_option('time_format', strtotime
                        ($manual_reviewed))); ?>
                </span>
            </p>
            <?php
            } ?>

        </div>

        <div class="quiz-attempt-student-info">
            <p class="quiz-attempt-info-row">
		        <?php
		        echo '<span class="attempt-property-name">'. __('Quiz', 'tutor').' </span> <span class="attempt-property-value">  : '."<a href='".admin_url("post.php?post={$comment_post_ID}&action=edit")."'>".get_the_title($comment_post_ID)."</a> </span> ";
		        ?>
            </p>

            <p class="quiz-attempt-info-row">
		        <?php
		        $quiz = tutor_utils()->get_course_by_quiz($comment_post_ID);
		        echo '<span class="attempt-property-name">'.__('Course', 'tutor').'</span> <span class="attempt-property-value"> : '."<a href='".admin_url("post.php?post={$quiz->ID}&action=edit")."'>".get_the_title($quiz->ID)."</a> </span>";
		        ?>
            </p>

            <p class="quiz-attempt-info-row">
                <span class="attempt-property-name"><?php _e('Result', 'tutor'); ?></span>

                <span class="attempt-property-value"> :
                    <?php
                    $earned_marks = tutor_utils()->avalue_dot('marks_earned', $quiz_attempt_info);
                    $total_marks = tutor_utils()->avalue_dot('total_marks', $quiz_attempt_info);
                    $passing_grade = tutor_utils()->avalue_dot('pass_mark_percent', $quiz_attempt_info);
                    $earned_percentage = $earned_marks > 0 ? ( number_format(($earned_marks * 100) / $total_marks)) : 0;


                    echo __('Earned marks ', 'tutor').$earned_marks.__(' out of ', 'tutor').$total_marks." ({$earned_percentage}%) ";

                    if ($earned_percentage >= $passing_grade){
	                    echo '<span class="result-pass">'.__('Pass', 'tutor').'</span>';
                    }else{
	                    echo '<span class="result-fail">'.__('Fail', 'tutor').'</span>';
                    }
                    ?>
                </span>
            </p>

            <p class="quiz-attempt-info-row">
                <span class="attempt-property-name"><?php _e('Quiz Time', 'tutor'); ?></span>
                <span class="attempt-property-value"> :
			        <?php
			        $time_limit_seconds = tutor_utils()->avalue_dot('time_limit_seconds', $quiz_attempt_info);
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
                <th><?php _e('Question', 'tutor'); ?></th>
                <th><?php _e('Status', 'tutor'); ?></th>
                <th><?php _e('Correct', 'tutor'); ?></th>
                <th><?php _e('Given Answers', 'tutor'); ?></th>
                <th><?php _e('Review', 'tutor'); ?></th>
            </tr>
			<?php
			foreach ($answers as $answer_key => $answer){
				?>

                <tr>
                    <td><?php echo get_the_title($answer['questionID']) ?></td>
                    <td><?php echo $answer['status']; ?></td>
                    <td>
						<?php $correct =  tutor_utils()->avalue_dot('has_correct', $answer);
						if ($correct){
							echo '<span class="tutor-status-approved-context"><i class="dashicons dashicons-yes"></i> </span>';
						}else{
							echo '<span class="tutor-status-blocked-context"><i class="dashicons dashicons-no-alt"></i> </span>';
						}
						?>
                    </td>

                    <td>
						<?php
						$answers_lists_by_question = $answer['answers_list'];
						$answer_type = tutor_utils()->avalue_dot('answer_type', $answers_lists_by_question);
						$answer_ids = tutor_utils()->avalue_dot('answer_ids', $answers_lists_by_question);
						echo '<p><strong>'.tutor_utils()->get_question_types($answer_type).'</strong></p>';

						if ($answer_ids){
							$get_answers = tutor_utils()->get_quiz_answers_by_ids($answer_ids);
							if ($get_answers){
								echo '<hr />';
								foreach ($get_answers as $given_answer){
									$formatted_answer = json_decode($given_answer->comment_content);
									echo $formatted_answer->answer_option_text;
								}
							}
						}
						?>
                    </td>

                    <td>
                        <a href="<?php echo admin_url("admin.php?action=review_quiz_answer&attempt_id={$attempt_id}&answer_index={$answer_key}&mark_as=correct"); ?>" title="<?php _e('Mark as correct', 'tutor'); ?>" class="tutor-button button button-success"><i class="dashicons dashicons-yes"></i> </a>

                        <a href="<?php echo admin_url("admin.php?action=review_quiz_answer&attempt_id={$attempt_id}&answer_index={$answer_key}&mark_as=incorrect"); ?>" title="<?php _e('Mark as In correct', 'tutor'); ?>" class="tutor-button button button-danger"><i class="dashicons dashicons-no-alt"></i></a>
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