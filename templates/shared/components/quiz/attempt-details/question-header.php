<?php
/**
 * Attempt details question header.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz\AttemptDetails
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Badge;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;

$index                = (int) ( $index ?? 1 );
$question_title       = (string) ( $question_title ?? '' );
$question_description = (string) ( $question_description ?? '' );
$status_badges        = isset( $status_badges ) && is_array( $status_badges ) ? $status_badges : array();
$question             = isset( $question ) && is_object( $question ) ? $question : null;
$answer_status        = (string) ( $answer_status ?? '' );
$attempt_id           = (int) ( $attempt_id ?? 0 );
$attempt_answer_id    = (int) ( $attempt_answer_id ?? 0 );
$is_instructor_review = ! empty( $is_instructor_review );
$review_field_name    = (string) ( $review_field_name ?? '' );
?>

<div class="tutor-quiz-question-header">
	<div class="tutor-quiz-question-number">
		<?php echo esc_html( $index ); ?>
	</div>

	<div class="tutor-quiz-question-title">
		<?php echo esc_html( wp_unslash( $question_title ) ); ?>

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

	<?php if ( ! empty( $status_badges ) || ( $is_instructor_review && $attempt_id ) ) : ?>
		<div class="tutor-quiz-question-header-actions">
			<?php if ( ! empty( $status_badges ) ) : ?>
				<div class="tutor-quiz-question-header-status">
					<?php foreach ( $status_badges as $badge ) : ?>
						<?php
						$badge_label   = (string) ( $badge['label'] ?? '' );
						$badge_variant = (string) ( $badge['variant'] ?? '' );

						if ( '' === $badge_label || '' === $badge_variant ) {
							continue;
						}

						Badge::make()
							->label( $badge_label )
							->variant( $badge_variant )
							->rounded()
							->render();
						?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $is_instructor_review && $attempt_id && $review_field_name ) : ?>
				<div class="tutor-quiz-question-header-divider" aria-hidden="true"></div>

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
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
