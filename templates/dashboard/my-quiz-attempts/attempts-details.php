<?php

/**
 * Quiz Attempts Details
 *
 * @since v.1.6.4
 *
 * @author Themeum
 * @url https://themeum.com
 *
 *
 * @package TutorLMS/Templates
 * @version 1.6.4
 */

if ( ! defined( 'ABSPATH' ) )
exit;

$user_id = get_current_user_id();
$attempt_id = (int) sanitize_text_field($_GET['attempt_id']);
$attempt_data = tutor_utils()->get_attempt($attempt_id);

?>

<div>
    <?php $attempts_page = tutor_utils()->get_tutor_dashboard_page_permalink('my-quiz-attempts'); ?>
    <a class="prev-btn" href="<?php echo $attempts_page; ?>">
        <span>&leftarrow;</span>
        <?php _e('Back to Attempt List', 'tutor'); ?>
    </a>
</div>

<div class="tutor-quiz-attempt-review-wrap">
    <div class="attempt-answers-header">
        <div class="attempt-header-course"><?php echo __('Course:','tutor')." <a href='" .get_permalink($attempt_data->course_id)."'>".get_the_title($attempt_data->course_id)."</a>"; ?></div>
        <div class="attempt-header-quiz"><?php echo "<a href='" .get_permalink($attempt_data->quiz_id)."'>".get_the_title($attempt_data->quiz_id)."</a>"; ?></div>
    </div>
</div>

<?php 
    tutor_load_template_from_custom_path(tutor()->path . '/views/quiz/attempt-details.php', array(
        'attempt_id' => $attempt_id,
        'attempt_data' => $attempt_data,
        'user_id' => $user_id
    ));
?>

<?php $feedback = get_post_meta($attempt_id ,'instructor_feedback', true); ?>
<?php if($feedback){ ?>
    <div class="tutor-quiz-attempt-review-wrap">
        <div class="quiz-attempt-answers-wrap">
            <div class="attempt-answers-header">
                <div class="attempt-header-quiz"><?php _e('Instructor Feedback', 'tutor'); ?></div>
            </div>
            <div class="instructor-feedback-content">
                <p><?php echo $feedback; ?></p>
            </div>
        </div>
    </div>
<?php } ?>