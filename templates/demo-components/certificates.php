<?php
/**
 * Tutor dashboard certificates.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

?>
<div class="tutor-user-certificates">
	<?php
	tutor_load_template(
		'demo-components.dashboard.components.profile-pages-header',
		array( 'page_title' => __( 'Certificates', 'tutor' ) )
	);
	?>
	<div class="tutor-profile-container">
		<div class="tutor-flex tutor-flex-column tutor-gap-5 tutor-mt-9">
			<?php tutor_load_template( 'demo-components.dashboard.components.certificate-card' ); ?>
			<?php tutor_load_template( 'demo-components.dashboard.components.certificate-card' ); ?>
			<?php tutor_load_template( 'demo-components.dashboard.components.certificate-card' ); ?>
			<?php tutor_load_template( 'demo-components.dashboard.components.certificate-card' ); ?>
		</div>
	</div>
</div>
