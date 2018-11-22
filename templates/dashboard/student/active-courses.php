<h1><?php _e('Active Course', 'dozent'); ?></h1>
<div class="dozent-dashboard-content-inner">
	<?php
	$active_courses = dozent_utils()->get_active_courses_by_user();
	if( ! $active_courses){
		return;
	}

	if ($active_courses->have_posts()):
		while ($active_courses->have_posts()):
			$active_courses->the_post();
			?>
            <div class="dozent-mycourse-wrap dozent-mycourse-<?php the_ID(); ?>">
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
        echo "There's no active course";
	endif;

	?>
</div>
