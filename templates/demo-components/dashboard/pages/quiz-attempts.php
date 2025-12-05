<?php
/**
 * Tutor dashboard quiz attempts.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$current_url = admin_url( 'admin.php?page=playground&subpage=dashboard' );

$page_nav_items = array(
	array(
		'type'    => 'dropdown',
		'icon'    => Icon::ENROLLED,
		'active'  => false,
		'options' => array(
			array(
				'label'  => __( 'Active', 'tutor' ),
				'icon'   => Icon::PLAY_LINE,
				'url'    => esc_url( add_query_arg( 'dashboard-page', 'courses', $current_url ) ),
				'active' => false,
			),
			array(
				'label'  => __( 'Enrolled', 'tutor' ),
				'icon'   => Icon::ENROLLED,
				'url'    => esc_url( add_query_arg( 'dashboard-page', 'courses', $current_url ) ),
				'active' => false,
			),
			array(
				'label'  => __( 'Complete', 'tutor' ),
				'icon'   => Icon::COMPLETED_CIRCLE,
				'url'    => esc_url( add_query_arg( 'dashboard-page', 'courses', $current_url ) ),
				'active' => false,
			),
		),
	),
	array(
		'type'   => 'link',
		'label'  => __( 'Wishlist', 'tutor' ),
		'icon'   => Icon::WISHLIST,
		'url'    => esc_url( add_query_arg( 'dashboard-page', 'wishlist', $current_url ) ),
		'active' => false,
	),
	array(
		'type'   => 'link',
		'label'  => __( 'Quiz Attempts', 'tutor' ),
		'icon'   => Icon::QUIZ_2,
		'url'    => esc_url( add_query_arg( 'dashboard-page', 'quiz-attempts', $current_url ) ),
		'active' => true,
	),
);

?>
<div class="tutor-pt-7">
	<div class="tutor-dashboard-page-card">
		<div class="tutor-p-6 tutor-sm-p-2 tutor-border-b tutor-sm-border tutor-sm-rounded-2xl">
			<?php
			tutor_load_template(
				'core-components.nav',
				array(
					'items' => $page_nav_items,
					'size'  => 'lg',
				)
			);
			?>
		</div>
		<div class="tutor-dashboard-page-card-body">
			<?php tutor_load_template( 'demo-components.dashboard.components.quiz-attempts-list' ); ?>
		</div>
	</div>
</div>
