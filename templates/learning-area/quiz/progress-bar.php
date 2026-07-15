<?php
/**
 * Tutor quiz progress bar.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Button;
use Tutor\Components\Constants\Variant;
use Tutor\Helpers\TimerHelper;

global $tutor_is_started_quiz;

$remaining_time_secs    = isset( $remaining_time_secs ) ? (int) $remaining_time_secs : 0;
$quiz_when_time_expires = $quiz_when_time_expires ?? 'auto_abandon';
$form_id                = $form_id ?? '';
$modal_id               = $modal_id ?? '';
$has_time_limit         = isset( $has_time_limit ) ? (bool) $has_time_limit : $remaining_time_secs > 0;
$hide_quiz_time_display = isset( $hide_quiz_time_display ) ? (bool) $hide_quiz_time_display : false;
$total_questions        = isset( $total_questions ) ? (int) $total_questions : 0;
$quiz_title             = get_the_title( $tutor_is_started_quiz->quiz_id );

$initial_tokens = TimerHelper::build_tokens( $remaining_time_secs );
$sizer_tokens   = TimerHelper::build_tokens( $remaining_time_secs );

$render_timer_tokens = static function ( array $tokens ) {
	foreach ( $tokens as $token ) {
		$type  = (string) ( $token['type'] ?? '' );
		$value = (string) ( $token['value'] ?? '' );
		$cls   = '';

		if ( 'digit' === $type ) {
			$cls = 'tutor-quiz-timer-digit-wrapper';
		} elseif ( 'separator' === $type ) {
			$cls = 'tutor-quiz-timer-separator';
		} elseif ( 'suffix' === $type ) {
			$cls = 'tutor-quiz-timer-suffix';
		} elseif ( 'spacer' === $type ) {
			$cls = 'tutor-quiz-timer-spacer';
		}

		if ( ! $cls ) {
			continue;
		}
		?>
		<span class="<?php echo esc_attr( $cls ); ?>">
			<?php if ( 'digit' === $type ) : ?>
				<?php echo esc_html( $value ); ?>
			<?php elseif ( 'spacer' !== $type ) : ?>
				<?php echo esc_html( $value ); ?>
			<?php endif; ?>
		</span>
		<?php
	}
};

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
			<div class="tutor-quiz-progress-meta">
				<?php if ( $has_time_limit && ! $hide_quiz_time_display ) : ?>
					<div
						class="tutor-quiz-timer-frame"
						:class="['is-' + timerState, 'is-' + timerFormat]"
						:data-shaking="shaking ? '1' : '0'"
						:data-format="timerFormat"
					>
						<div class="tutor-quiz-timer-frame-shape" aria-hidden="true">
							<div class="tutor-quiz-timer-frame-tabs">
								<span class="tutor-quiz-timer-frame-tab tutor-quiz-timer-frame-tab-start"></span>
								<span class="tutor-quiz-timer-frame-tab tutor-quiz-timer-frame-tab-end"></span>
							</div>
							<div class="tutor-quiz-timer-frame-body">
								<div class="tutor-quiz-timer-frame-body-fill"></div>
								<div class="tutor-quiz-timer-frame-body-stroke"></div>
							</div>
						</div>

						<div class="tutor-quiz-timer-text tutor-quiz-timer-text-sizer" aria-hidden="true">
							<?php $render_timer_tokens( $sizer_tokens ); ?>
						</div>

						<div class="tutor-quiz-timer-text tutor-quiz-timer-text-fallback" :class="'is-' + timerState" x-show="!isReady">
							<?php $render_timer_tokens( $initial_tokens ); ?>
						</div>

						<div class="tutor-quiz-timer-text tutor-quiz-timer-text-live" :class="'is-' + timerState" x-show="isReady">
							<template x-for="token in displayTokens" :key="token.key">
								<span
									:class="{
										'tutor-quiz-timer-digit-wrapper': token.type === 'digit',
										'tutor-quiz-timer-separator': token.type === 'separator',
										'tutor-quiz-timer-suffix': token.type === 'suffix',
										'tutor-quiz-timer-spacer': token.type === 'spacer'
									}"
								>
									<template x-if="token.type === 'digit'">
										<span
											class="tutor-quiz-timer-reel"
											:style="'transform: translateY(-' + (Number(token.value) * 1.25) + 'em)'"
										>
											<?php for ( $i = 0; $i < 10; $i++ ) : ?>
												<span><?php echo esc_html( $i ); ?></span>
											<?php endfor; ?>
										</span>
									</template>

									<template x-if="token.type !== 'digit'">
										<span x-text="token.type === 'spacer' ? '' : token.value"></span>
									</template>
								</span>
							</template>
						</div>
					</div>
				<?php endif; ?>

				<div class="tutor-quiz-progress-title">
					<?php echo esc_html( $quiz_title ? $quiz_title : __( 'Quiz', 'tutor' ) ); ?>
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

		<div class="tutor-quiz-progress-bar-wrapper">
			<div class="tutor-progress-bar tutor-progress-bar-brand">
				<div
					class="tutor-progress-bar-fill"
					:style="`--tutor-progress-width: ${attemptedProgress}%`"
				></div>
			</div>
		</div>
	</div>
</div>
