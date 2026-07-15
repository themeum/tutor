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
	@click.stop="navigate()"
	@keydown.enter.prevent="navigate()"
	@keydown.space.prevent="navigate()"
>
	<div class="tutor-progress-card-inner">
		<?php tutor_load_template( 'dashboard.courses.course-card-thumbnail', array( 'thumbnail_img' => $tutor_course_img, 'post_id' => $course_id ) ); ?>

		<div class="tutor-progress-card-content">
			<?php tutor_load_template( 'dashboard.courses.course-card-header', array( 'category' => $category ) ); ?>

			<?php tutor_load_template( 'dashboard.courses.course-card-progress', array( 'course_progress' => $course_progress ) ); ?>
		</div>
	</div>

	<div class="tutor-progress-card-actions">
		<?php do_action( 'tutor_course_action_btn', $course_id ); ?>
	</div>
</div>
