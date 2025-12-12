<?php
/**
 * Course Card Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Enrolled_Courses
 * @author Themeum
 */

use TUTOR\Icon;
use Tutor\Models\CourseModel;

$course_permalink = get_the_permalink();
$course_title     = get_the_title();

$tutor_course_img = get_tutor_course_thumbnail_src();

$course_id       = get_the_ID();
$course_progress = tutor_utils()->get_course_completed_percent( $course_id, 0, true );

$course_categories = get_the_terms( $course_id, CourseModel::COURSE_CATEGORY );
$category_names    = is_array( $course_categories ) && ! is_wp_error( $course_categories ) ? wp_list_pluck( $course_categories, 'name' ) : array();
$category          = implode( ', ', $category_names );

?>

<a href="<?php echo esc_html( $course_permalink ); ?>">
	<div class="tutor-card tutor-progress-card">
		<?php
		// load loop folder thumbnail template here.
		tutor_load_template( 'loop.thumbnail', array( 'course_title' => $course_title ) );
		?>
		<div class="tutor-progress-card-content">
			<?php
			tutor_load_template(
				'loop.title',
				array(
					'course_title' => $course_title,
					'category'     => $category,
				)
			);
			?>

			<!-- course progress  -->
			<?php if ( $course_progress['completed_count'] > 0 || $course_progress['total_count'] > 0 ) : ?>
				<div class="tutor-progress-card-progress">
					<?php if ( $course_progress['total_count'] > 0 ) : ?>
						<div class="tutor-progress-card-details">
							<?php
							echo esc_html( $course_progress['completed_percent'] ) . ' ' . esc_html__( 'of', 'tutor' ) . ' ' . esc_html( $course_progress['total_count'] ) . ' ' . esc_html__( 'lessons', 'tutor' );
							?>
							<span class="tutor-progress-card-separator">•</span>
							<?php echo esc_html( $course_progress['completed_percent'] ); ?>%
							<?php echo esc_html__( 'Complete', 'tutor' ); ?>
						</div>
					<?php endif; ?>
					<?php if ( $course_progress['completed_percent'] >= 0 ) : ?>
						<div class="tutor-progress-card-bar">
							<div class="tutor-progress-bar" data-tutor-animated>
								<div class="tutor-progress-bar-fill"
									style="--tutor-progress-width: <?php echo esc_attr( $course_progress['completed_percent'] ); ?>%;">
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</a>