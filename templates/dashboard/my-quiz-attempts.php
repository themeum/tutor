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


if(isset($_GET['view_quiz_attempt_id']) && get_tutor_option('tutor_quiz_student_attempt_view_in_profile')) {
    $_GET['attempt_id'] = $_GET['view_quiz_attempt_id'];
    echo tutor_get_template_html('dashboard.my-quiz-attempts.attempts-details');
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
            'column_hook' => array(
                'tutor_quiz/my_attempts/table/thead/col',
                'tutor_quiz/my_attempts/table/tbody/col'
            )
        ));
    } else {
        echo __('You have not attempted any quiz yet', 'tutor');
    } 
?>