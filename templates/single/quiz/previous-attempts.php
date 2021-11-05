<?php
/**
 * @package TutorLMS/Templates
 * @version 1.6.4
 */

if(isset($_GET['view_quiz_attempt_id'])) {
    // Load single attempt details if ID provided
    $attempt_id = (int) sanitize_text_field(tutils()->array_get('view_quiz_attempt_id', $_GET));
    if ($attempt_id) {
        $user_id = get_current_user_id();
        $attempt_data = tutils()->get_attempt($attempt_id);

        tutor_load_template_from_custom_path(tutor()->path . '/views/quiz/attempt-details.php', array(
            'attempt_id' => $attempt_id,
            'attempt_data' => $attempt_data,
            'user_id' => $user_id,
            'context' => 'course-single-previous-attempts'
        ));
        return;
    }
}

tutor_load_template_from_custom_path(tutor()->path . '/views/quiz/attempt-table.php', array(
    'attempt_list' => $previous_attempts,
    'context' => 'course-single-previous-attempts'
));
?>