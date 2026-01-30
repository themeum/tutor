<?php
/**
 * Stat Card Component
 * Reusable stat card component for dashboard
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Input;
use TUTOR\Instructor;


defined( 'ABSPATH' ) || exit;

// Default values.
$icon_size       = $icon_size ?? 24;
$value           = $value ?? 0;
$content_display = $content ?? '';
$show_graph      = $show_graph ?? false;
$data            = $data ?? array( 0, 0, 0 );
$hover_content   = $hover_content ?? array();
$variation       = $variation ?? $icon;

// Required fields validation.
if ( ! isset( $card_title ) || empty( $card_title ) ) {
	return;
}
if ( ! isset( $icon ) || empty( $icon ) ) {
	return;
}

if ( ! empty( $hover_content ) ) {
	$start_date     = Input::has( 'start_date' ) ? tutor_get_formated_date( 'Y-m-d', Input::get( 'start_date' ) ) : '';
	$end_date       = Input::has( 'end_date' ) ? tutor_get_formated_date( 'Y-m-d', Input::get( 'end_date' ) ) : '';
	$template_path  = tutor()->path . 'templates/dashboard/instructor/analytics/stat-card-hover.php';
	$hover_template = Instructor::get_template_output(
		$template_path,
		array(
			'start_date'    => $start_date,
			'end_date'      => $end_date,
			'hover_content' => $hover_content,
			'hover_amount'  => $value,
		),
		false
	);
}

?>
<div class="tutor-card tutor-stat-card tutor-stat-card-<?php echo esc_attr( $variation ); ?>">
	<div class="tutor-stat-card-header">
		<div class="tutor-stat-card-title">
			<?php echo esc_html( $card_title ); ?>
		</div>
		<div class="tutor-stat-card-icon">
			<?php tutor_utils()->render_svg_icon( $icon, $icon_size, $icon_size ); ?>
		</div>
	</div>
	<div class="tutor-stat-card-content">
		<div class="tutor-stat-card-value">
			<?php echo esc_html( $value ); ?>
		</div>

		<?php if ( ! empty( $content_display ) ) : ?>
		<p class="tutor-stat-card-change">
			<?php echo esc_html( $content_display ); ?>
		</p>
		<?php endif; ?>

		<?php if ( ! empty( $hover_content ) ) : ?>
			<?php echo $hover_template; //phpcs:ignore ?>
		<?php endif; ?>
	</div>
	<?php if ( $show_graph ) : ?>
		<div class="tutor-stat-card-chart" x-data="tutorStatCard(<?php echo wp_json_encode( $data ); ?>)">
			<canvas x-ref="canvas" hright="33" width="100%"></canvas>
		</div>
	<?php endif; ?>
</div>
