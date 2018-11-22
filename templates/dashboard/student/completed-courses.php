<h1><?php _e('Completed Course', 'tutor'); ?></h1>
<div class="tutor-dashboard-content-inner">

<?php
$completed_courses = tutor_utils()->get_courses_by_user();

if ($completed_courses->have_posts()):

	while ($completed_courses->have_posts()):
        $completed_courses->the_post();
		?>
        <div class="tutor-mycourse-wrap tutor-mycourse-<?php the_ID(); ?>">
            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> </h3>
            <div class="tutor-meta tutor-course-metadata">
				<?php
                    $total_lessons = tutor_utils()->get_lesson_count_by_course();
                    $completed_lessons = tutor_utils()->get_completed_lesson_count_by_course();
				?>
                <p>
					<?php _e(sprintf("%d Lessons, %d of %d lessons completed", $total_lessons, $completed_lessons, $total_lessons), 'tutor');?>
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
