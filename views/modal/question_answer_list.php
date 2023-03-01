<?php
/**
 * Quiz question answer list
 *
 * @package Tutor\Views
 * @subpackage Tutor\Modal
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

use TUTOR\Question_Answers_List;

if ( 'open_ended' === $question_type || 'short_answer' === $question_type ) {
	echo '<p class="tutor-px-32 tutor-py-16">' .
			esc_html__( 'No option is necessary for this answer type', 'tutor' ) .
		'</p>';
	return '';
}
?>

<div id="tutor_quiz_question_answers" data-question-id="<?php echo esc_attr( $question_id ); ?>">
	<?php
	$answers = Question_Answers_List::answer_list_by_question( $question_id, $question_type );

	if ( is_array( $answers ) && count( $answers ) ) {
		foreach ( $answers as $answer ) {
			?>
	<div class="tutor-quiz-answer-wrap" data-answer-id="<?php echo esc_attr( $answer->answer_id ); ?>">
		<div class="tutor-quiz-answer">
			<span class="tutor-quiz-answer-title">
			<?php
			echo esc_html( stripslashes( $answer->answer_title ) );
			if ( 'fill_in_the_blank' === $answer->belongs_question_type ) {
				?>
				<?php esc_html_e( 'Answer', 'tutor' ); ?>
				<strong>
					<?php echo esc_html( stripslashes( $answer->answer_two_gap_match ) ); ?>
				</strong>
				<?php
			}

			if ( 'matching' === $answer->belongs_question_type ) {
				echo ' - ' . esc_html( stripslashes( $answer->answer_two_gap_match ) );
			}
			?>
			</span>

			<?php
			// Show image for the single answer.
			if ( $answer->image_id ) {
				echo '<span class="tutor-question-answer-image">
                <img src="' . esc_url( wp_get_attachment_image_url( $answer->image_id ) ) . '" /></span>';
			}

			if ( 'true_false' === $question_type || 'single_choice' === $question_type ) {
				?>
					<span class="tutor-quiz-answers-mark-correct-wrap tutor-mr-4">
						<input type="radio" name="mark_as_correct[<?php echo esc_attr( $answer->belongs_question_id ); ?>]" value="<?php echo esc_attr( $answer->answer_id ); ?>" title="<?php esc_html_e( 'Mark as correct', 'tutor' ); ?>" <?php checked( 1, $answer->is_correct ); ?> >
					</span>
					<?php
			} elseif ( 'multiple_choice' === $question_type ) {
				?>
				<span class="tutor-quiz-answers-mark-correct-wrap tutor-mr-4">
					<input type="checkbox" name="mark_as_correct[<?php echo esc_attr( $answer->belongs_question_id ); ?>]" value="<?php echo esc_attr( $answer->answer_id ); ?>" title="<?php esc_html_e( 'Mark as correct', 'tutor' ); ?>" <?php checked( 1, $answer->is_correct ); ?> >
				</span>														
				<?php
			}
			?>

			<?php if ( 'true_false' !== $question_type ) : ?>
				<span class="tutor-quiz-answer-edit tutor-mr-4">
					<a class="tutor-iconic-btn" href="javascript:;">
						<i class="tutor-icon-pencil" area-hidden="true"></i> 
					</a>
				</span>
			<?php endif; ?>

			<?php if ( 'fill_in_the_blank' !== $question_type ) : ?>
				<span class="tutor-quiz-answer-sort-icon">
					<i class="tutor-d-flex tutor-icon-hamburger-o"></i>
				</span>
			<?php endif; ?>
		</div>

			<?php if ( 'true_false' !== $question_type && 'fill_in_the_blank' !== $question_type ) : ?>
			<div class="tutor-quiz-answer-trash-wrap tutor-d-flex">
				<a href="javascript:;" class="answer-trash-btn answer-trash-btn tutor-d-flex tutor-align-center" data-answer-id="<?php echo esc_attr( $answer->answer_id ); ?>">
					<i class="tutor-icon-trash-can"></i>
				</a>
			</div>
		<?php endif; ?>
	</div>
			<?php
		}
	}
	?>
</div>

<?php if ( 'true_false' != $question_type && ( 'fill_in_the_blank' != $question_type || empty( $answers ) ) ) : ?>
	<a href="javascript:;" class="add_question_answers_option tutor-btn tutor-d-flex tutor-align-center" data-question-id="<?php echo esc_attr( $question_id ); ?>">
		<i class="tutor-icon-plus-o "></i>
		<?php esc_html_e( 'Add An Option', 'tutor' ); ?>
	</a>
<?php endif; ?>
