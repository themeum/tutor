<?php
/**
 * Course Card Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Enrolled_Courses
 * @author Themeum
 *
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Models\CourseModel;

$course_permalink = get_the_permalink();
$course_title     = get_the_title();
$tutor_course_img = get_tutor_course_thumbnail_src();

$course_id       = get_the_ID();
$course_progress = tutor_utils()->get_course_completed_percent( $course_id, 0, true );

$course_categories = get_the_terms( $course_id, CourseModel::COURSE_CATEGORY );
$category_names    = is_array( $course_categories ) ? wp_list_pluck( $course_categories, 'name' ) : array();
$category          = implode( ', ', $category_names );

$course_learning_url = tutor_utils()->get_course_first_lesson();
if ( get_post_type() !== tutor()->course_post_type ) {
	$course_learning_url = get_permalink();
}

if ( ! $course_learning_url ) {
	$course_learning_url = $course_permalink;
}

?>

<div
	class="tutor-progress-card"
	role="link"
	tabindex="0"
	x-data="{
		navigate() {
			window.location.href = '<?php echo esc_js( esc_url( $course_learning_url ) ); ?>';
		}
	}"
	@click="navigate()"
	@keydown.enter.prevent="navigate()"
	@keydown.space.prevent="navigate()"
>
	<div class="tutor-progress-card-inner">
		<div class="tutor-progress-card-thumbnail">
			<?php do_action( 'tutor_courses_card_before_thumbnail', $course_id ); ?>
			<?php if ( ! empty( $tutor_course_img ) ) : ?>
				<img src="<?php echo esc_url( $tutor_course_img ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
			<?php endif; ?>
		</div>

		<div class="tutor-progress-card-content">
			<!-- course header  -->
			<div class="tutor-progress-card-header">
				<?php if ( ! empty( $category ) ) : ?>
					<div class="tutor-progress-card-category">
						<?php echo esc_html( $category ); ?>
					</div>
				<?php endif; ?>
				<h3 class="tutor-progress-card-title tutor-line-clamp-2">
					<?php the_title(); ?>
				</h3>
			</div>

			<!-- course progress  -->
			<?php if ( $course_progress['completed_count'] > 0 || $course_progress['total_count'] > 0 ) : ?>
				<div class="tutor-progress-card-progress">
					<?php if ( $course_progress['total_count'] > 0 ) : ?>
						<div class="tutor-progress-card-details">
							<?php
							$progress_msg = sprintf(
								esc_html(
									// translators: %1$s is the completed count, %2$s is the total count.
									_n(
										'%1$s of %2$s lesson',
										'%1$s of %2$s lessons',
										(int) $course_progress['total_count'],
										'tutor'
									)
								),
								esc_html( $course_progress['completed_count'] ),
								esc_html( $course_progress['total_count'] )
							);
							// phpcs:ignore -- already sanitized
							echo apply_filters( 'tutor_course_progress_message', esc_html( $progress_msg ), $course_progress['completed_count'], $course_progress['total_count'] );
							?>
							<span class="tutor-progress-card-separator">•</span>
							<?php
								printf(
									// translators: %1$s is the completed percent.
									esc_html__( '%1$s%% Complete', 'tutor' ),
									esc_html( $course_progress['completed_percent'] )
								);
							?>
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

	<div class="tutor-progress-card-actions">
		<?php do_action( 'tutor_course_action_btn', $course_id ); ?>
	</div>
</div>
