<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */


global $post;
$currentPost = $post;
$quiz_id = get_the_ID();
$previous_attempts = tutor_utils()->quiz_attempts();
$attempted_count = is_array($previous_attempts) ? count($previous_attempts) : 0;
$attempts_allowed = tutor_utils()->get_quiz_option($quiz_id, 'attempts_allowed', 0);
$attempt_remaining = $attempts_allowed - $attempted_count;
?>
<?php
	if ($attempted_count !== 0) {
?>
<div id="tutor-quiz-content" class="tutor-quiz-content tutor-quiz-content-<?php the_ID(); ?>">
	<?php

    do_action('tutor_quiz/content/before', $quiz_id);

    do_action('tutor_quiz/content/after', $quiz_id);
	?>
</div>
<?php } ?>