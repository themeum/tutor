<?php
/**
 * Open ended
 *
 * @package Tutor\Templates
 * @subpackage Single\Quiz\Parts
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @version 1.0.0
 */

?>

<div class="quiz-question-ans-choice-area tutor-mt-40 question-type-<?php echo esc_attr( $question_type ); ?> <?php echo $answer_required ? 'quiz-answer-required' : ''; ?>">
	<div class="quiz-question-ans-choice">
		<textarea class="tutor-form-control question_type_<?php echo esc_attr( $question_type ); ?>" name="attempt[<?php echo esc_attr( $is_started_quiz->attempt_id ); ?>][quiz_question][<?php echo esc_attr( $question->question_id ); ?>]"></textarea>
	</div>
	<?php
	if ( 'open_ended' === $question_type ) {
		$get_option_meta = tutor_utils()->get_quiz_option( $quiz_id );
		if ( isset( $get_option_meta['open_ended_answer_characters_limit'] ) ) {
			if ( '' != $get_option_meta['open_ended_answer_characters_limit'] ) {
				$characters_limit = $get_option_meta['open_ended_answer_characters_limit'];
				$markup           = '<p class="answer_limit_desc">' . __( 'characters remaining', 'tutor' ) . ' :<span class="characters_remaining">' . $characters_limit . '</span> </p>';
				echo wp_kses(
					$markup,
					array(
						'p'    => array( 'class' => true ),
						'span' => array( 'class' => true ),
					)
				);
			}
		}
	}
	?>
</div>
