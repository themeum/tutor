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
$wrapper_class       = isset( $wrapper_class ) ? $wrapper_class : 'tutor-ratings-stars tutor-flex tutor-items-center tutor-gap-2';
$average_rating      = isset( $course_rating->rating_avg ) ? (float) $course_rating->rating_avg : 0.0;
$has_rating          = $average_rating > 0;
$show_course_ratings = apply_filters( 'tutor_show_course_ratings', true, get_the_ID() );
?>

<div class="<?php echo esc_attr( $wrapper_class ); ?> tutor-text-exception4" data-rating-value="<?php echo esc_attr( number_format( $average_rating, 2, '.', '' ) ); ?>">
	<?php
	if ( $show_course_ratings ) :
		for ( $i = 1; $i <= 5; $i++ ) :
			if ( $average_rating >= $i ) {
				SvgIcon::make()->name( Icon::STAR_FILL )->size( 12 )->render();
			} elseif ( $average_rating >= ( $i - 0.5 ) ) {
				SvgIcon::make()->name( Icon::STAR_HALF )->size( 12 )->render();
			} else {
				SvgIcon::make()->name( Icon::STAR_LINE )->size( 12 )->render();
			}
		endfor;

		if ( $has_rating ) :
			?>
			<div class="tutor-ratings-average">
				<?php echo esc_html( apply_filters( 'tutor_course_rating_average', $course_rating->rating_avg ) ); ?>
			</div>
			<div class="tutor-ratings-count">
				(<?php echo esc_html( (int) $course_rating->rating_count ); ?>)
			</div>
			<?php
		endif;
	endif;
	?>
</div>
