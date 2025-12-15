<?php
/**
 * Display loop thumbnail
 *
 * @package Tutor\Templates
 * @subpackage CourseLoopPart
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

use TUTOR\Icon;

$tutor_course_img = get_tutor_course_thumbnail_src();
?>

<?php if ( ! empty( $tutor_course_img ) ) : ?>
	<div class="tutor-progress-card-thumbnail">
		<img src="<?php echo esc_url( $tutor_course_img ); ?>" alt="<?php the_title(); ?>" />
	</div>
<?php endif; ?>
