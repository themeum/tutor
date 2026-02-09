<?php
/**
 * Tutor quiz progress bar.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\Button;
use Tutor\Components\Constants\Variant;
use TUTOR\Icon;

$remaining_time_secs    = isset( $remaining_time_secs ) ? (int) $remaining_time_secs : 0;
$quiz_when_time_expires = $quiz_when_time_expires ?? 'auto_abandon';
$form_id                = $form_id ?? '';
$modal_id               = $modal_id ?? '';
$has_time_limit         = isset( $has_time_limit ) ? (bool) $has_time_limit : $remaining_time_secs > 0;

?>

<div class="tutor-quiz-header">
	<div
		class="tutor-quiz-progress"
		x-data="tutorQuizTimer({
			duration: <?php echo esc_attr( $remaining_time_secs ); ?>,
			hasLimit: <?php echo $has_time_limit ? 'true' : 'false'; ?>,
			expiresAction: '<?php echo esc_attr( $quiz_when_time_expires ); ?>',
			formId: '<?php echo esc_attr( $form_id ); ?>',
		})"
		x-init="init()"
	>
		<div class="tutor-quiz-progress-header">
			<div class="tutor-flex tutor-items-center tutor-gap-4">
				<?php tutor_utils()->render_svg_icon( Icon::TIME, 32, 32, array( 'class' => 'tutor-icon-brand' ) ); ?>

				<?php if ( $has_time_limit ) : ?>
					<div class="tutor-quiz-progress-time">
						<span x-text="minutes">00</span>
						<span>:</span>
						<span x-text="seconds">00</span>
					</div>
				<?php else : ?>
					<div class="tutor-quiz-progress-time">
						<?php esc_html_e( 'No Limit', 'tutor' ); ?>
					</div>
				<?php endif; ?>
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

		<div class="tutor-progress-bar tutor-progress-bar-brand">
			<div 
				class="tutor-progress-bar-fill"
				:style="`--tutor-progress-width: ${progress}%`"
			></div>
		</div>
	</div>
</div>
