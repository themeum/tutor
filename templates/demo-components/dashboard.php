<?php
/**
 * Tutor dashboard.
 *
 * @package tutor
 */

?>
<div class="tutor-dashboard-wrapper">
	<?php tutor_load_template( 'demo-components.dashboard.components.sidebar' ); ?>
	<div class="tutor-dashboard-content">
		<?php tutor_load_template( 'demo-components.dashboard.components.header' ); ?>
		<div class="tutor-dashboard-content-inner">
			<div class="tutor-text-h3 tutor-color-black tutor-p-8">
				<?php esc_html_e( 'Welcome to TutorLMS Dashboard', 'tutor' ); ?>
			</div>
		</div>
	</div>
</div>
