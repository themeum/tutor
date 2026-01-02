<?php
/**
 * Tutor learning area webinar.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$webinar_overview = array(
	'month_label'        => __( 'August 2025', 'tutor' ),
	'month_text'         => __( 'August', 'tutor' ),
	'year_text'          => __( '2025', 'tutor' ),
	'search_placeholder' => __( 'Search', 'tutor' ),
);

$webinar_month_text = isset( $webinar_overview['month_text'] ) ? $webinar_overview['month_text'] : $webinar_overview['month_label'];
$webinar_year_text  = isset( $webinar_overview['year_text'] ) ? $webinar_overview['year_text'] : '';

$webinar_lessons = array(
	array(
		'group_heading'     => __( 'Today', 'tutor' ),
		'date_text'         => __( 'Today', 'tutor' ),
		'time_text'         => __( '2:00 PM', 'tutor' ),
		'lesson_title'      => __( 'This is the lesson title', 'tutor' ),
		'course_name'       => __( 'Camera Skills & Photo Theory', 'tutor' ),
		'show_live_tag'     => true,
		'event_tag_text'    => __( 'Expired', 'tutor' ),
		'event_tag_icon'    => '',
		'event_tag_variant' => 'expired',
		'action_text'       => __( 'Details', 'tutor' ),
		'action_url'        => '#',
	),
	array(
		'group_heading'  => __( '1st Jan, 2025', 'tutor' ),
		'date_text'      => __( '1st Jan, 2025', 'tutor' ),
		'time_text'      => __( '2:00 PM', 'tutor' ),
		'lesson_title'   => __( 'This is the lesson title', 'tutor' ),
		'course_name'    => __( 'Camera Skills & Photo Theory', 'tutor' ),
		'show_live_tag'  => true,
		'event_tag_text' => __( 'Live Session', 'tutor' ),
		'event_tag_icon' => Icon::ZOOM_COLORIZE,
		'action_text'    => __( 'Join', 'tutor' ),
		'action_url'     => '#',
	),
	array(
		'group_heading'  => '',
		'date_text'      => __( '1st Jan, 2025', 'tutor' ),
		'time_text'      => __( '2:00 PM', 'tutor' ),
		'lesson_title'   => __( 'Portfolio Critique Workshop', 'tutor' ),
		'course_name'    => __( 'Camera Skills & Photo Theory', 'tutor' ),
		'show_live_tag'  => true,
		'event_tag_text' => __( 'Live Session', 'tutor' ),
		'event_tag_icon' => Icon::ZOOM_COLORIZE,
		'action_text'    => __( 'Join', 'tutor' ),
		'action_url'     => '#',
	),
	array(
		'group_heading'  => __( '5th Jan, 2025', 'tutor' ),
		'date_text'      => __( '5th Jan, 2025', 'tutor' ),
		'time_text'      => __( '2:00 PM', 'tutor' ),
		'lesson_title'   => __( 'Studio Lighting Deep Dive', 'tutor' ),
		'course_name'    => __( 'Camera Skills & Photo Theory', 'tutor' ),
		'show_live_tag'  => true,
		'event_tag_text' => __( 'Live Session', 'tutor' ),
		'event_tag_icon' => Icon::ZOOM_COLORIZE,
		'action_text'    => __( 'Join', 'tutor' ),
		'action_url'     => '#',
	),
	array(
		'group_heading'  => '',
		'date_text'      => __( '5th Jan, 2025', 'tutor' ),
		'time_text'      => __( '2:00 PM', 'tutor' ),
		'lesson_title'   => __( 'Live Editing Session', 'tutor' ),
		'course_name'    => __( 'Camera Skills & Photo Theory', 'tutor' ),
		'show_live_tag'  => true,
		'event_tag_text' => __( 'Live Session', 'tutor' ),
		'event_tag_icon' => Icon::ZOOM_COLORIZE,
		'action_text'    => __( 'Join', 'tutor' ),
		'action_url'     => '#',
	),
	array(
		'group_heading'  => '',
		'date_text'      => __( '5th Jan, 2025', 'tutor' ),
		'time_text'      => __( '2:00 PM', 'tutor' ),
		'lesson_title'   => __( 'Live Editing Session', 'tutor' ),
		'course_name'    => __( 'Camera Skills & Photo Theory', 'tutor' ),
		'show_live_tag'  => true,
		'event_tag_text' => __( 'Live Session', 'tutor' ),
		'event_tag_icon' => Icon::ZOOM_COLORIZE,
		'action_text'    => __( 'Join', 'tutor' ),
		'action_url'     => '#',
	),
);

?>
<section class="tutor-webinar">
	<div class="tutor-card tutor-webinar-card">
		<div class="tutor-card-header tutor-space-y-4">
			<div class="tutor-webinar-header">
					<h4 class="tutor-mb-1">
						<span class="tutor-webinar-month"><?php echo esc_html( $webinar_month_text ); ?></span>
						<?php if ( ! empty( $webinar_year_text ) ) : ?>
							<span class="tutor-webinar-year"><?php echo esc_html( $webinar_year_text ); ?></span>
						<?php endif; ?>
				</h4>
				<div class="tutor-flex tutor-items-center tutor-gap-2">
					<button
						type="button"
						class="tutor-btn tutor-btn-secondary tutor-btn-x-small"
						aria-label="<?php esc_attr_e( 'Show previous month', 'tutor' ); ?>"
					>
						<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_LEFT, 16, 16 ); ?>
					</button>
					<button type="button" class="tutor-btn tutor-btn-secondary tutor-btn-x-small">
						<?php esc_html_e( 'Today', 'tutor' ); ?>
					</button>
					<button
						type="button"
						class="tutor-btn tutor-btn-secondary tutor-btn-x-small"
						aria-label="<?php esc_attr_e( 'Show next month', 'tutor' ); ?>"
					>
						<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_RIGHT, 16, 16 ); ?>
					</button>
				</div>
			</div>

			<div class="tutor-webinar-search">
				<div class="tutor-input-field tutor-m-0">
					<div class="tutor-input-wrapper">
						<input
							type="search"
							id="tutor-webinar-search-input"
							class="tutor-input tutor-input-content-left tutor-input-content-clear"
							placeholder="<?php echo esc_attr( $webinar_overview['search_placeholder'] ); ?>"
						>
						<div class="tutor-input-content tutor-input-content-left">
							<?php tutor_utils()->render_svg_icon( Icon::SEARCH_2, 20, 20 ); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="tutor-webinar-divider" aria-hidden="true"></div>
		</div>

		<div class="tutor-webinar-body tutor-space-y-8">
			<?php
			$current_group = '';
			$group_open    = false;

			foreach ( $webinar_lessons as $lesson ) :
				$lesson_group = isset( $lesson['group_heading'] ) ? $lesson['group_heading'] : '';

				if ( $lesson_group && $lesson_group !== $current_group ) :
					if ( $group_open ) :
						?>
						</div>
						<?php
					endif;

					$current_group = $lesson_group;
					$group_open    = true;
					?>
					<div class="tutor-webinar-group tutor-space-y-3">
						<div class="tutor-webinar-group-heading">
							<?php echo esc_html( $lesson_group ); ?>
						</div>
					<?php
				elseif ( ! $group_open ) :
					$group_open = true;
					?>
					<div class="tutor-webinar-group tutor-space-y-3">
					<?php
				endif;

				tutor_load_template(
					'core-components.upcoming-lesson-card',
					array(
						'date_text'         => isset( $lesson['date_text'] ) ? $lesson['date_text'] : '',
						'time_text'         => isset( $lesson['time_text'] ) ? $lesson['time_text'] : '',
						'lesson_title'      => isset( $lesson['lesson_title'] ) ? $lesson['lesson_title'] : '',
						'course_name'       => isset( $lesson['course_name'] ) ? $lesson['course_name'] : '',
						'show_live_tag'     => isset( $lesson['show_live_tag'] ) ? $lesson['show_live_tag'] : true,
						'event_tag_text'    => isset( $lesson['event_tag_text'] ) ? $lesson['event_tag_text'] : '',
						'event_tag_icon'    => isset( $lesson['event_tag_icon'] ) ? $lesson['event_tag_icon'] : Icon::ZOOM_COLORIZE,
						'event_tag_variant' => isset( $lesson['event_tag_variant'] ) ? $lesson['event_tag_variant'] : '',
						'action_text'       => isset( $lesson['action_text'] ) ? $lesson['action_text'] : '',
						'action_url'        => isset( $lesson['action_url'] ) ? $lesson['action_url'] : '',
					)
				);
			endforeach;

			if ( $group_open ) :
				?>
				</div>
				<?php
			endif;
			?>
		</div>
	</div>
</section>
