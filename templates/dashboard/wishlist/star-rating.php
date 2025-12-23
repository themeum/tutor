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
$rating              = isset( $rating ) ? floatval( $rating->rating_avg ) : 0.00;
$wrapper_class       = isset( $wrapper_class ) ? $wrapper_class : 'tutor-ratings-stars tutor-flex tutor-items-center tutor-gap-2';
$show_rating_average = isset( $show_rating_average ) ? (bool) $show_rating_average : false;
?>
<div class="<?php echo esc_attr( $wrapper_class ); ?> tutor-text-exception4" data-rating-value="<?php echo esc_attr( $rating ); ?>">
	<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
		<?php
		if ( (int) $rating >= $i ) {
			tutor_utils()->render_svg_icon( Icon::STAR_FILL, 14 );
		} elseif ( ( $rating - $i ) >= -0.5 ) {
			tutor_utils()->render_svg_icon( Icon::STAR_HALF, 14 );
		} else {
			tutor_utils()->render_svg_icon( Icon::STAR_LINE, 14 );
		}
		?>

	<?php endfor; ?>
	<?php if ( $show_rating_average ) : ?>
		<div class="tutor-ratings-average">
			<?php echo esc_html( apply_filters( 'tutor_course_rating_average', $rating ) ); ?>
		</div>
	<?php endif; ?>
</div>
