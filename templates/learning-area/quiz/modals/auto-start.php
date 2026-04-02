<?php
/**
 * Tutor quiz auto-start modal content.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

$countdown_seconds = isset( $data['countdown_seconds'] ) ? (int) $data['countdown_seconds'] : 5;
$modal_id          = isset( $data['modal_id'] ) ? (string) $data['modal_id'] : '';
?>

<div class="tutor-modal-body tutor-quiz-autostart-modal">
<div
	class="tutor-quiz-autostart-card"
	data-quiz-autostart-modal="<?php echo esc_attr( $modal_id ); ?>"
	x-data="tutorRadar({ seconds: <?php echo esc_attr( $countdown_seconds ); ?>, eventName: 'tutor-quiz-autostart-complete' })"
>
		<canvas data-quiz-autostart-canvas="bg"></canvas>
		<canvas data-quiz-autostart-canvas="sweep"></canvas>
		<div class="tutor-quiz-autostart-title">
			<?php esc_html_e( 'Starting Quiz in', 'tutor' ); ?>
		</div>
		<div class="tutor-quiz-autostart-digit" data-quiz-autostart-digit>
			<?php echo esc_html( $countdown_seconds ); ?>
		</div>
	</div>
</div>
