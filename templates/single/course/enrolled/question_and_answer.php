<?php
/**
 * Question and answer
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

$enable_q_and_a_on_course = tutor_utils()->get_option('enable_q_and_a_on_course');
if ( ! $enable_q_and_a_on_course) {
	tutor_load_template( 'single.course.q_and_a_turned_off' );
	return;
}
?>
<?php do_action('tutor_course/question_and_answer/before'); ?>
<div class="tutor-queston-and-answer-wrap">

    <div class="tutor-question-top">
        <div class="tutor-ask-question-btn-wrap">
            <a href="javascript:;" class="tutor-ask-question-btn tutor-btn"> <?php _e('Ask a new question', 'tutor'); ?> </a>
        </div>
    </div>

    <div class="tutor-add-question-wrap" style="display: none;">
        <form method="post" id="tutor-ask-question-form">
			<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
            <input type="hidden" value="add_question" name="tutor_action"/>
            <input type="hidden" value="<?php echo get_the_ID(); ?>" name="tutor_course_id"/>

            <div class="tutor-form-group">
                <input type="text" name="question_title" value="" placeholder="<?php _e('Question Title', 'tutor'); ?>">
            </div>

            <div class="tutor-form-group">
				<?php
				$editor_settings = array(
					'teeny' => true,
					'media_buttons' => false,
					'quicktags' => false,
					'editor_height' => 100,
				);
				wp_editor(null, 'question', $editor_settings);
				?>
            </div>

            <div class="tutor-form-group">
                <a href="javascript:;" class="tutor_question_cancel"><?php _e('Cancel', 'tutor'); ?></a>
                <button type="submit" class="tutor-btn tutor_ask_question_btn" name="tutor_question_search_btn"><?php _e('Post Question', 'tutor'); ?> </button>
            </div>
        </form>
    </div>

    <div class="tutor_question_answer_wrap">
		<?php
		$questions = tutor_utils()->get_top_question();
		?>


		<?php
		if (is_array($questions) && count($questions)){
			foreach ($questions as $question){
				$answers = tutor_utils()->get_qa_answer_by_question($question->comment_ID);
				$profile_url = tutor_utils()->profile_url($question->user_id);
				?>
                <div class="tutor_original_question">
                    <div class="question-left">
                        <a href="<?php echo $profile_url; ?>"> <?php echo tutor_utils()->get_tutor_avatar($question->user_id); ?></a>
                    </div>

                    <div class="question-right">
                        <div class="question-top-meta">
                            <p class="review-meta">
                                <a href="<?php echo $profile_url; ?>"><?php echo $question->display_name; ?></a> -
                                <span class="tutor-text-mute"><?php _e(sprintf('%s ago', human_time_diff(strtotime($question->comment_date))), 'lms'); ?></span>
                            </p>
                        </div>

                        <div class="tutor_question_area">
                            <p><strong><?php echo $question->question_title; ?> </strong></p>
							<?php echo wpautop($question->comment_content); ?>
                        </div>
                    </div>
                </div>

                <div class="tutor_admin_answers_list_wrap">
					<?php
					if (is_array($answers) && count($answers)){
						foreach ($answers as $answer){
							$answer_profile = tutor_utils()->profile_url($answer->user_id);
							?>
                            <div class="tutor_individual_answer <?php echo ($question->user_id == $answer->user_id) ? 'tutor-bg-white' : 'tutor-bg-light'
							?> ">
                                <div class="question-left">
                                    <a href="<?php echo $answer_profile; ?>"> <?php echo tutor_utils()->get_tutor_avatar($answer->user_id); ?></a>
                                </div>

                                <div class="question-right">
                                    <div class="question-top-meta">
                                        <p class="review-meta">


                                            <a href="<?php echo $answer_profile; ?>"><?php echo $answer->display_name; ?></a> -
                                            <span class="tutor-text-mute">
										<?php _e(sprintf('%s ago', human_time_diff(strtotime($answer->comment_date))), 'lms'); ?>
									</span>
                                        </p>
                                    </div>

                                    <div class="tutor_question_area">
										<?php echo wpautop(stripslashes($answer->comment_content)); ?>
                                    </div>
                                </div>
                            </div>
							<?php
						}
					}
					?>
                </div>

                <div class="tutor_add_answer_row">
                    <div class="tutor_qa_avatar_left">
                        <a href="<?php echo $profile_url; ?>"> <?php echo tutor_utils()->get_tutor_avatar($question->user_id); ?></a>
                    </div>

                    <div class="tutor_add_answer_wrap " data-question-id="<?php echo $question->comment_ID; ?>">
                        <div class="tutor_wp_editor_show_btn_wrap">
                            <a href="javascript:;" class="tutor_wp_editor_show_btn tutor-btn"><?php _e('Add an answer', 'tutor'); ?></a>
                        </div>

                        <div class="tutor_wp_editor_wrap" style="display: none;">
                            <form method="post" class="tutor-add-answer-form">
								<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
                                <input type="hidden" value="tutor_add_answer" name="tutor_action"/>
                                <input type="hidden" value="<?php echo $question->comment_ID; ?>" name="question_id"/>

                                <div class="tutor-form-group">
                                    <textarea id="tutor_answer_<?php echo $question->comment_ID; ?>" name="answer" class="tutor_add_answer_textarea" placeholder="<?php _e('Write your answer here...', 'tutor'); ?>"></textarea>
                                </div>

                                <div class="tutor-form-group">
                                    <a href="javascript:;" class="tutor_cancel_wp_editor"><?php _e('Cancel', 'tutor'); ?></a>
                                    <button type="submit" class="tutor-btn tutor_add_answer_btn" name="tutor_answer_search_btn">
										<?php _e('Add Answer', 'tutor'); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>

				<?php
			}
		}
		?>
    </div>

</div>
<?php do_action('tutor_course/question_and_answer/after'); ?>