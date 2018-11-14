<?php
/**
 * Template for displaying single quiz
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

get_header();

$course = tutor_utils()->get_course_by_quiz(get_the_ID());
?>

<?php do_action('tutor_quiz/single/before/wrap'); ?>

    <div <?php tutor_post_class(); ?>>
        <div class="tutor-quiz-single-wrap tutor-container">
            <input type="hidden" name="tutor_quiz_id" id="tutor_quiz_id" value="<?php the_ID(); ?>">

	        <?php
            if ($course){
	            tutor_single_quiz_top();
	            tutor_single_quiz_body();
            }else{
	            tutor_single_quiz_no_course_belongs();
            }
	        ?>

        </div>
    </div><!-- .wrap -->

<?php do_action('tutor_quiz/single/after/wrap');

get_footer();