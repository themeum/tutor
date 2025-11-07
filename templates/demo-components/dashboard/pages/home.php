<?php
/**
 * Tutor dashboard.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

?>
<div class="tutor-p-8">
	<div class="tutor-text-h3 tutor-color-black tutor-mb-6">
		<?php esc_html_e( 'Welcome to TutorLMS Home', 'tutor' ); ?>
	</div>
	
	<div class="tutor-mb-4">
		<?php tutor_load_template( 'core-components.live-session-card' ); ?>
	</div>
</div>
