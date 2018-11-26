<h1><?php _e('Active Course', 'tutor'); ?></h1>
<div class="tutor-dashboard-content-inner">
	<?php
	$active_courses = tutor_utils()->get_active_courses_by_user();

	if ($active_courses && $active_courses->have_posts()):
		while ($active_courses->have_posts()):
			$active_courses->the_post();
			?>
            <div class="tutor-mycourse-wrap tutor-mycourse-<?php the_ID(); ?>">
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
        echo "<div class='tutor-mycourse-wrap'>". esc_html__('There\'s no active course', 'tutor') ."</div>";
	endif;

	?>
</div>
