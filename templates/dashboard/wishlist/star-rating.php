<?php
/**
 * Star Rating Component
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\SvgIcon;

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
				SvgIcon::make()->name( Icon::STAR_FILL )->size( 12 )->render();
			} elseif ( ( $rating_count - $i ) >= -0.5 ) {
				SvgIcon::make()->name( Icon::STAR_HALF )->size( 12 )->render();
			} else {
				SvgIcon::make()->name( Icon::STAR_LINE )->size( 12 )->render();
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
