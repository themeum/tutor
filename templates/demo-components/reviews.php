<?php
/**
 * Tutor dashboard reviews.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

?>
<div class="tutor-user-reviews">
	<?php
	tutor_load_template(
		'demo-components.dashboard.components.profile-pages-header',
		array( 'page_title' => __( 'Reviews', 'tutor' ) )
	);
	?>
	<div class="tutor-profile-container">
		<div class="tutor-flex tutor-flex-column tutor-gap-5 tutor-mt-9">
			<?php tutor_load_template( 'demo-components.dashboard.components.review-card' ); ?>
			<?php tutor_load_template( 'demo-components.dashboard.components.review-card' ); ?>
			<?php tutor_load_template( 'demo-components.dashboard.components.review-card' ); ?>
			<?php tutor_load_template( 'demo-components.dashboard.components.review-card' ); ?>
		</div>
	</div>
</div>
