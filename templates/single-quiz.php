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

$course = dozent_utils()->get_course_by_quiz(get_the_ID());
?>

<?php do_action('dozent_quiz/single/before/wrap'); ?>

    <div <?php dozent_post_class(); ?>>
        <div class="dozent-quiz-single-wrap dozent-container">
            <input type="hidden" name="dozent_quiz_id" id="dozent_quiz_id" value="<?php the_ID(); ?>">

	        <?php
            if ($course){
	            dozent_single_quiz_top();
	            dozent_single_quiz_body();
            }else{
	            dozent_single_quiz_no_course_belongs();
            }
	        ?>

        </div>
    </div><!-- .wrap -->

<?php do_action('dozent_quiz/single/after/wrap');

get_footer();