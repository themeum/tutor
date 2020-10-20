<div class="tutor-course-filter-wrapper">
    <div class="tutor-course-filter-container">
        <?php tutor_load_template('course-filter.filters'); ?>
    </div>
    <div>
        <div class="<?php tutor_container_classes() ?> tutor-course-filter-loop-container">
            <?php tutor_load_template('archive-course-init'); ?>
        </div><!-- .wrap -->
    </div>
</div>


<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

return;

if ( have_posts() ) :
	/* Start the Loop */

	tutor_course_loop_start();

	while ( have_posts() ) : the_post();
		/**
		 * @hook tutor_course/archive/before_loop_course
		 * @type action
		 * Usage Idea, you may keep a loop within a wrap, such as bootstrap col
		 */
		do_action('tutor_course/archive/before_loop_course');

		tutor_load_template('loop.course');

		/**
		 * @hook tutor_course/archive/after_loop_course
		 * @type action
		 * Usage Idea, If you start any div before course loop, you can end it here, such as </div>
		 */
		do_action('tutor_course/archive/after_loop_course');
	endwhile;

	tutor_course_loop_end();

endif;
