<?php
$attempt_id = (int) sanitize_text_field($_GET['attempt_id']);
$attempt = dozent_utils()->get_attempt($attempt_id);
$quiz_attempt_info = dozent_utils()->quiz_attempt_info($attempt_id);
$answers = dozent_utils()->avalue_dot('answers', $quiz_attempt_info);
$manual_reviewed = dozent_utils()->avalue_dot('manual_reviewed', $quiz_attempt_info);
?>

<div class="wrap">
    <h2><?php _e('View Attempts', 'dozent'); ?></h2>

    <div class="dozent-quiz-attempt-info-row">
        <div class="dozent-attempt-student-info">
            <?php
            $user_id = dozent_utils()->avalue_dot('user_id', $attempt);
            $user = get_userdata($user_id);

            $quiz_started_at = dozent_utils()->avalue_dot('quiz_started_at', $attempt);
            $quiz_attempt_status = dozent_utils()->avalue_dot('quiz_attempt_status', $attempt);
            $comment_post_ID = dozent_utils()->avalue_dot('comment_post_ID', $attempt);

            ?>

            <p class="quiz-attempt-info-row"> <?php echo '<span class="attempt-property-name">'.__('Attempt By', 'dozent').' </span><span> : '. $user->display_name.'</span>'; ?></p>

            <p class="quiz-attempt-info-row"> <?php echo '<span class="attempt-property-name">'.__('Attempt At', 'dozent').'</span> <span> : '. date_i18n(get_option('date_format', strtotime($quiz_started_at))).' '.date_i18n(get_option('time_format', strtotime($quiz_started_at))).'</span>'; ?></p>

            <p class="quiz-attempt-info-row">
                <span class="attempt-property-name">
                    <?php echo __('Status', 'dozent'); ?>
                </span>

                <span class="attempt-property-value"> :
                <?php
                $status = ucwords(str_replace('quiz_', '', $quiz_attempt_status));
                echo "<span class='dozent-status-context {$quiz_attempt_status}'>{$status}</span>";
                ?>
                </span>
            </p>



            <?php if ($manual_reviewed){
                ?>
            <p class="quiz-attempt-info-row text-notified">
                 <span class="attempt-property-name">
                <?php _e('Manually reviewed at', 'dozent'); ?>
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
		        echo '<span class="attempt-property-name">'. __('Quiz', 'dozent').' </span> <span class="attempt-property-value">  : '."<a href='".admin_url("post.php?post={$comment_post_ID}&action=edit")."'>".get_the_title($comment_post_ID)."</a> </span> ";
		        ?>
            </p>

            <p class="quiz-attempt-info-row">
		        <?php
		        $quiz = dozent_utils()->get_course_by_quiz($comment_post_ID);
		        if ($quiz) {
			        echo '<span class="attempt-property-name">' . __( 'Course', 'dozent' ) . '</span> <span class="attempt-property-value"> : ' . "<a href='" . admin_url( "post.php?post={$quiz->ID}&action=edit" ) . "'>" . get_the_title( $quiz->ID ) . "</a> </span>";
		        }
		        ?>
            </p>

            <p class="quiz-attempt-info-row">
                <span class="attempt-property-name"><?php _e('Result', 'dozent'); ?></span>

                <span class="attempt-property-value"> :
                    <?php
                    $earned_marks = dozent_utils()->avalue_dot('marks_earned', $quiz_attempt_info);
                    $total_marks = dozent_utils()->avalue_dot('total_marks', $quiz_attempt_info);
                    $passing_grade = dozent_utils()->avalue_dot('pass_mark_percent', $quiz_attempt_info);
                    $earned_percentage = $earned_marks > 0 ? ( number_format(($earned_marks * 100) / $total_marks)) : 0;


                    echo __('Earned marks ', 'dozent').$earned_marks.__(' out of ', 'dozent').$total_marks." ({$earned_percentage}%) ";

                    if ($earned_percentage >= $passing_grade){
	                    echo '<span class="result-pass">'.__('Pass', 'dozent').'</span>';
                    }else{
	                    echo '<span class="result-fail">'.__('Fail', 'dozent').'</span>';
                    }
                    ?>
                </span>
            </p>

            <p class="quiz-attempt-info-row">
                <span class="attempt-property-name"><?php _e('Quiz Time', 'dozent'); ?></span>
                <span class="attempt-property-value"> :
			        <?php
			        $time_limit_seconds = dozent_utils()->avalue_dot('time_limit_seconds', $quiz_attempt_info);
			        echo dozent_utils()->seconds_to_time_context($time_limit_seconds);
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
                <th><?php _e('Question', 'dozent'); ?></th>
                <th><?php _e('Status', 'dozent'); ?></th>
                <th><?php _e('Correct', 'dozent'); ?></th>
                <th><?php _e('Given Answers', 'dozent'); ?></th>
                <th><?php _e('Review', 'dozent'); ?></th>
            </tr>
			<?php
			foreach ($answers as $answer_key => $answer){
				?>

                <tr>
                    <td><?php echo get_the_title($answer['questionID']) ?></td>
                    <td><?php echo $answer['status']; ?></td>
                    <td>
						<?php $correct =  dozent_utils()->avalue_dot('has_correct', $answer);
						if ($correct){
							echo '<span class="dozent-status-approved-context"><i class="dashicons dashicons-yes"></i> </span>';
						}else{
							echo '<span class="dozent-status-blocked-context"><i class="dashicons dashicons-no-alt"></i> </span>';
						}
						?>
                    </td>

                    <td>
						<?php
						$answers_lists_by_question = $answer['answers_list'];
						$answer_type = dozent_utils()->avalue_dot('answer_type', $answers_lists_by_question);
						$answer_ids = dozent_utils()->avalue_dot('answer_ids', $answers_lists_by_question);
						echo '<p><strong>'.dozent_utils()->get_question_types($answer_type).'</strong></p>';

						if ($answer_ids){
							$get_answers = dozent_utils()->get_quiz_answers_by_ids($answer_ids);
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
                        <a href="<?php echo admin_url("admin.php?action=review_quiz_answer&attempt_id={$attempt_id}&answer_index={$answer_key}&mark_as=correct"); ?>" title="<?php _e('Mark as correct', 'dozent'); ?>" class="dozent-button button button-success"><i class="dashicons dashicons-yes"></i> </a>

                        <a href="<?php echo admin_url("admin.php?action=review_quiz_answer&attempt_id={$attempt_id}&answer_index={$answer_key}&mark_as=incorrect"); ?>" title="<?php _e('Mark as In correct', 'dozent'); ?>" class="dozent-button button button-danger"><i class="dashicons dashicons-no-alt"></i></a>
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