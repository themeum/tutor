<?php
/**
 * Template for displaying courses
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

get_header(); ?>

	<div class="<?php lms_container_classes() ?>">

		<?php
		do_action('lms_course/archive/before_loop');

		if ( have_posts() ) :
			/* Start the Loop */

			lms_course_loop_start();

			while ( have_posts() ) : the_post();
				/**
				 * @hook lms_course/archive/before_loop_course
				 * @type action
				 * Usage Idea, you may keep a loop within a wrap, such as bootstrap col
				 */
				do_action('lms_course/archive/before_loop_course');


				lms_load_template('loop.course');


				/**
				 * @hook lms_course/archive/after_loop_course
				 * @type action
				 * Usage Idea, If you start any div before course loop, you can end it here, such as </div>
				 */
				do_action('lms_course/archive/after_loop_course');
			endwhile;

			lms_course_loop_end();

		else :

			/**
			 * No course found
			 */
			lms_load_template('course-none');

		endif; ?>

		<?php
		do_action('lms_course/archive/after_loop');
		?>



	</div><!-- .wrap -->

<?php get_footer();
