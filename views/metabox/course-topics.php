<div class="course-contents">

	<?php
    $post_id = get_the_ID();


    print_r(get_post($post_id));


	$topics = range(1,10);

	foreach ($topics as $topic){
		?>

        <div id="lms-topics-<?php echo $topic; ?>" class="lms-topics-wrap">

            <div class="lms-topics-top">
                <h3>
                    <i class="dashicons dashicons-move course-move-handle"></i> <a href="">Topics <?php echo $topic; ?></a> <i class="dashicons dashicons-edit"></i>
                </h3>
            </div>


            <div class="lms-lessions">

				<?php
				$lessons = range(1,3);

				foreach ($lessons as $lesson){
					?>
                    <div class=" lms-lesson lms-lesson-<?php echo $lesson ?>">
                        <div class="lms-lesson-top">
                            <i class="dashicons dashicons-move"></i>
                            <a href=""> <i class="dashicons dashicons-list-view"></i> Lesson <?php echo $lesson ?></a><i class="dashicons dashicons-edit"></i>
                        </div>
                    </div>

					<?php
				}
				?>
            </div>
        </div>
		<?php
	}
	?>

    <input id="lms_topics_lessons_sorting" type="hidden" value="" />
</div>



<div class="lms-untopics-lessons">

    <h1><?php _e('Un Topics Lessons'); ?></h1>


    <div class="lms-lessions">

		<?php
		$lessons = range(1,3);

		foreach ($lessons as $lesson){
			?>
            <div class=" lms-lesson lms-lesson-<?php echo $lesson ?>">
                <div class="lms-lesson-top">
                    <i class="dashicons dashicons-move"></i>
                    <a href=""> <i class="dashicons dashicons-list-view"></i> Lesson <?php echo $lesson ?></a><i class="dashicons dashicons-edit"></i>
                </div>
            </div>

			<?php
		}
		?>
    </div>

</div>