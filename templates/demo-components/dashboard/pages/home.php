<?php
/**
 * Tutor dashboard.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

// Define stat card variations with sample data matching the design.
$stat_cards = array(
	array(
		'variation' => 'enrolled',
		'title'     => esc_html__( 'Enrolled Courses', 'tutor' ),
		'icon'      => Icon::COURSES,
		'value'     => '12',
		'change'    => '+2',
	),
	array(
		'variation' => 'active',
		'title'     => esc_html__( 'Active', 'tutor' ),
		'icon'      => Icon::PLAY_LINE,
		'value'     => '3',
		'change'    => '+2',
	),
	array(
		'variation' => 'completed',
		'title'     => esc_html__( 'Completed', 'tutor' ),
		'icon'      => Icon::COMPLETED_CIRCLE,
		'value'     => '5',
		'change'    => '+2',
	),
	array(
		'variation' => 'time-spent',
		'title'     => esc_html__( 'Time Spent', 'tutor' ),
		'icon'      => Icon::TIME,
		'value'     => '375h+',
		'change'    => '+2',
	),
);

?>
<div class="tutor-p-8">
	<div class="tutor-text-h3 tutor-color-black tutor-mb-6">
		<?php esc_html_e( 'Welcome to TutorLMS Home', 'tutor' ); ?>
	</div>
	
	<div class="tutor-mb-4">
		<?php tutor_load_template( 'core-components.event-badge' ); ?>
	<div class="tutor-flex tutor-gap-4">
		<?php foreach ( $stat_cards as $card ) : ?>
			<div class="tutor-flex-1">
			<?php
			tutor_load_template(
				'demo-components.dashboard.components.stat-card',
				array(
					'variation'  => isset( $card['variation'] ) ? $card['variation'] : 'enrolled',
					'card_title' => isset( $card['title'] ) ? $card['title'] : '',
					'icon'       => isset( $card['icon'] ) ? $card['icon'] : '',
					'value'      => isset( $card['value'] ) ? $card['value'] : '',
					'change'     => isset( $card['change'] ) ? $card['change'] : '',
				)
			);
			?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
