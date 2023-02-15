<?php
/**
 * Template for displaying single quiz
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

use Tutor\Models\CourseModel;

global $previous_id;

// Get the ID of this content and the corresponding course.
$course_content_id     = get_the_ID();
$course_id             = tutor_utils()->get_course_id_by_subcontent( $course_content_id );
$content_id            = tutor_utils()->get_post_id( $course_content_id );
$contents              = tutor_utils()->get_course_prev_next_contents_by_id( $content_id );
$previous_id           = $contents->previous_id;
$course                = CourseModel::get_course_by_quiz( get_the_ID() );
$enable_spotlight_mode = tutor_utils()->get_option( 'enable_spotlight_mode' );
ob_start();
?>
<input type="hidden" name="tutor_quiz_id" id="tutor_quiz_id" value="<?php the_ID(); ?>">
<?php tutor_load_template( 'single.common.header', array( 'course_id' => $course_id ) ); ?>

<?php ob_start(); ?>
<div class="tutor-quiz-wrapper tutor-d-flex tutor-justify-center tutor-mt-80 tutor-pb-80 tutor-px-16">
	<input type="hidden" name="tutor_quiz_id" id="tutor_quiz_id" value="<?php the_ID(); ?>">

	<?php
	if ( $course ) {
		tutor_single_quiz_top();
		tutor_single_quiz_body();
	} else {
		tutor_single_quiz_no_course_belongs();
	}
	?>
</div>
<?php
tutor_load_template( 'single.common.footer', array( 'course_id' => $course_id ) );
echo apply_filters( 'tutor_quiz/single/wrapper', ob_get_clean() ); //phpcs:ignore
tutor_load_template_from_custom_path(
	__DIR__ . '/single-content-loader.php',
	array(
		'context'      => 'quiz',
		'html_content' => ob_get_clean(),
	),
	false
);
?>
