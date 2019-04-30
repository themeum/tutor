<h3><?php _e('My Courses', 'tutor'); ?></h3>

<div class="tutor-dashboard-content-inner">

	<?php
	$my_courses = tutor_utils()->get_courses_by_instructor(null, 'any');

	if (is_array($my_courses) && count($my_courses)):
		global $post;
		foreach ($my_courses as $post):
			setup_postdata($post);

			$avg_rating = tutor_utils()->get_course_rating()->rating_avg;
			?>
        
            <div class="tutor-mycourse-wrap tutor-mycourse-<?php the_ID(); ?>">

                <div class="tutor-mycourse-thumbnail">
					<?php
					tutor_course_loop_thumbnail();
					?>
                </div>

                <div class="tutor-mycourse-content">

                    <div class="tutor-mycourse-rating">
						<?php
						tutor_utils()->star_rating_generator($avg_rating);
						?>
                    </div>
                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> </h3>
                    <div class="tutor-meta tutor-course-metadata">
						<?php
                            $total_lessons = tutor_utils()->get_lesson_count_by_course();
                            $completed_lessons = tutor_utils()->get_completed_lesson_count_by_course();

                            $course_duration = get_tutor_course_duration_context();
                            $course_students = tutor_utils()->count_enrolled_users_by_course();
						?>
                        <ul>
                            <li>
								<?php
								_e('Status:', 'tutor');
								$status = ucwords($post->post_status);
								echo "<span>$status</span>";
								?>
                            </li>
                            <li>
								<?php
								_e('Duration:', 'tutor');
								echo "<span>$course_duration</span>";
								?>
                            </li>
                            <li>
								<?php
								_e('Students:', 'tutor');
								echo "<span>$course_students</span>";
								?>
                            </li>
                        </ul>
                    </div>

                    <div class="mycourse-footer">
                        <div class="tutor-mycourses-stats">
	                        <?php echo tutor_utils()->tutor_price(tutor_utils()->get_course_price()); ?>
                            <a href="<?php echo get_edit_post_link(get_the_ID()); ?>" class="tutor-mycourse-edit"> <i class="tutor-icon-pencil"></i> Edit</a>
                            <a href="#course-delete" class="tutor-mycourse-delete"> <i class="tutor-icon-garbage"></i> Delete</a>
                        </div>

                    </div>
                </div>

            </div>
		<?php
		endforeach;
	else : ?>
        <div>
            <h2><?php _e("Not Found" , 'tutor'); ?></h2>
            <p><?php _e("Sorry, but you are looking for something that isn't here." , 'tutor'); ?></p>
        </div>
	<?php endif; ?>


    <div class="tutor-frontend-modal" data-popup-rel="#course-delete" style="display: none">
        <div class="tutor-frontend-modal-overlay"></div>
        <div class="tutor-frontend-modal-content">
            <button class="tm-close tutor-icon-line-cross"></button>

            <div class="tutor-modal-body tutor-course-delete-popup">
                <img src="<?php echo tutor()->url . 'assets/images/delete-icon.png' ?>" alt="">
                <h3><?php _e('Delete This Course?', 'tutor'); ?></h3>
                <p><?php _e('You have selected to this course Creative Nonfiction: Write Truth with Style If this was the action that you wanted to do . Please confirm your choice', 'tutor'); ?></p>
                <div class="tutor-modal-button-group">
                    <button><?php _e('Cancel', 'tutor') ?></button>
                    <button class="tutor-danger"><?php _e('Yes, Delete Course', 'tutor') ?></button>
                </div>
            </div>
            
        </div> <!-- tutor-frontend-modal-content -->
    </div> <!-- tutor-frontend-modal -->

</div>
