<?php
/**
 * Question and answer
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */
global $post;
$disable_qa_for_this_course = get_post_meta($post->ID, '_tutor_enable_qa', true)!='yes';
$enable_q_and_a_on_course = tutor_utils()->get_option('enable_q_and_a_on_course');
if ( !$enable_q_and_a_on_course || $disable_qa_for_this_course == 'yes') {
	tutor_load_template( 'single.course.q_and_a_turned_off' );
	return;
}
?>
<?php do_action('tutor_course/question_and_answer/before'); ?>
<div class="tutor-queston-and-answer-wrap">

    <div class="tutor-add-question-wrap">
        <form method="post" id="tutor-ask-question-form">
			<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
            <input type="hidden" value="add_question" name="tutor_action"/>
            <input type="hidden" value="<?php echo get_the_ID(); ?>" name="tutor_course_id"/>

            <div class="tutor-form-group">
                <input type="text" name="question_title" value="" placeholder="<?php _e('Question Title', 'tutor'); ?>" required="required">
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
                <a href="javascript:;" class="tutor_question_cancel tutor-btn tutor-danger"><?php _e('Cancel', 'tutor'); ?></a>
                <button type="submit" class="tutor-btn tutor_ask_question_btn" name="tutor_question_search_btn"><?php _e('Post Question', 'tutor'); ?> </button>
            </div>
        </form>
    </div>

    <div class="tutor_question_answer_wrap">
		<?php
		$questions = tutor_utils()->get_qa_questions(0, 20);

		if (is_array($questions) && count($questions)){
			foreach ($questions as $question) {
				$answers = tutor_utils()->get_qa_answer_by_question($question->comment_ID);
				$profile_url = tutor_utils()->profile_url($question->user_id);
				?>
                <div class="tutor_original_question">
                    <div class="tutor-question-wrap">
                        <div class="question-top-meta">
                            <div class="tutor-question-avater">
                                <a href="<?php echo $profile_url; ?>"> <?php echo tutor_utils()->get_tutor_avatar($question->user_id); ?></a>
                            </div>
                            <p class="review-meta">
                                <a href="<?php echo $profile_url; ?>"><?php echo $question->display_name; ?></a>
                                <span class="tutor-text-mute"><?php echo sprintf(__('%s ago', 'tutor'), human_time_diff(strtotime($question->comment_date_gmt))) ; ?></span>
                            </p>
                        </div>

                        <div class="tutor_question_area">
							<?php echo stripslashes($question->comment_content); ?>
                        </div>
                    </div>
                </div>
				<?php
                    tutor_load_template_from_custom_path(tutor()->path . '/views/qna/qna-single.php', array(
                        'question_id' => $question->comment_ID,
                        'context' => 'course-single-qna-single'
                    ));
                    ?>
				<?php
			}
		}
		?>
    </div>
</div>
<?php do_action('tutor_course/question_and_answer/after'); ?>