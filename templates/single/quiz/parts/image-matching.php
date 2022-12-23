<?php
/**
 * Image matching
 *
 * @package Tutor\Templates
 * @subpackage Single\Quiz\Parts
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @version 1.0.0
 */

?>

<div id="quiz-image-matching-ans-area" 
	 class="quiz-question-ans-choice-area tutor-mt-40 question-type-<?php echo esc_attr( $question_type ); ?> <?php echo $answer_required ? 'quiz-answer-required' : ''; ?> ">
	<div class="matching-quiz-question-desc tutor-draggable tutor-mb-44">
		<?php
			$rand_answers = \Tutor\Models\QuizModel::get_answers_by_quiz_question( $question->question_id, true );
			foreach ( $rand_answers as $rand_answer ) {
			?>
		<div class="tutor-quiz-border-box" draggable="true">
			<?php
			if ( 'matching' === $question_type ) {
				$markup = '<span class="tutor-dragging-text-conent tutor-fs-6 tutor-color-black">' . stripslashes( $rand_answer->answer_two_gap_match ) . '</span>';
				echo wp_kses(
					$markup,
					array(
						'span' => array( 'class' => true ),
					)
				);
			} else {
				$markup = '<span class="tutor-dragging-text-conent tutor-fs-6 tutor-color-black">' . stripslashes( $rand_answer->answer_title ) . '</span>';
				echo wp_kses(
					$markup,
					array(
						'span' => array( 'class' => true ),
					)
				);
			}
			?>
			<span class="tutor-icon-hamburger-menu tutor-color-black-fill"></span>
			<input  type="hidden" 
					data-name="attempt[<?php echo esc_attr( $is_started_quiz->attempt_id ); ?>][quiz_question][<?php echo esc_attr( $question->question_id ); ?>][answers][]" 
					value="<?php echo esc_attr( $rand_answer->answer_id ); ?>" >
		</div>
		<?php } ?>
	</div>
	<div class="quiz-image-matching-ans tutor-d-flex tutor-align-start">
		<?php
		if ( is_array( $answers ) && count( $answers ) ) {
			foreach ( $answers as $answer ) {
				?>
		<div class="quiz-image-box">
			<div class="quiz-image tutor-d-flex tutor-mb-16">
				<img src="<?php echo esc_url( wp_get_attachment_image_url( $answer->image_id, 'full' ) ); ?>" />
			</div>
			<div class="tutor-quiz-dotted-box tutor-dropzone tutor-d-flex tutor-align-center">
				<span class="tutor-dragging-text-conent tutor-fs-6 tutor-color-black">
				<?php esc_html_e( 'Drag your answer', 'tutor' ); ?>
				</span>
			</div>
		</div>
				<?php
			}
		}
		?>
	</div>
</div>
