<?php
/**
 * Quiz Attempts, I attempted to courses
 *
 * @since v.1.1.2
 *
 * @author Themeum
 * @url https://themeum.com
 *
 *
 * @package TutorLMS/Templates
 * @version 1.6.4
 */


if(isset($_GET['view_quiz_attempt_id'])) {
    // Load single attempt details if ID provided
    include __DIR__ . '/my-quiz-attempts/attempts-details.php';
    return;
}

$previous_attempts = tutor_utils()->get_all_quiz_attempts_by_user();
$attempted_count = is_array($previous_attempts) ? count($previous_attempts) : 0;
?>

<h3><?php _e('My Quiz Attempts', 'tutor'); ?></h3>
<?php
    if ($attempted_count){
        tutor_load_template_from_custom_path(tutor()->path . '/views/quiz/attempt-table.php', array(
            'attempt_list' => $previous_attempts,
            'context' => 'frontend-dashboard-my-attempts'
        ));
    } else {
        echo __('You have not attempted any quiz yet', 'tutor');
    } 
?>