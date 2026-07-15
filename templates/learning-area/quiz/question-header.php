<?php
/**
 * True False
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

global $tutor_is_started_quiz;

$quiz_id                       = $quiz_id ?? ( $tutor_is_started_quiz->quiz_id ?? 0 );
$quiz_options                  = $quiz_id ? tutor_utils()->get_quiz_option( (int) $quiz_id ) : array();
$hide_question_number_overview = (string) ( $quiz_options['hide_question_number_overview'] ?? '0' );
$show_question_number          = '1' !== $hide_question_number_overview;

$index                = $question->index;
$question_title       = $question->question_title ?? '';
$question_description = $question->question_description ?? '';
$question_mark        = $question->question_mark ?? '';

?>

<div class="tutor-quiz-question-header">
	<?php if ( $show_question_number ) : ?>
		<div class="tutor-quiz-question-number">
			<?php echo esc_html( $index ); ?>
		</div>
	<?php endif; ?>

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

	<?php if ( $show_question_mark ) : ?>
		<span class="tutor-badge tutor-badge-rounded tutor-text-secondary">
			<span class="tutor-text-subdued">
				<?php esc_html_e( 'Points: ', 'tutor' ); ?>
			</span>
			<?php echo esc_html( $question_mark ); ?>
		</span>
	<?php endif; ?>
</div>
