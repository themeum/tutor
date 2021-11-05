<?php
/**
 * Student's Quiz Review Frontend
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

    $attempt_id = (int) sanitize_text_field($_GET['view_quiz_attempt_id']);
    $attempt_data = tutor_utils()->get_attempt($attempt_id);
    $user_id = tutor_utils()->avalue_dot('user_id', $attempt_data);
?>

<div class="wrap">
    <div class="tutor-quiz-attempt-details-wrapper">
        <?php 
            tutor_load_template_from_custom_path(tutor()->path . '/views/quiz/attempt-details.php', array(
                'attempt_id' => $attempt_id,
                'attempt_data' => $attempt_data,
                'user_id' => $user_id,
                'context' => 'frontend-dashboard-students-attempts'
            ));
        ?>
    </div>

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
</div>