<?php
/**
 * Course Card Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Enrolled_Courses
 * @author Themeum
 */

use Tutor\Models\CourseModel;

$course_permalink = get_the_permalink();
$course_title     = get_the_title();
$tutor_course_img = get_tutor_course_thumbnail_src();

$course_id       = get_the_ID();
$course_progress = tutor_utils()->get_course_completed_percent( $course_id, 0, true );

$course_categories = get_the_terms( $course_id, CourseModel::COURSE_CATEGORY );
$category_names    = is_array( $course_categories ) ? wp_list_pluck( $course_categories, 'name' ) : array();
$category          = implode( ', ', $category_names );

?>

<a href="<?php echo esc_url( $course_permalink ); ?>">
	<div class="tutor-card tutor-progress-card">
		
		<div class="tutor-courses-thumb tutor-position-relative">
			<?php do_action( 'tutor_my_courses_card_thumbnail_before', $course_id ); ?>

			<?php if ( ! empty( $tutor_course_img ) ) : ?>
				<div class="tutor-progress-card-thumbnail">
					<img src="<?php echo esc_url( $tutor_course_img ); ?>" alt="<?php the_title(); ?>" />
				</div>
			<?php endif; ?>
		</div>


		<div class="tutor-progress-card-content">

			<div class="tutor-progress-card-header">
				<?php if ( ! empty( $category ) ) : ?>
					<div class="tutor-progress-card-category">
						<?php echo esc_html( $category ); ?>
					</div>
				<?php endif; ?>
				<h3 class="tutor-progress-card-title">
					<?php the_title(); ?>
				</h3>
			</div>

			<!-- course progress  -->
			<?php if ( $course_progress['completed_count'] > 0 || $course_progress['total_count'] > 0 ) : ?>
				<div class="tutor-progress-card-progress">
					<?php if ( $course_progress['total_count'] > 0 ) : ?>
						<div class="tutor-progress-card-details">
							<?php
							printf(
								esc_html(
									_n(
										'%1$s of %2$s lesson',
										'%1$s of %2$s lessons',
										(int) $course_progress['total_count'],
										'tutor'
									)
								),
								esc_html( $course_progress['completed_percent'] ),
								esc_html( $course_progress['total_count'] )
							);

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