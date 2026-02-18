<?php
/**
 * Tutor quiz progress bar.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use Tutor\Components\Button;
use Tutor\Components\Constants\Variant;

$remaining_time_secs    = isset( $remaining_time_secs ) ? (int) $remaining_time_secs : 0;
$quiz_when_time_expires = $quiz_when_time_expires ?? 'auto_abandon';
$form_id                = $form_id ?? '';
$modal_id               = $modal_id ?? '';
$has_time_limit         = isset( $has_time_limit ) ? (bool) $has_time_limit : $remaining_time_secs > 0;
$total_questions        = isset( $total_questions ) ? (int) $total_questions : 0;

?>

<div class="tutor-quiz-header">
	<div
		class="tutor-quiz-progress"
		x-data="tutorQuizTimer({
			duration: <?php echo esc_attr( $remaining_time_secs ); ?>,
			hasLimit: <?php echo $has_time_limit ? 'true' : 'false'; ?>,
			expiresAction: '<?php echo esc_attr( $quiz_when_time_expires ); ?>',
			formId: '<?php echo esc_attr( $form_id ); ?>',
			totalQuestions: <?php echo esc_attr( $total_questions ); ?>,
		})"
		x-init="init()"
	>
		<div class="tutor-quiz-progress-header">
			<?php if ( $has_time_limit ) : ?>
				<div class="tutor-quiz-timer-frame" :class="'is-' + timerState" :data-shaking="shaking ? '1' : '0'">
					<?php tutor_utils()->render_svg_icon( Icon::CLOCK_FRAME, 66, 33 ); ?>

					<div class="tutor-quiz-timer-text" :class="'is-' + timerState">
						<?php
						$digit_positions = array(
							array(
								'field' => 'minutes',
								'index' => 0,
							),
							array(
								'field' => 'minutes',
								'index' => 1,
							),
							'separator',
							array(
								'field' => 'seconds',
								'index' => 0,
							),
							array(
								'field' => 'seconds',
								'index' => 1,
							),
						);

						foreach ( $digit_positions as $pos ) :
							if ( 'separator' === $pos ) :
								?>
								<span class="tutor-quiz-timer-separator">:</span>
								<?php
							else :
								?>
								<span class="tutor-quiz-timer-digit-wrapper">
									<span
										class="tutor-quiz-timer-reel"
										:style="'transform: translateY(-' + (<?php echo esc_attr( $pos['field'] ); ?>.charAt(<?php echo esc_attr( $pos['index'] ); ?>) * 1.25) + 'em)'"
									>
										<?php for ( $i = 0; $i < 10; $i++ ) : ?>
											<span><?php echo esc_html( $i ); ?></span>
										<?php endfor; ?>
									</span>
								</span>
								<?php
							endif;
						endforeach;
						?>
					</div>
				</div>
			<?php endif; ?>

			<div class="tutor-quiz-progress-bar-wrapper">
				<div class="tutor-progress-bar tutor-progress-bar-brand">
					<div 
						class="tutor-progress-bar-fill"
						:style="`--tutor-progress-width: ${attemptedProgress}%`"
					></div>
				</div>
			</div>

			<?php
				Button::make()
					->label( __( 'Quit', 'tutor' ) )
					->variant( Variant::OUTLINE )
					->attr( 'type', 'button' )
					->attr( 'class', 'tutor-px-8' )
					->attr( '@click', "TutorCore.modal.showModal('$modal_id')" )
					->render();
			?>
		</div>
	</div>
</div>
