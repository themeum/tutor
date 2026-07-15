<?php
/**
 * My Quiz Attempts Details
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\My_Quiz_Attempts
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.6.4
 */

use TUTOR\Input;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user_id      = get_current_user_id();
$attempt_id   = Input::get( 'attempt_id', 0, Input::TYPE_INT );
$attempt_data = tutor_utils()->get_attempt( $attempt_id );
$quiz_id      = (int) tutor_utils()->avalue_dot( 'quiz_id', $attempt_data );
$back_url     = remove_query_arg( 'attempt_id' );
?>

<div class="tutor-quiz-attempt-details-wrapper">
	<?php
		tutor_load_template(
			'shared.components.quiz.attempt-details',
			array(
				'attempt_id'   => $attempt_id,
				'attempt_data' => $attempt_data,
				'quiz_id'      => $quiz_id,
				'user_id'      => $user_id,
				'back_url'     => $back_url,
			)
		);
		?>
</div>
