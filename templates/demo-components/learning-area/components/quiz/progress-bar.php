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

?>

<div class="tutor-quiz-header">
	<div class="tutor-quiz-progress" x-data="tutorQuizTimer(1500)" >
		<div class="tutor-quiz-progress-header">
			<div class="tutor-flex tutor-items-center tutor-gap-4">
				<?php tutor_utils()->render_svg_icon( Icon::TIME, 32, 32, array( 'class' => 'tutor-icon-brand' ) ); ?>

				<div class="tutor-quiz-progress-time">
					<span x-text="minutes">00</span>
					<span>:</span>
					<span x-text="seconds">00</span>
				</div>
			</div>

			<button class="tutor-btn tutor-btn-outline tutor-px-8">
				<?php esc_html_e( 'Quit', 'tutor' ); ?>
			</button>
		</div>

		<div class="tutor-progress-bar tutor-progress-bar-brand">
			<div 
				class="tutor-progress-bar-fill"
				:style="`--tutor-progress-width: ${progress}%`"
			></div>
		</div>
	</div>
</div>