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
	<div class="tutor-quiz-progress">
		<div class="tutor-quiz-progress-header">
			<div class="tutor-flex tutor-items-center tutor-gap-4">
				<?php tutor_utils()->render_svg_icon( Icon::TIME, 32, 32, array( 'class' => 'tutor-icon-brand' ) ); ?>
				<!-- Time -->
				<div class="tutor-quiz-progress-time">
					<!-- Minitues -->
					<span>25</span>
					<!-- Separator -->
					<span>:</span>
					<!-- Seconds -->
					<span>00</span>
				</div>
			</div>

			<!-- Action -->
			<button class="tutor-btn tutor-btn-outline tutor-px-8">
				<?php esc_html_e( 'Quit', 'tutor' ); ?>
			</button>
		</div>

		<!-- Progress bar -->
		<div class="tutor-progress-bar tutor-progress-bar-brand" data-tutor-animated>
			<div class="tutor-progress-bar-fill" style="--tutor-progress-width: 75%;"></div>
		</div>
	</div>
</div>