<?php
/**
 * Stat Card Hover Content
 *
 * @package Tutor\Templates
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\SvgIcon;

$start_date          = $data['start_date'] ?? '';
$end_date            = $data['end_date'] ?? '';
$hover_content       = $data['hover_content'];
$hover_amount        = $data['hover_amount'];
$time_zone           = wp_timezone();
$previous_start_date = $hover_content['previous_start_date'] ?? '';
$previous_end_date   = $hover_content['previous_end_date'] ?? '';

?>

<div class="tutor-stat-card-hover tutor-m-1">
	<div class="tutor-stat-card-hover-wrap">
		<span class="stat-hover-trigger <?php echo esc_attr( $hover_content['class'] ?? '' ); ?>">
			<?php echo esc_html( $hover_content['percentage'] ); ?>
			<?php SvgIcon::make()->name( $hover_content['icon'] )->size( 16 )->attr( 'class', $hover_content['icon_class'] ?? '' )->render(); ?>
		</span>

		<div class="tutor-stat-card-hover-card">
			<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-tiny tutor-text-secondary">
				<span>
					<?php echo esc_html( tutor_i18n_get_formated_date( $previous_start_date, 'M j' ) ); ?> -
					<?php echo esc_html( tutor_i18n_get_formated_date( $previous_end_date, 'M j Y' ) ); ?>
				</span>
				<span class="tutor-text-subdued"><?php echo esc_html_e( 'vs', 'tutor' ); ?></span>
				<span>
					<?php echo esc_html( tutor_i18n_get_formated_date( $start_date, 'M j' ) ); ?> -
					<?php echo esc_html( tutor_i18n_get_formated_date( $end_date, 'M j Y' ) ); ?>
				</span>
			</div>
			<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-justify-between tutor-mt-5">
				<span class="tutor-font-semibold tutor-text-primary tutor-tiny">
					<?php echo wp_kses( $hover_amount, tutor_price_allowed_html() ); ?>
				</span>
				<span class="stat-hover-trigger <?php echo esc_attr( $hover_content['class'] ?? '' ); ?>">
					<?php echo esc_html( $hover_content['percentage'] ); ?>
					<?php SvgIcon::make()->name( $hover_content['icon'] )->size( 16 )->attr( 'class', $hover_content['icon_class'] ?? '' )->render(); ?>
				</span>
			</div>
		</div>
	</div>
</div>

