<?php
/**
 * @package TutorLMS/Templates
 * @version 1.6.4
 */

tutor_load_template_from_custom_path(tutor()->path . '/views/quiz/attempt-table.php', array(
    'attempt_list' => $previous_attempts,
    'context' => 'course-single-previous'
));
?>