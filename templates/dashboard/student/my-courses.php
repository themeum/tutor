<h1><?php _e('My Courses', 'tutor'); ?></h1>

<div class="tutor-dashboard-content-inner">
	<?php
	$my_courses = tutor_utils()->get_enrolled_courses_by_user();

	if ($my_courses && $my_courses->have_posts()):
		while ($my_courses->have_posts()):
			$my_courses->the_post();
			$avg_rating = tutor_utils()->get_course_rating()->rating_avg;
			?>
            <div class="tutor-mycourse-wrap tutor-mycourse-<?php the_ID(); ?>">
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
                <?php tutor_course_completing_progress_bar(); ?>
				<?php the_excerpt(); ?>
            </div>

			<?php
		endwhile;

		wp_reset_postdata();
    else:
        echo "<div class='tutor-mycourse-wrap'>You didn't purchased any course</div>";
	endif;

	?>

</div>
