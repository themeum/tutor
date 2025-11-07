<?php
/**
 * Template for displaying dashboard demo components
 *
 * @package Tutor
 * @since 1.0.0
 */

use TUTOR\Icon;

?>
<h2>Dashboard</h2>

<section>
	<h3>Stat Cards</h3>
	<div class="tutor-row tutor-gx-4">
		<div class="tutor-col-md-6 tutor-col-lg-3 tutor-mb-16">
			<?php
			tutor_load_template_from_custom_path(
				tutor()->path . 'templates/demo-components/dashboard/components/stat-card.php',
				array(
					'card_title' => __( 'Enrolled Courses', 'tutor' ),
					'value'      => '12',
					'change'     => '+2 this month',
					'icon'       => Icon::BOOK,
					'variant'    => 'enrolled',
				)
			);
			?>
		</div>

		<div class="tutor-col-md-6 tutor-col-lg-3 tutor-mb-16">
			<?php
			tutor_load_template_from_custom_path(
				tutor()->path . 'templates/demo-components/dashboard/components/stat-card.php',
				array(
					'card_title' => __( 'Active', 'tutor' ),
					'value'      => '3',
					'change'     => '+2 this month',
					'icon'       => Icon::ACTIVE,
					'variant'    => 'active',
				)
			);
			?>
		</div>

		<div class="tutor-col-md-6 tutor-col-lg-3 tutor-mb-16">
			<?php
			tutor_load_template_from_custom_path(
				tutor()->path . 'templates/demo-components/dashboard/components/stat-card.php',
				array(
					'card_title' => __( 'Completed', 'tutor' ),
					'value'      => '5',
					'change'     => '+2 this month',
					'icon'       => Icon::COMPLETED,
					'variant'    => 'completed',
				)
			);
			?>
		</div>

		<div class="tutor-col-md-6 tutor-col-lg-3 tutor-mb-16">
			<?php
			tutor_load_template_from_custom_path(
				tutor()->path . 'templates/demo-components/dashboard/components/stat-card.php',
				array(
					'card_title' => __( 'Time Spent', 'tutor' ),
					'value'      => '375h+',
					'change'     => '+2 this month',
					'icon'       => Icon::CLOCK,
					'variant'    => 'time-spent',
				)
			);
			?>
		</div>
	</div>
</section>