<?php

use TUTOR\Icon;
/**
 * Star Rating Component
 * Reusable star rating component for displaying ratings
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Default values - all data must be passed from parent.
$rating              = isset( $rating ) ? floatval( $rating ) : 0.00;
$wrapper_class       = isset( $wrapper_class ) ? $wrapper_class : 'tutor-ratings-stars tutor-flex tutor-items-center tutor-gap-2';
$icon_class          = ! empty( $icon_class ) ? $icon_class : 'tutor-icon-exception4';
$show_rating_average = isset( $show_rating_average ) ? (bool) $show_rating_average : false;
?>
<div class="<?php echo esc_attr( $wrapper_class ); ?>" data-rating-value="<?php echo esc_attr( $rating ); ?>">
	<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
		<?php
		$is_full = (int) $rating >= $i;
		$is_half = ! $is_full && ( $rating >= ( $i - 0.5 ) );

		$icon_name = $is_full
			? Icon::STAR_FILL
			: ( $is_half ? Icon::STAR_LINE : Icon::STAR_LINE ); // Todo: Half star icon.

		$icon_html = tutor_utils()->render_svg_icon( $icon_name, 16, 16, array(), true ); // phpcs:ignore
		?>
		<div class="<?php echo esc_attr( $icon_class ); ?>" data-rating-value="<?php echo esc_attr( $i ); ?>">
			<?php echo $icon_html; // phpcs:ignore ?>
		</div>
	<?php endfor; ?>
	<?php if ( $show_rating_average ) : ?>
		<div class="tutor-ratings-average">
			<?php echo esc_html( apply_filters( 'tutor_course_rating_average', $rating ) ); ?>
		</div>
	<?php endif; ?>
</div>

