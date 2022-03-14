<?php

/**
 * My Quiz Attempts Details
 *
 * @since v.1.6.4
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user_id      = get_current_user_id();
$attempt_id   = (int) sanitize_text_field( $_GET['view_quiz_attempt_id'] );
$attempt_data = tutor_utils()->get_attempt( $attempt_id );
?>

<div class="tutor-quiz-attempt-details-wrapper">
	<?php
		tutor_load_template_from_custom_path(
			tutor()->path . '/views/quiz/attempt-details.php',
			array(
				'attempt_id'   => $attempt_id,
				'attempt_data' => $attempt_data,
				'user_id'      => $user_id,
				'context'      => 'frontend-dashboard-my-attempts',
			)
		);
		?>
</div>

