<?php
/**
 * A single course loop rating
 *
 * @package Tutor\Templates
 * @subpackage CourseLoopPart
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$class               = isset( $class ) ? ' ' . $class : ' tutor-mb-8';
$show_course_ratings = apply_filters( 'tutor_show_course_ratings', true, get_the_ID() );
?>

<div class="tutor-course-ratings<?php echo esc_html( $class ); ?>">
	<?php if ( $show_course_ratings ) : ?>
	<div class="tutor-ratings">
		<div class="tutor-ratings-stars">
			<?php
				$course_rating = tutor_utils()->get_course_rating();
				tutor_utils()->star_rating_generator_course( $course_rating->rating_avg );
			?>
		</div>

		<?php if ( $course_rating->rating_avg > 0 ) : ?>
			<div class="tutor-ratings-average">
				<?php echo esc_html( apply_filters( 'tutor_course_rating_average', $course_rating->rating_avg ) ); ?>
			</div>
			<div class="tutor-ratings-count">
				(<?php echo esc_html( $course_rating->rating_count > 0 ? $course_rating->rating_count : 0 ); ?>)
			</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>
</div>
<?php do_action( 'tutor_after_course_loop_rating', get_the_ID() ); ?>
