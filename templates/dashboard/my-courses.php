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
						?>
                        <ul>
                            <li>
								<?php
								_e('Total Lessons:', 'tutor');
								echo "<span>$total_lessons</span>";
								?>
                            </li>
                            <li>
								<?php
								_e('Completed Lessons:', 'tutor');
								echo "<span>$completed_lessons / $total_lessons</span>";
								?>
                            </li>
                        </ul>
                    </div>

                    <div class="mycourse-footer">
                        <div class="tutor-mycourse-status">
                            <?php echo ucwords($post->post_status); ?>
                        </div>

                        <div class="tutor-mycourses-stats">
	                        <?php
	                        $course_duration = get_tutor_course_duration_context();
	                        $course_students = tutor_utils()->count_enrolled_users_by_course();

	                        if(!empty($course_duration)) echo "<i class='tutor-icon-clock'></i> <span>$course_duration</span>";
	                        echo "<i class='tutor-icon-user'></i> <span>$course_students</span>";
	                        ?>
                        </div>

                        <div class="tutor-mycourses-price">
                            <?php echo tutor_utils()->get_course_price(); ?>
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



</div>
