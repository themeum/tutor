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

// Define popup progress card variations with sample data.
$progress_cards = array(
	array(
		'variation'   => 'enrolled',
		'card_title'  => esc_html__( 'Fantastic,', 'tutor' ),
		'user_name'   => esc_html__( 'Johny!', 'tutor' ),
		'subtitle'    => esc_html__( "You've enrolled in", 'tutor' ),
		'value'       => '12',
		'button_text' => esc_html__( "I'm Happy", 'tutor' ),
		'breakdown'   => sprintf(
			/* translators: %1$s: course count */
			__( "You've enrolled in <strong>%1\$s courses</strong> across <strong>5 different categories</strong>! Keep exploring!", 'tutor' ),
			'12'
		),
	),
	array(
		'variation'   => 'active',
		'card_title'  => esc_html__( 'Great Progress!', 'tutor' ),
		'user_name'   => '',
		'subtitle'    => esc_html__( "You're actively learning", 'tutor' ),
		'value'       => '3',
		'button_text' => esc_html__( "I'm Happy", 'tutor' ),
		'breakdown'   => sprintf(
			/* translators: %1$s: progress percentage */
			__( "You're making great progress! <strong>%1\$s%% average completion</strong> across all your active courses.", 'tutor' ),
			'68'
		),
	),
	array(
		'variation'   => 'completed',
		'card_title'  => esc_html__( 'Congratulations!', 'tutor' ),
		'user_name'   => '',
		'subtitle'    => esc_html__( "You've completed", 'tutor' ),
		'value'       => '5',
		'button_text' => esc_html__( "I'm Happy", 'tutor' ),
		'breakdown'   => sprintf(
			/* translators: %1$s: certificate count */
			__( "Congratulations! You've earned <strong>%1\$s certificates</strong> and completed <strong>all quizzes</strong> with flying colors!", 'tutor' ),
			'5'
		),
	),
	array(
		'variation'   => 'time-spent',
		'card_title'  => esc_html__( 'Amazing!', 'tutor' ),
		'user_name'   => '',
		'subtitle'    => esc_html__( "You've dedicated over", 'tutor' ),
		'value'       => '375h+',
		'button_text' => esc_html__( "I'm Happy", 'tutor' ),
		'breakdown'   => sprintf(
			/* translators: %1$s: streak days */
			__( "Amazing dedication! You've maintained a <strong>%1\$s-day learning streak</strong> and reached a new milestone!", 'tutor' ),
			'15'
		),
	),
);

?>
<div class="tutor-p-8">
	<div class="tutor-text-h3 tutor-color-black tutor-mb-6">
		<?php esc_html_e( 'Welcome to TutorLMS Home', 'tutor' ); ?>
	</div>
	
	<div class="tutor-mb-4">
		<?php tutor_load_template( 'core-components.event-badge' ); ?>
	</div>
	
	<div class="tutor-flex tutor-gap-4 tutor-mb-8">
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
	
	<div class="tutor-mb-6">
		<div class="tutor-text-h4 tutor-color-black tutor-mb-4">
			<?php esc_html_e( 'Progress Card Demo', 'tutor' ); ?>
		</div>
		
		<div class="tutor-grid tutor-grid-cols-1 tutor-grid-cols-md-2 tutor-gap-6">
			<?php foreach ( $progress_cards as $progress_card ) : ?>
				<div>
					<?php
					tutor_load_template(
						'demo-components.dashboard.components.popup-progress-card',
						array(
							'variation'   => isset( $progress_card['variation'] ) ? $progress_card['variation'] : 'enrolled',
							'card_title'  => isset( $progress_card['card_title'] ) ? $progress_card['card_title'] : esc_html__( 'Fantastic!', 'tutor' ),
							'user_name'   => isset( $progress_card['user_name'] ) ? $progress_card['user_name'] : '',
							'subtitle'    => isset( $progress_card['subtitle'] ) ? $progress_card['subtitle'] : '',
							'value'       => isset( $progress_card['value'] ) ? $progress_card['value'] : '',
							'button_text' => isset( $progress_card['button_text'] ) ? $progress_card['button_text'] : esc_html__( "I'm Happy", 'tutor' ),
							'breakdown'   => isset( $progress_card['breakdown'] ) ? $progress_card['breakdown'] : '',
						)
					);
					?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
