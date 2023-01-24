<?php
/**
 * Single quiz content
 *
 * @package Tutor\Templates
 * @subpackage Single\Quiz
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @version 1.4.3
 */

use Tutor\Models\CourseModel;

$course    = CourseModel::get_course_by_quiz( get_the_ID() );
$course_id = tutor_utils()->get_course_id_by( 'lesson', get_the_ID() );
?>

<?php tutor_load_template( 'single.common.header', array( 'course_id' => $course_id ) ); ?>

<?php ob_start(); ?>
<div class="tutor-quiz-single-wrap ">
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
<?php echo apply_filters( 'tutor_quiz/single/wrapper', ob_get_clean() );//phpcs:ignore ?>
