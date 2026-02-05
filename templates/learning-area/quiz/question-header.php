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

?>

<div class="tutor-quiz-question-header">
	<?php if ( $show_question_number ) : ?>
		<div class="tutor-quiz-question-number">
			<?php echo esc_html( $index ); ?>
		</div>
	<?php endif; ?>

	<div class="tutor-quiz-question-title">
		<?php echo esc_html( $question_title ); ?>

		<?php if ( ! empty( $question_description ) ) : ?>
			<div class="tutor-p2 tutor-text-secondary">
				<?php echo wp_kses_post( $question_description ); ?>
			</div>
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
