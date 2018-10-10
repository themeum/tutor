<?php
/**
 * Question and answer
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

?>


<div class="tutor-queston-and-answer-wrap">


    <div class="tutor-question-top">

        <div class="tutor-question-search-form">
            <form method="get">
                <input type="text" name="q" value="" placeholder="<?php _e('search for a question', 'tutor'); ?>">
                <button type="submit" name="tutor_question_search_btn"><?php _e('Search Question', 'tutor'); ?> </button>
            </form>
        </div>


        <div class="tutor-ask-question-btn-wrap">
            <a href="javascript:;" class="tutor-ask-question-btn tutor-btn"> <?php _e('Ask a new question', 'tutor'); ?> </a>
        </div>

    </div>

    <div class="tutor-add-question-wrap">


        <form method="post" id="tutor-ask-question-form">
	        <?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
            <input type="hidden" value="add_question" name="tutor_action"/>
            <input type="hidden" value="<?php echo get_the_ID(); ?>" name="tutor_course_id"/>

            <div class="tutor-form-group">
                <input type="text" name="question_title" value="" placeholder="<?php _e('Question Title', 'tutor'); ?>">
            </div>

            <div class="tutor-form-group">
                <?php
                $settings = array(
                    'media_buttons' => false,
	                'quicktags' => false,
                    'editor_height' => 100,
                );
                wp_editor(null, 'question', $settings);
                ?>
            </div>

            <div class="tutor-form-group">
                <a href="javascript:;" class="tutor_question_cancel"><?php _e('Cancel', 'tutor'); ?></a>
                <button type="submit" class="tutor-btn tutor_ask_question_btn" name="tutor_question_search_btn"><?php _e('Post Question', 'tutor'); ?> </button>
            </div>
        </form>


    </div>




</div>
