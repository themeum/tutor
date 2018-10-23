<div class="course-contents">

	<?php
    $course_id = get_the_ID();

	$query_lesson = tutor_utils()->get_lesson($course_id);
	$query_topics = tutor_utils()->get_topics($course_id);
	$attached_lesson_ids = array();

	//tutor_utils()->print_view($lessons);
    //print_r(get_post($course_id));


	//$topics = range(1,10);

	foreach ($query_topics->posts as $topic){
		?>
        <div id="tutor-topics-<?php echo $topic->ID; ?>" class="tutor-topics-wrap">

            <div class="tutor-topics-top">
                <h3>
                    <i class="dashicons dashicons-move course-move-handle"></i>
                    <span class="topic-inner-title"><?php echo $topic->post_title; ?></span>
                    <i class="dashicons dashicons-edit topic-edit-icon"></i>

                    <span class="topic-delete-btn">
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?action=tutor_delete_topic&topic_id='.$topic->ID), tutor()->nonce_action, tutor()->nonce); ?>" title="<?php _e('Delete Topic',
                            'tutor');
                        ?>">
                            <i class="dashicons dashicons-trash"></i>
                        </a>
                    </span>
                </h3>

                <div class="tutor-topics-edit-form" style="display: none;">
                    <div class="tutor-option-field-row">
                        <div class="tutor-option-field-label">
                            <label for=""><?php _e('Topic Name', 'tutor'); ?></label>
                        </div>
                        <div class="tutor-option-field">
                            <input type="text" name="topic_title" value="<?php echo $topic->post_title; ?>">

                            <p class="desc">
				                <?php _e('Topic title will be publicly show where required, you can call it as a section also in course', 'tutor'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="tutor-option-field-row">
                        <div class="tutor-option-field-label">
                            <label for=""><?php _e('Topic Summery', 'tutor'); ?></label>
                        </div>
                        <div class="tutor-option-field">
                            <textarea name="topic_summery"><?php echo $topic->post_content; ?></textarea>

                            <p class="desc">
				                <?php _e('The idea of a summary is a short text to prepare students for the activities within the topic or week. The text is shown on the course page under the topic name.', 'tutor'); ?>
                            </p>

                            <button type="button" class="button button-primary tutor-topics-edit-button"><i class="dashicons dashicons-edit"></i> <?php _e('Edit Topic', 'tutor');
                            ?></button>
                        </div>
                    </div>
                </div>

            </div>

            <div class="tutor-lessions">
				<?php
				$lessons = tutor_utils()->get_lessons_by_topic($topic->ID);
				?>
                <div class="drop-lessons" style="display: <?php echo count($lessons->posts) ? 'none' : 'block'; ?>;">
                    <p><i class="dashicons dashicons-upload"></i> <?php _e('Drop lesson here', 'tutor'); ?></p>
                </div>
                <?php
				//print_r($lessons);
				foreach ($lessons->posts as $lesson){
					$attached_lesson_ids[] = $lesson->ID;
					?>
                    <div id="tutor-lesson-<?php echo $lesson->ID; ?>" class=" tutor-lesson tutor-lesson-<?php echo $lesson->ID; ?>">
                        <div class="tutor-lesson-top">
                            <i class="dashicons dashicons-move"></i>
                            <a href="<?php echo admin_url("post.php?post={$lesson->ID}&action=edit"); ?>"> <i class="dashicons dashicons-list-view"></i> <?php echo $lesson->post_title; ?> </a>
                        </div>
                    </div>
					<?php
				}
				?>
            </div>



            <div class="tutor_add_quiz_wrap" data-add-quiz-under="<?php echo $topic->ID; ?>">

                <div class="tutor-available-quizzes">
                    <?php
                    $attached_quizzes = tutor_utils()->get_attached_quiz($topic->ID);
                    if ($attached_quizzes){
                        foreach ($attached_quizzes as $attached_quiz){
                            ?>
                            <div id="added-quiz-id-<?php echo $attached_quiz->ID; ?>" class="added-quiz-item added-quiz-item-<?php echo $attached_quiz->ID; ?>" data-quiz-id="<?php echo $attached_quiz->ID; ?>">
                                <span class="quiz-icon"><i class="dashicons dashicons-clock"></i></span>
                                <span class="quiz-name">
                                    <?php edit_post_link( $attached_quiz->post_title, null, null, $attached_quiz->ID ); ?>
                                </span>
                                <span class="quiz-control">
                                    <a href="javascript:;" class="tutor-quiz-delete-btn"><i class="dashicons dashicons-trash"></i></a>
                                </span>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>

                <div class="tutor-add-quiz-button-wrap">
                    <button type="button" class="button button-default tutor-add-quiz-btn"> <?php _e('Add Topic Quiz', 'tutor'); ?> </button>
                </div>
            </div>



        </div>
		<?php
	}
	?>

    <input type="hidden" id="tutor_topics_lessons_sorting" name="tutor_topics_lessons_sorting" value="" />
</div>


<div class="tutor-untopics-lessons">
    <h1><?php _e('Un Topics Lessons'); ?></h1>

    <div class="tutor-lessions">
        <div class="drop-lessons" style="display: <?php echo count($query_lesson->posts) == count($attached_lesson_ids) ? 'block' : 'none'; ?>;">
            <p><i class="dashicons dashicons-upload"></i> <?php _e('Drop un topics lesson here', 'tutor'); ?></p>
        </div>

		<?php
		foreach ($query_lesson->posts as $lesson){
		    if ( ! in_array($lesson->ID, $attached_lesson_ids)) {
			    ?>
                <div id="tutor-lesson-<?php echo $lesson->ID; ?>" class="tutor-lesson tutor-lesson-<?php echo $lesson->ID; ?>">
                    <div class="tutor-lesson-top">
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


<div class="tutor-metabox-add-topics">

    <h3><?php _e('Add Topic', 'tutor'); ?></h3>

    <div class="tutor-option-field-row">
        <div class="tutor-option-field-label">
            <label for=""><?php _e('Topic Name', 'tutor'); ?></label>
        </div>
        <div class="tutor-option-field">
            <input type="text" name="topic_title" value="">

            <p class="desc">
		        <?php _e('Topic title will be publicly show where required, you can call it as a section also in course', 'tutor'); ?>
            </p>
        </div>
    </div>


    <div class="tutor-option-field-row">
        <div class="tutor-option-field-label">
            <label for=""><?php _e('Topic Summery', 'tutor'); ?></label>
        </div>
        <div class="tutor-option-field">
            <textarea name="topic_summery"></textarea>

            <p class="desc">
				<?php _e('The idea of a summary is a short text to prepare students for the activities within the topic or week. The text is shown on the course page under the topic name.', 'tutor'); ?>
            </p>

	        <?php
            submit_button(__('Add Topic', 'tutor')); ?>
        </div>
    </div>


</div>



<div class="tutor-quizzes-modal-wrap">
    <div class="tutor-quizzes-modal-content">
        <div class="quizzes-modal-header">
            <div class="quizzes-search-bar">
                <input type="text" class="tutor-quizzes-search-input" placeholder="<?php _e('Search quiz...'); ?>">
            </div>
            <div class="quizzes-modal-close-wrap">
                <a href="javascript:;" class="quizzes-modal-close-btn">&times;</a>
            </div>
        </div>
        <div class="quizzes-modal-container"></div>
        <div class="quizzes-modal-footer">
            <button type="button" class="button button-primary add_quiz_to_post_btn"><?php _e('Add Quiz', 'tutor'); ?></button>
        </div>
    </div>
</div>