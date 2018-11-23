<h1><?php _e('My Courses', 'dozent'); ?></h1>

<div class="dozent-dashboard-content-inner">
	<?php
	$my_courses = dozent_utils()->get_enrolled_courses_by_user();

	if ($my_courses && $my_courses->have_posts()):
		while ($my_courses->have_posts()):
			$my_courses->the_post();
			$avg_rating = dozent_utils()->get_course_rating()->rating_avg;
			?>
            <div class="dozent-mycourse-wrap dozent-mycourse-<?php the_ID(); ?>">
                <div class="dozent-mycourse-rating">
					<?php
					dozent_utils()->star_rating_generator($avg_rating);
					?>
                </div>
                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> </h3>
                <div class="dozent-meta dozent-course-metadata">
					<?php
					$total_lessons = dozent_utils()->get_lesson_count_by_course();
					$completed_lessons = dozent_utils()->get_completed_lesson_count_by_course();
					?>
                    <ul>
                        <li>
                            <?php
                                _e('Total Lessons:', 'dozent');
                                echo "<span>$total_lessons</span>";
                            ?>
                        </li>
                        <li>
                            <?php
                                _e('Completed Lessons:', 'dozent');
                                echo "<span>$completed_lessons / $total_lessons</span>";
                            ?>
                        </li>
                    </ul>
                </div>
                <?php dozent_course_completing_progress_bar(); ?>
				<?php the_excerpt(); ?>
            </div>

			<?php
		endwhile;

		wp_reset_postdata();
    else:
        echo "<div class='dozent-mycourse-wrap'>You didn't purchased any course</div>";
	endif;

	?>

</div>
