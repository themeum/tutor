<?php
/**
 * Ordering
 *
 * @package Tutor\Templates
 * @subpackage Single\Quiz\Parts
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @version 1.0.0
 */

?>

<div id="quiz-ordering-ans-area" class="quiz-question-ans-choice-area quiz-image-ordering-ans-area tutor-mt-40 tutor-sortable-list question-type-<?php echo esc_attr( $question_type ); ?> <?php echo $answer_required ? 'quiz-answer-required' : ''; ?> ">
	<?php
	if ( is_array( $answers ) && count( $answers ) ) {
		$answer_i = 0;
		foreach ( $answers as $answer ) {
			$answer_i++;
			$answer_title                         = stripslashes( $answer->answer_title );
			$answer->is_correct ? $quiz_answers[] = $answer->answer_id : 0;
			if ( 'image' === $answer->answer_view_format || 'text_image' === $answer->answer_view_format ) {
				?>
	<div class="quiz-image-ordering-ans tutor-d-flex tutor-align-center">
		<div class="tutor-quiz-ans-no  tutor-fs-6 tutor-fw-medium  tutor-color-black">
			<span class="snum">&nbsp;</span>
		</div>
		<div class="quiz-image-ordering-ans-item tutor-d-flex tutor-ml-20">
			<div class="tutor-quiz-image-ordering-icon tutor-d-flex tutor-align-center tutor-justify-center">
				<span class="tutor-icon-hamburger-menu tutor-color-black-fill"></span>
			</div>
			<div class="tutor-quiz-image-ordering-item tutor-d-flex tutor-align-center tutor-p-12">
				<img src="<?php echo esc_url( wp_get_attachment_image_url( $answer->image_id, 'full' ) ); ?>" />
				<span class="tutor-fs-6 tutor-color-black tutor-ml-16">
				<?php echo esc_html( $answer_title ); ?>
				</span>
				<input type="hidden" name="attempt[<?php echo esc_attr( $is_started_quiz->attempt_id ); ?>][quiz_question][<?php echo esc_attr( $question->question_id ); ?>][answers][]" value="<?php echo esc_attr( $answer->answer_id ); ?>" >
			</div>
		</div>
	</div>
	<?php } else { ?>
	<div class="quiz-ordering-ans tutor-d-flex tutor-align-center">
		<div class="tutor-quiz-ans-no  tutor-fs-6 tutor-fw-medium  tutor-color-black">
			<span class="snum">&nbsp;</span>
		</div>
		<div class="quiz-ordering-ans-item tutor-ml-32">
			<div class="tutor-quiz-border-box">
				<span class="tutor-fs-6 tutor-color-black">
					<?php echo esc_html( $answer_title ); ?>
				</span>
				<span class="tutor-icon-hamburger-menu tutor-color-black-fill"></span>
				<input type="hidden" name="attempt[<?php echo esc_attr( $is_started_quiz->attempt_id ); ?>][quiz_question][<?php echo esc_attr( $question->question_id ); ?>][answers][]" value="<?php echo esc_attr( $answer->answer_id ); ?>" >
			</div>
		</div>
	</div>

				<?php
	}
		}
	}
	?>
</div>
