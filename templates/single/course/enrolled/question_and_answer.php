<?php
/**
 * Question and answer
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

$enable_q_and_a_on_course = dozent_utils()->get_option('enable_q_and_a_on_course');
if ( ! $enable_q_and_a_on_course) {
	dozent_load_template( 'single.course.q_and_a_turned_off' );
	return;
}
?>
<?php do_action('dozent_course/question_and_answer/before'); ?>
<div class="dozent-queston-and-answer-wrap">

    <div class="dozent-question-top">
        <div class="dozent-ask-question-btn-wrap">
            <a href="javascript:;" class="dozent-ask-question-btn dozent-btn"> <?php _e('Ask a new question', 'dozent'); ?> </a>
        </div>
    </div>

    <div class="dozent-add-question-wrap" style="display: none;">
        <form method="post" id="dozent-ask-question-form">
			<?php wp_nonce_field( dozent()->nonce_action, dozent()->nonce ); ?>
            <input type="hidden" value="add_question" name="dozent_action"/>
            <input type="hidden" value="<?php echo get_the_ID(); ?>" name="dozent_course_id"/>

            <div class="dozent-form-group">
                <input type="text" name="question_title" value="" placeholder="<?php _e('Question Title', 'dozent'); ?>">
            </div>

            <div class="dozent-form-group">
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

            <div class="dozent-form-group">
                <a href="javascript:;" class="dozent_question_cancel dozent-button dozent-danger"><?php _e('Cancel', 'dozent'); ?></a>
                <button type="submit" class="dozent-button dozent-success dozent_ask_question_btn" name="dozent_question_search_btn"><?php _e('Post Question', 'dozent'); ?> </button>
            </div>
        </form>
    </div>

    <div class="dozent_question_answer_wrap">
		<?php
		$questions = dozent_utils()->get_top_question();

		if (is_array($questions) && count($questions)){
			foreach ($questions as $question){
				$answers = dozent_utils()->get_qa_answer_by_question($question->comment_ID);
				$profile_url = dozent_utils()->profile_url($question->user_id);
				?>
                <div class="dozent_original_question">
                    <div class="dozent-question-wrap">
                        <div class="question-top-meta">
                            <div class="dozent-question-avater">
                                <a href="<?php echo $profile_url; ?>"> <?php echo dozent_utils()->get_dozent_avatar($question->user_id); ?></a>
                            </div>
                            <p class="review-meta">
                                <a href="<?php echo $profile_url; ?>"><?php echo $question->display_name; ?></a> -
                                <span class="dozent-text-mute"><?php _e(sprintf('%s ago', human_time_diff(strtotime($question->comment_date))), 'lms'); ?></span>
                            </p>
                        </div>

                        <div class="dozent_question_area">
                            <p><strong><?php echo $question->question_title; ?> </strong></p>
							<?php echo wpautop($question->comment_content); ?>
                        </div>
                    </div>
                </div>

                <div class="dozent_admin_answers_list_wrap">
					<?php
					if (is_array($answers) && count($answers)){
						foreach ($answers as $answer){
							$answer_profile = dozent_utils()->profile_url($answer->user_id);
							?>
                            <div class="dozent_individual_answer <?php echo ($question->user_id == $answer->user_id) ? 'dozent-bg-white' : 'dozent-bg-light'
							?> ">
                                <div class="dozent-question-wrap">
                                    <div class="question-top-meta">
                                        <div class="dozent-question-avater">
                                            <a href="<?php echo $answer_profile; ?>"> <?php echo dozent_utils()->get_dozent_avatar($answer->user_id); ?></a>
                                        </div>
                                        <p class="review-meta">
                                            <a href="<?php echo $answer_profile; ?>"><?php echo $answer->display_name; ?></a> -
                                            <span class="dozent-text-mute">
										        <?php _e(sprintf('%s ago', human_time_diff(strtotime($answer->comment_date))), 'lms'); ?>
									        </span>
                                        </p>
                                    </div>

                                    <div class="dozent_question_area">
										<?php echo wpautop(stripslashes($answer->comment_content)); ?>
                                    </div>
                                </div>
                            </div>
							<?php
						}
					}
					?>
                </div>

                <div class="dozent_add_answer_row">
                    <div class="dozent_add_answer_wrap " data-question-id="<?php echo $question->comment_ID; ?>">
                        <div class="dozent_wp_editor_show_btn_wrap">
                            <a href="javascript:;" class="dozent_wp_editor_show_btn dozent-button dozent-success"><?php _e('Add an answer', 'dozent'); ?></a>
                        </div>
                        <div class="dozent_wp_editor_wrap" style="display: none;">
                            <form method="post" class="dozent-add-answer-form">
								<?php wp_nonce_field( dozent()->nonce_action, dozent()->nonce ); ?>
                                <input type="hidden" value="dozent_add_answer" name="dozent_action"/>
                                <input type="hidden" value="<?php echo $question->comment_ID; ?>" name="question_id"/>

                                <div class="dozent-form-group">
                                    <textarea id="dozent_answer_<?php echo $question->comment_ID; ?>" name="answer" class="dozent_add_answer_textarea" placeholder="<?php _e('Write your answer here...', 'dozent'); ?>"></textarea>
                                </div>

                                <div class="dozent-form-group">
                                    <a href="javascript:;" class="dozent_cancel_wp_editor dozent-button dozent-danger"><?php _e('Cancel', 'dozent'); ?></a>
                                    <button type="submit" class="dozent-button dozent_add_answer_btn dozent-success" name="dozent_answer_search_btn">
										<?php _e('Add Answer', 'dozent'); ?>
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
<?php do_action('dozent_course/question_and_answer/after'); ?>