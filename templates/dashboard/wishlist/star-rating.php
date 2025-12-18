<?php
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
$icon_class          = isset( $icon_class ) ? $icon_class : 'tutor-icon-exception4';
$show_rating_average = isset( $show_rating_average ) ? (bool) $show_rating_average : false;
?>
<div class="<?php echo esc_attr( $wrapper_class ); ?>" data-rating-value="<?php echo esc_attr( $rating ); ?>">
	<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
		<?php
		$icon_class_name = '';
		if ( (int) $rating >= $i ) {
			$icon_class_name = 'tutor-icon-star-bold tutor-icon-exception4';
		} elseif ( ( $rating - $i ) >= -0.5 ) {
			$icon_class_name = 'tutor-icon-star-half-bold tutor-icon-exception4';
		} else {
			$icon_class_name = 'tutor-icon-star-line tutor-icon-exception4';
		}
		$final_class = ! empty( $icon_class ) ? $icon_class_name . ' ' . $icon_class : $icon_class_name;
		?>
		<i class="<?php echo esc_attr( $final_class ); ?>" data-rating-value="<?php echo esc_attr( $i ); ?>"></i>
	<?php endfor; ?>
	<?php if ( $show_rating_average ) : ?>
		<div class="tutor-ratings-average">
			<?php echo esc_html( apply_filters( 'tutor_course_rating_average', $rating ) ); ?>
		</div>
	<?php endif; ?>
</div>