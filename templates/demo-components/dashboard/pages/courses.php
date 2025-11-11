<?php
/**
 * Tutor dashboard courses.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$page_nav_items = array(
	array(
		'type'    => 'dropdown',
		'icon'    => Icon::ENROLLED,
		'active'  => true,
		'options' => array(
			array(
				'label'  => __( 'Active', 'tutor' ),
				'icon'   => Icon::PLAY_LINE,
				'url'    => '#',
				'active' => false,
			),
			array(
				'label'  => __( 'Enrolled', 'tutor' ),
				'icon'   => Icon::ENROLLED,
				'url'    => '#',
				'active' => true,
			),
			array(
				'label'  => __( 'Complete', 'tutor' ),
				'icon'   => Icon::COMPLETED_CIRCLE,
				'url'    => '#',
				'active' => false,
			),
		),
	),
	array(
		'type'   => 'link',
		'label'  => __( 'Wishlist', 'tutor' ),
		'icon'   => Icon::WISHLIST,
		'url'    => '#',
		'active' => false,
	),
	array(
		'type'   => 'link',
		'label'  => __( 'Quiz Attempts', 'tutor' ),
		'icon'   => Icon::QUIZ_2,
		'url'    => '#',
		'active' => false,
	),
);

?>
<div class="tutor-dashboard-page-card">
	<?php
	tutor_load_template(
		'demo-components.dashboard.components.page-nav',
		array( 'items' => $page_nav_items )
	);
	?>
	<div class="tutor-dashboard-page-card-body">
		<p class="tutor-text-center tutor-py-16 tutor-text-muted">
			<?php esc_html_e( 'You have not enrolled in any courses yet.', 'tutor' ); ?>
		</p>
	</div>
</div>
