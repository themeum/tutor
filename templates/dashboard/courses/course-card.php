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
use TutorPro\CourseBundle\Models\BundleModel;

$course_permalink = get_the_permalink();
$course_title     = get_the_title();

$course_id       = get_the_ID();
$course_progress = tutor_utils()->get_course_completed_percent( $course_id, 0, true );

$course_categories = get_the_terms( $course_id, CourseModel::COURSE_CATEGORY );
$category_names    = is_array( $course_categories ) && ! is_wp_error( $course_categories ) ? wp_list_pluck( $course_categories, 'name' ) : array();
$category          = implode( ', ', $category_names );

$bundle_id = BundleModel::get_bundle_id_by_course( $course_id );

?>

<a href="<?php echo esc_html( $course_permalink ); ?>">
	<div class="tutor-card tutor-progress-card">
		
		<div class="tutor-courses-thumb tutor-position-relative">
			<?php
			if ( $bundle_id ) :
				$bundle_course = BundleModel::get_bundle_courses( $bundle_id );
				?>
				<div class="tutor-bundle-course-badge tutor-badge tutor-badge-exception tutor-badge-circle">
					<?php tutor_utils()->render_svg_icon( Icon::BUNDLE ); ?>
					<span><?php echo esc_html( count( $bundle_course ) ); ?></span> Course Bundle
				</div>
			<?php endif; ?>
			<?php tutor_load_template( 'loop.thumbnail' ); ?>
		</div>

		<div class="tutor-progress-card-content">
			<?php tutor_load_template( 'loop.title', array( 'category' => $category ) ); ?>

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