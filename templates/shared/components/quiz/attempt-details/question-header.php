<?php
/**
 * Attempt details question header.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz\AttemptDetails
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Quiz;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\Models\QuizModel;

$index                = (int) ( $index ?? 1 );
$question_title       = (string) ( $question_title ?? '' );
$question_description = (string) ( $question_description ?? '' );
$question             = isset( $question ) && is_object( $question ) ? $question : null;
$answer_status        = (string) ( $answer_status ?? '' );
$attempt_id           = (int) ( $attempt_id ?? 0 );
$attempt_answer_id    = (int) ( $attempt_answer_id ?? 0 );
$is_instructor_review = ! empty( $is_instructor_review );
$is_skipped           = ! empty( $is_skipped );
$is_overridden        = ! empty( $is_overridden );
$review_field_name    = (string) ( $review_field_name ?? '' );
$is_manual_question   = $question && in_array( (string) ( $question->question_type ?? '' ), QuizModel::get_manual_review_types(), true );
?>

<div class="tutor-quiz-question-header">
	<div class="tutor-quiz-question-number">
		<?php echo esc_html( $index ); ?>
	</div>

	<div class="tutor-quiz-question-title">
		<?php echo esc_html( Quiz::sanitize_quiz_content( $question_title ) ); ?>

		<?php if ( ! empty( $question_description ) ) : ?>
			<?php
			$description = apply_filters( 'tutor_filter_quiz_question_description', wp_unslash( $question_description ) );
			if ( $description ) {
				$markup = "<div class='tutor-p2 tutor-text-secondary'>{$description}</div>";
				if ( function_exists( 'tutor' ) && tutor()->has_pro ) {
					do_action( 'tutor_quiz_question_desc_render', $markup, $question );
				} else {
					echo wp_kses_post( $markup );
				}
			}
			?>
		<?php endif; ?>
	</div>

	<?php if ( $question || ( $is_instructor_review && $attempt_id ) ) : ?>
		<div class="tutor-quiz-question-header-actions">
			<div class="tutor-quiz-question-header-status">
				<?php
				QuizModel::render_attempt_answer_badge(
					$question,
					array(
						'is_instructor_review' => $is_instructor_review,
						'review_field_name'    => $review_field_name,
					)
				);
				?>
			</div>

			<?php
			$show_header_score = ! $is_instructor_review && ! $is_skipped && 'pending' !== $answer_status && isset( $question->question_mark );
			if ( $show_header_score ) :
				$achieved_display   = (float) ( $question->achieved_mark ?? 0 );
				$achieved_formatted = ( floor( $achieved_display ) === $achieved_display ) ? (string) (int) $achieved_display : (string) round( $achieved_display, 2 );

				$total_display   = (float) ( $question->question_mark ?? 0 );
				$total_formatted = ( floor( $total_display ) === $total_display ) ? (string) (int) $total_display : (string) round( $total_display, 2 );
				?>
				<div class="tutor-quiz-question-header-divider" aria-hidden="true"></div>
				<span class="tutor-quiz-question-header-score">
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: achieved marks, 2: total marks. */
							__( 'Score: %1$s/%2$s', 'tutor' ),
							$achieved_formatted,
							$total_formatted
						)
					);
					?>
				</span>
			<?php endif; ?>

			<?php if ( $is_instructor_review && $attempt_id && $review_field_name && ! $is_skipped && ! $is_manual_question ) : ?>
				<div class="tutor-quiz-question-header-divider" aria-hidden="true"></div>

				<div class="tutor-quiz-question-review-actions-wrap">
					<div class="tutor-quiz-question-review-actions">
						<input
							type="hidden"
							name="<?php echo esc_attr( $review_field_name ); ?>"
							value="<?php echo esc_attr( $answer_status ); ?>"
							x-bind="register('<?php echo esc_attr( $review_field_name ); ?>')"
						/>

						<label
							class="tutor-quiz-question-review-action"
							data-review-status="correct"
							title="<?php esc_attr_e( 'Mark as correct', 'tutor' ); ?>"
							@click="setValue('<?php echo esc_attr( $review_field_name ); ?>', 'correct', { shouldDirty: true })"
						>
							<input
								class="tutor-quiz-question-review-input"
								type="radio"
								name="<?php echo esc_attr( $review_field_name ); ?>"
								value="correct"
								:checked="watch('<?php echo esc_attr( $review_field_name ); ?>') === 'correct'"
								tabindex="-1"
								aria-hidden="true"
							/>
							<?php SvgIcon::make()->name( Icon::CHECK_2 )->size( 20 )->render(); ?>
						</label>

						<label
							class="tutor-quiz-question-review-action"
							data-review-status="incorrect"
							title="<?php esc_attr_e( 'Mark as incorrect', 'tutor' ); ?>"
							@click="setValue('<?php echo esc_attr( $review_field_name ); ?>', 'incorrect', { shouldDirty: true })"
						>
							<input
								class="tutor-quiz-question-review-input"
								type="radio"
								name="<?php echo esc_attr( $review_field_name ); ?>"
								value="incorrect"
								:checked="watch('<?php echo esc_attr( $review_field_name ); ?>') === 'incorrect'"
								tabindex="-1"
								aria-hidden="true"
							/>
							<?php SvgIcon::make()->name( Icon::CROSS )->size( 20 )->render(); ?>
						</label>
					</div>

					<div
						class="tutor-quiz-question-review-override-notice tutor-fs-8 tutor-color-muted tutor-mt-4"
						x-show="<?php echo $is_overridden ? 'true' : "watch('" . esc_attr( $review_field_name ) . "') !== '" . esc_attr( $answer_status ) . "'"; ?>"
					>
						<?php esc_html_e( '(Overrides the auto-graded result)', 'tutor' ); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
