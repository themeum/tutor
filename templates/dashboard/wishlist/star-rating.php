<?php
/**
 * Star Rating Component
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
$rating_count        = isset( $course_rating->rating_count ) ? floatval( $course_rating->rating_count ) : 0.00;
$wrapper_class       = isset( $wrapper_class ) ? $wrapper_class : 'tutor-ratings-stars tutor-flex tutor-items-center tutor-gap-2';
$rating_average      = isset( $course_rating->rating_avg ) ? (bool) $course_rating->rating_avg : false;
$show_course_ratings = apply_filters( 'tutor_show_course_ratings', true, get_the_ID() );
?>
<div class="<?php echo esc_attr( $wrapper_class ); ?> tutor-text-exception4" data-rating-value="<?php echo esc_attr( $rating_count ); ?>">
	<?php
	if ( $show_course_ratings ) :
		for ( $i = 1; $i <= 5; $i++ ) :
			if ( (int) $rating_count >= $i ) {
				tutor_utils()->render_svg_icon( Icon::STAR_FILL, 12 );
			} elseif ( ( $rating_count - $i ) >= -0.5 ) {
				tutor_utils()->render_svg_icon( Icon::STAR_HALF, 12 );
			} else {
				tutor_utils()->render_svg_icon( Icon::STAR_LINE, 12 );
			}
		endfor;
		if ( $rating_average > 0 ) :
			?>
			<div class="tutor-ratings-average">
				<?php echo esc_html( apply_filters( 'tutor_course_rating_average', $rating_count ) ); ?>
			</div>
			<div class="tutor-ratings-count">
				(<?php echo esc_html( $course_rating->rating_count > 0 ? $course_rating->rating_count : 0 ); ?>)
			</div>
			<?php
		endif;
	endif;
	?>
</div>
<?php do_action( 'tutor_after_course_loop_rating', get_the_ID() ); ?>
