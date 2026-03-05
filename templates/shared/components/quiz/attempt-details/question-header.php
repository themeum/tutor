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

$index                = (int) ( $index ?? 1 );
$question_title       = (string) ( $question_title ?? '' );
$question_description = (string) ( $question_description ?? '' );
$status_label         = (string) ( $status_label ?? '' );
$status_variant       = (string) ( $status_variant ?? '' );
?>

<div class="tutor-quiz-question-header">
	<div class="tutor-quiz-question-number">
		<?php echo esc_html( $index ); ?>
	</div>

	<div class="tutor-quiz-question-title">
		<?php echo esc_html( $question_title ); ?>

		<?php if ( ! empty( $question_description ) ) : ?>
			<?php
			$description = apply_filters( 'tutor_filter_quiz_question_description', wp_unslash( $question_description ) );
			if ( $description ) {
				$markup = "<div class='tutor-p2 tutor-text-secondary'>{$description}</div>";
				if ( function_exists( 'tutor' ) && tutor()->has_pro ) {
					do_action( 'tutor_quiz_question_desc_render', $markup );
				} else {
					echo wp_kses_post( $markup );
				}
			}
			?>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $status_label ) && ! empty( $status_variant ) ) : ?>
		<?php
		Badge::make()
			->label( $status_label )
			->variant( $status_variant )
			->rounded()
			->render();
		?>
	<?php endif; ?>
</div>
