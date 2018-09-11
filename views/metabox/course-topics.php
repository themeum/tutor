<div class="course-contents">

	<?php
    $course_id = get_the_ID();

	$query_lesson = lms_utils()->get_lesson($course_id);
	$query_topics = lms_utils()->get_topics($course_id);
	$attached_lesson_ids = array();

	//lms_utils()->print_view($lessons);
    //print_r(get_post($course_id));


	//$topics = range(1,10);

	foreach ($query_topics->posts as $topic){
		?>
        <div id="lms-topics-<?php echo $topic->ID; ?>" class="lms-topics-wrap">

            <div class="lms-topics-top">
                <h3>
                    <i class="dashicons dashicons-move course-move-handle"></i>
                    <span class="topic-inner-title"><?php echo $topic->post_title; ?></span>
                    <i class="dashicons dashicons-edit topic-edit-icon"></i>

                    <span class="topic-delete-btn">
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?action=lms_delete_topic&topic_id='.$topic->ID), lms()->nonce_action, lms()->nonce); ?>" title="<?php _e('Delete Topic',
                            'lms');
                        ?>">
                            <i class="dashicons dashicons-trash"></i>
                        </a>
                    </span>
                </h3>

                <div class="lms-topics-edit-form" style="display: none;">
                    <div class="lms-option-field-row">
                        <div class="lms-option-field-label">
                            <label for=""><?php _e('Topic Name', 'lms'); ?></label>
                        </div>
                        <div class="lms-option-field">
                            <input type="text" name="topic_title" value="<?php echo $topic->post_title; ?>">

                            <p class="desc">
				                <?php _e('Topic title will be publicly show where required, you can call it as a section also in course', 'lms'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="lms-option-field-row">
                        <div class="lms-option-field-label">
                            <label for=""><?php _e('Topic Summery', 'lms'); ?></label>
                        </div>
                        <div class="lms-option-field">
                            <textarea name="topic_summery"><?php echo $topic->post_content; ?></textarea>

                            <p class="desc">
				                <?php _e('The idea of a summary is a short text to prepare students for the activities within the topic or week. The text is shown on the course page under the topic name.', 'lms'); ?>
                            </p>

                            <button type="button" class="button button-primary lms-topics-edit-button"><i class="dashicons dashicons-edit"></i> <?php _e('Edit Topic', 'lms');
                            ?></button>
                        </div>
                    </div>
                </div>

            </div>

            <div class="lms-lessions">
				<?php
				$lessons = lms_utils()->get_lessons_by_topic($topic->ID);
				?>
                <div class="drop-lessons" style="display: <?php echo count($lessons->posts) ? 'none' : 'block'; ?>;">
                    <p><i class="dashicons dashicons-upload"></i> <?php _e('Drop lesson here', 'lms'); ?></p>
                </div>
                <?php
				//print_r($lessons);
				foreach ($lessons->posts as $lesson){
					$attached_lesson_ids[] = $lesson->ID;
					?>
                    <div id="lms-lesson-<?php echo $lesson->ID; ?>" class=" lms-lesson lms-lesson-<?php echo $lesson->ID; ?>">
                        <div class="lms-lesson-top">
                            <i class="dashicons dashicons-move"></i>
                            <a href="<?php echo admin_url("post.php?post={$lesson->ID}&action=edit"); ?>"> <i class="dashicons dashicons-list-view"></i> <?php echo $lesson->post_title; ?> </a>
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

    <input type="hidden" id="lms_topics_lessons_sorting" name="lms_topics_lessons_sorting" value="" />
</div>


<div class="lms-untopics-lessons">
    <h1><?php _e('Un Topics Lessons'); ?></h1>

    <div class="lms-lessions">
        <div class="drop-lessons" style="display: <?php echo count($query_lesson->posts) == count($attached_lesson_ids) ? 'block' : 'none'; ?>;">
            <p><i class="dashicons dashicons-upload"></i> <?php _e('Drop un topics lesson here', 'lms'); ?></p>
        </div>

		<?php
		foreach ($query_lesson->posts as $lesson){
		    if ( ! in_array($lesson->ID, $attached_lesson_ids)) {
			    ?>
                <div id="lms-lesson-<?php echo $lesson->ID; ?>" class="lms-lesson lms-lesson-<?php echo $lesson->ID; ?>">
                    <div class="lms-lesson-top">
                        <i class="dashicons dashicons-move"></i>
					    <?php edit_post_link( $lesson->post_title, null, null, $lesson->ID ); ?>
                    </div>
                </div>
			    <?php
		    }
		}
		?>
    </div>
</div>


<div class="lms-metabox-add-topics">

    <h3><?php _e('Add Topic', 'lms'); ?></h3>

    <div class="lms-option-field-row">
        <div class="lms-option-field-label">
            <label for=""><?php _e('Topic Name', 'lms'); ?></label>
        </div>
        <div class="lms-option-field">
            <input type="text" name="topic_title" value="">

            <p class="desc">
		        <?php _e('Topic title will be publicly show where required, you can call it as a section also in course', 'lms'); ?>
            </p>
        </div>
    </div>


    <div class="lms-option-field-row">
        <div class="lms-option-field-label">
            <label for=""><?php _e('Topic Summery', 'lms'); ?></label>
        </div>
        <div class="lms-option-field">
            <textarea name="topic_summery"></textarea>

            <p class="desc">
				<?php _e('The idea of a summary is a short text to prepare students for the activities within the topic or week. The text is shown on the course page under the topic name.', 'lms'); ?>
            </p>

	        <?php
            submit_button(__('Add Topic', 'lms')); ?>
        </div>
    </div>


</div>

