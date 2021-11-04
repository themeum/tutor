<?php
/**
 * Quiz Review Frontend
 *
 * @since v.1.4.0
 *
 * @author Themeum
 * @url https://themeum.com
 * @package Tutor
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

    $attempt_id = (int) sanitize_text_field($_GET['attempt_id']);
    $attempt_data = tutor_utils()->get_attempt($attempt_id);
    $user_id = tutor_utils()->avalue_dot('user_id', $attempt_data);
?>

<div>
    <?php $attempts_page = tutor_utils()->get_tutor_dashboard_page_permalink('quiz-attempts'); ?>
    <a class="prev-btn" href="<?php echo $attempts_page; ?>"><span>&leftarrow;</span><?php _e('Back to Attempt List', 'tutor'); ?></a>
</div>

<div class="attempt-answers-header">
    <div class="attempt-header-quiz"><?php echo __('Quiz:','tutor')." <a href='" .get_permalink($attempt_data->quiz_id)."'>".get_the_title($attempt_data->quiz_id)."</a>"; ?></div>
    <div class="attempt-header-course"><?php echo __('Course:','tutor')." <a href='" .get_permalink($attempt_data->course_id)."'>".get_the_title($attempt_data->course_id)."</a>"; ?></div>
</div>
    
<?php 
    add_action('tutor_quiz_review/thead/column', function(){
        echo '<th></th>';
    });

    add_action( 'tutor_quiz_review/tbody/column', function($attempt) {
        ?>
        <td data-th="<?php _e('Manual Review', 'tutor'); ?>" class="tutor-text-center tutor-bg-gray-10">
            <a href="javascript:;" data-attempt-id="<?php echo $attempt_id; ?>" data-attempt-answer-id="<?php echo $answer->attempt_answer_id; ?>" data-mark-as="correct" title="<?php _e('Mark as correct', 'tutor'); ?>" class="quiz-manual-review-action tutor-mr-10 tutor-icon-rounded tutor-text-success">
                <i class="tutor-icon-mark"></i> 
            </a>
            <a href="javascript:;" data-attempt-id="<?php echo $attempt_id; ?>" data-attempt-answer-id="<?php echo $answer->attempt_answer_id; ?>" data-mark-as="incorrect" title="<?php _e('Mark as In correct', 'tutor'); ?>" class="quiz-manual-review-action tutor-icon-rounded tutor-text-danger">
                <i class="tutor-icon-line-cross"></i>
            </a>
        </td>
        <?php
    } );

    tutor_load_template_from_custom_path(tutor()->path . '/views/quiz/attempt-details.php', array(
        'attempt_id' => $attempt_id,
        'attempt_data' => $attempt_data,
        'user_id' => $user_id
    ));
?>

<div class="quiz-attempt-answers-wrap">
    <div class="attempt-answers-header">
        <div class="attempt-header-quiz">
            <?php _e('Instructor Feedback', 'tutor'); ?>
        </div>
    </div>
    <div class="tutor-instructor-feedback-wrap">
        <textarea class="tutor-instructor-feedback-content tutor-form-control"><?php 
            echo get_post_meta($attempt_id, 'instructor_feedback', true); 
        ?></textarea>

        <a class="tutor-btn tutor-instructor-feedback tutor-mt-10" data-attemptid="<?php echo $attempt_id; ?>" data-toast_success_message="<?php _e('Updated', 'tutor'); ?>">
            <?php _e('Update', 'tutor'); ?>
        </a>
    </div>
</div>