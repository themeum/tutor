<?php
/**
 * Matching
 *
 * @package Tutor\Templates
 * @subpackage Single\Quiz\Parts
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @version 1.0.0
 */

?>

<div id="quiz-matching-ans-area" 
	 class="quiz-question-ans-choice-area tutor-mt-40 question-type-<?php echo esc_attr( $question_type ); ?> <?php echo $answer_required ? 'quiz-answer-required' : ''; ?> ">
	<div class="matching-quiz-question-desc tutor-d-flex tutor-align-start">
		<?php
		$rand_answers = \Tutor\Models\QuizModel::get_answers_by_quiz_question( $question->question_id, true );
		foreach ( $rand_answers as $rand_answer ) {
			?>
			<div class="" style="display:flex; flex-direction: column; row-gap: 10px;">
				<div class="tutor-draggable">
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
				</div>
				<?php if ( 'image' === $rand_answer->answer_view_format || 'text_image' === $rand_answer->answer_view_format ) : ?>
					<div class="tutor-mb-32">
						<?php
						if ( isset( $rand_answer->image_id ) ) :
							$image_url = wp_get_attachment_url( $rand_answer->image_id );
							?>
							<img src="<?php echo esc_url( $image_url ); ?>" alt="" style="max-height: 240px;">
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php } ?>
	</div>
	
	<?php
	if ( is_array( $answers ) && count( $answers ) ) {
		$answer_i = 0;
		foreach ( $answers as $answer ) {
			$answer_i++;
			?>
			<div class="quiz-matching-ans">
				<div class="tutor-quiz-ans-no  tutor-fs-6 tutor-fw-medium  tutor-color-black">
					<?php
					if ( $answer_i < 9 ) {
						echo 0;
					}
					echo esc_html( $answer_i . '. ' );

					echo esc_html( stripslashes( $answer->answer_title ) );
					?>
					
				</div>
				<div class="quiz-matching-ans-item">
					<span class="tutor-fs-6 tutor-fw-medium  tutor-color-black">-</span>
					<div class="tutor-quiz-dotted-box tutor-dropzone">
						<span class="tutor-dragging-text-conent tutor-fs-6 tutor-color-black">
						<?php esc_html_e( 'Drag your answer', 'tutor' ); ?>
						</span>
					</div>
				</div>
			</div>
			<?php
		}
	}
	?>
</div>
