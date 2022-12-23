<?php
/**
 * Image answer
 *
 * @package Tutor\Templates
 * @subpackage Single\Quiz\Parts
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @version 1.0.0
 */

?>
<div class="quiz-question-ans-choice-area tutor-mt-40 question-type-<?php echo esc_attr( $question_type ); ?> <?php echo $answer_required ? 'quiz-answer-required' : ''; ?> ">
	<?php
	if ( is_array( $answers ) && count( $answers ) ) {
		foreach ( $answers as $answer ) {
			?>
		<div class="tutor-image-answer">
			<?php
			if ( intval( $answer->image_id ) ) {
				?>
			<div class="quiz-short-ans-image tutor-mb-32 tutor-mb-md-30">
				<?php
				echo wp_kses(
					'<img src="' . wp_get_attachment_image_url( $answer->image_id, 'full' ) . '" />',
					array(
						'img' => array( 'src' => true ),
					)
				);
				?>
			</div>
			<?php } ?>
			<div class="quiz-question-ans-choice">
				<input  type="text" 
						class="tutor-form-control" 
						placeholder="<?php esc_attr_e( 'Write your answer here', 'tutor' ); ?>" 
						name="attempt[<?php echo esc_attr( $is_started_quiz->attempt_id ); ?>][quiz_question][<?php echo esc_attr( $question->question_id ); ?>][answer_id][<?php echo esc_attr( $answer->answer_id ); ?>]" />
			</div>
		</div>
			<?php
		}
	}
	?>
</div>
