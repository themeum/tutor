<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<h3><?php _e('Enrolled Courses', 'tutor'); ?></h3>

<div class="tutor-dashboard-content-inner enrolled-courses">

    <div class="tutor-dashboard-inline-links">
        <ul>
            <li class="active">
                <a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('enrolled-courses'); ?>"> 
                    <?php _e('All Courses', 'tutor'); ?>
                </a> 
            </li>
            <li>
                <a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('enrolled-courses/active-courses'); ?>"> 
                    <?php _e('In Progress', 'tutor'); ?> 
                </a> 
            </li>
            <li>
                <a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('enrolled-courses/completed-courses'); ?>">
					<?php _e('Completed Courses', 'tutor'); ?> 
                </a> 
            </li>
        </ul>
    </div>

    <div class="tutor-course-listing tutor-course-listing-grid-3">
	<?php
	$my_courses = tutor_utils()->get_enrolled_courses_by_user(get_current_user_id(), array('private', 'publish'));

	if ($my_courses && $my_courses->have_posts()):
		while ($my_courses->have_posts()):
			$my_courses->the_post();
			$avg_rating = tutor_utils()->get_course_rating()->rating_avg;
			$tutor_course_img = get_tutor_course_thumbnail_src();
            /**
             * wp 5.7.1 showing plain permalink for private post
             * since tutor do not work with plain permalink
             * url is set to post_type/slug (courses/course-slug)
             * @since 1.8.10
            */
            
            $post = $my_courses->post;
            $custom_url = home_url($post->post_type.'/'.$post->post_name);
			?>
           
            <?php
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
            ?>

			<?php
		endwhile;

		wp_reset_postdata();
    else:
        echo "<div class='tutor-mycourse-wrap'><div class='tutor-mycourse-content'>".__('You haven\'t purchased any course', 'tutor')."</div></div>";
	endif;

	?>
    </div>
</div>
