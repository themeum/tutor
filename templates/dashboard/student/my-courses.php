<h1><?php _e('My Courses', 'tutor'); ?></h1>

<div class="tutor-dashboard-content-inner">
<?php
$my_courses = tutor_utils()->get_enrolled_courses_by_user();

if ($my_courses->have_posts()):

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
                    <li><?php ?></li>
                </ul>
                <p>
                    <?php _e(sprintf("Total Lessons: %d, Completed Lessons: %d of %d lessons", $total_lessons, $completed_lessons, $total_lessons), 'tutor');?>
                </p>
            </div>

            <?php the_excerpt(); ?>
			<?php tutor_course_completing_progress_bar(); ?>
        </div>

		<?php
	endwhile;

	wp_reset_postdata();

endif;

?>

</div>
