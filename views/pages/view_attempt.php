<?php
/**
 * Student's Quiz Review Backend
 */

$attempt_id = (int) sanitize_text_field($_GET['view_quiz_attempt_id']);
$attempt = tutor_utils()->get_attempt($attempt_id);
$attempt_data = $attempt;
$user_id = tutor_utils()->avalue_dot('user_id', $attempt_data);

if (!$attempt){
    ?>
    <h1><?php _e('Attempt not found', 'tutor'); ?></h1>
    <?php
    return;
}

$quiz_attempt_info = tutor_utils()->quiz_attempt_info($attempt->attempt_info);
$answers = tutor_utils()->get_quiz_answers_by_attempt_id($attempt->attempt_id);

$user_id = tutor_utils()->avalue_dot('user_id', $attempt);
$user = get_userdata($user_id);
?>

<div class="tutor-quiz-attempt-details-wrapper">
    <?php 
        tutor_load_template_from_custom_path(tutor()->path . '/views/quiz/attempt-details.php', array(
            'attempt_id' => $attempt_id,
            'attempt_data' => $attempt_data,
            'user_id' => $user_id,
            'context' => 'backend-dashboard-students-attempts'
        ));
    ?>
</div>

<div class="wrap">
    <div class="quiz-attempt-answers-wrap">
        <div class="attempt-answers-header tutor-mb-5">
            <div class="attempt-header-quiz">
                <strong><?php _e('Instructor Feedback', 'tutor'); ?></strong>
            </div>
        </div>
        <div class="tutor-instructor-feedback-wrap">
            <textarea class="tutor-form-control"><?php 
                echo get_post_meta($attempt_id, 'instructor_feedback', true); 
            ?></textarea>
            <button class="tutor-btn <?php echo is_admin() ? 'tutor-btn-wordpress' : ''; ?> tutor-instructor-feedback tutor-mt-5" data-attemptid="<?php echo $attempt_id; ?>" data-toast_success_message="<?php _e('Updated', 'tutor'); ?>">
                <?php _e('Update', 'tutor'); ?>
            </button>
        </div>
    </div>
</div>