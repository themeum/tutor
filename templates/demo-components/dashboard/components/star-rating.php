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
use TUTOR\Icon;

// Default values - all data must be passed from parent.
$rating              = isset( $rating ) ? floatval( $rating ) : 0.00;
$wrapper_class       = isset( $wrapper_class ) ? $wrapper_class : 'tutor-ratings-stars tutor-flex tutor-items-center tutor-gap-2';
$show_rating_average = isset( $show_rating_average ) ? (bool) $show_rating_average : false;
$icon_size           = 16;


$star_fill = tutor_utils()->get_svg_icon( Icon::STAR_FILL, $icon_size, $icon_size );
$star_half = tutor_utils()->get_svg_icon( Icon::STAR_HALF, $icon_size, $icon_size );
$star      = tutor_utils()->get_svg_icon( Icon::STAR_LINE, $icon_size, $icon_size );
?>
<div class="<?php echo esc_attr( $wrapper_class ); ?>" data-rating-value="<?php echo esc_attr( $rating ); ?>">
	<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
		<span class="tutor-icon-exception4 tutor-flex-center">
			<?php
			if ( (int) $rating >= $i ) {
				echo $star_fill; // phpcs:ignore -- already escaped inside template file
			} elseif ( ( $rating - $i ) >= -0.5 ) {
				echo $star_half; // phpcs:ignore -- already escaped inside template file
			} else {
				echo $star; // phpcs:ignore -- already escaped inside template file
			}
			?>
		</span>
	<?php endfor; ?>
	<?php if ( $show_rating_average ) : ?>
		<div class="tutor-ratings-average">
			<?php echo esc_html( apply_filters( 'tutor_course_rating_average', $rating ) ); ?>
		</div>
	<?php endif; ?>
</div>

