<div class="course-contents">

	<?php
    $course_id = get_the_ID();
	$query_lesson = dozent_utils()->get_lesson($course_id);
	$query_topics = dozent_utils()->get_topics($course_id);
	$attached_lesson_ids = array();

	foreach ($query_topics->posts as $topic){
		?>
        <div id="dozent-topics-<?php echo $topic->ID; ?>" class="dozent-topics-wrap">

            <div class="dozent-topics-top">
                <h3>
                    <i class="dashicons dashicons-move course-move-handle"></i>
                    <span class="topic-inner-title"><?php echo $topic->post_title; ?></span>
                    <i class="dashicons dashicons-edit topic-edit-icon"></i>

                    <span class="topic-delete-btn">
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?action=dozent_delete_topic&topic_id='.$topic->ID), dozent()->nonce_action, dozent()->nonce); ?>" title="<?php _e('Delete Topic',
                            'dozent');
                        ?>">
                            <i class="dashicons dashicons-trash"></i>
                        </a>
                    </span>
                </h3>

                <div class="dozent-topics-edit-form" style="display: none;">
                    <div class="dozent-option-field-row">
                        <div class="dozent-option-field-label">
                            <label for=""><?php _e('Topic Name', 'dozent'); ?></label>
                        </div>
                        <div class="dozent-option-field">
                            <input type="text" name="topic_title" value="<?php echo $topic->post_title; ?>">

                            <p class="desc">
				                <?php _e('Topic title will be publicly show where required, you can call it as a section also in course', 'dozent'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="dozent-option-field-row">
                        <div class="dozent-option-field-label">
                            <label for=""><?php _e('Topic Summery', 'dozent'); ?></label>
                        </div>
                        <div class="dozent-option-field">
                            <textarea name="topic_summery"><?php echo $topic->post_content; ?></textarea>
                            <p class="desc">
				                <?php _e('The idea of a summary is a short text to prepare students for the activities within the topic or week. The text is shown on the course page under the topic name.', 'dozent'); ?>
                            </p>

                            <button type="button" class="button button-primary dozent-topics-edit-button"><i class="dashicons dashicons-edit"></i> <?php _e('Edit Topic', 'dozent');
                            ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dozent-lessions">
				<?php
				$lessons = dozent_utils()->get_lessons_by_topic($topic->ID);
				?>
                <div class="drop-lessons" style="display: <?php echo count($lessons->posts) ? 'none' : 'block'; ?>;">
                    <p><i class="dashicons dashicons-upload"></i> <?php _e('Drop lesson here', 'dozent'); ?></p>
                </div>
                <?php
				//print_r($lessons);
				foreach ($lessons->posts as $lesson){
					$attached_lesson_ids[] = $lesson->ID;
					?>
                    <div id="dozent-lesson-<?php echo $lesson->ID; ?>" class=" dozent-lesson dozent-lesson-<?php echo $lesson->ID; ?>">
                        <div class="dozent-lesson-top">
                            <i class="dashicons dashicons-move"></i>
                            <a href="<?php echo admin_url("post.php?post={$lesson->ID}&action=edit"); ?>"> <i class="dashicons dashicons-list-view"></i> <?php echo $lesson->post_title; ?> </a>
                        </div>
                    </div>
					<?php
				}
				?>
            </div>

            <div class="dozent_add_quiz_wrap" data-add-quiz-under="<?php echo $topic->ID; ?>">
                <div class="dozent-available-quizzes">
                    <?php
                    $attached_quizzes = dozent_utils()->get_attached_quiz($topic->ID);
                    if ($attached_quizzes){
                        foreach ($attached_quizzes as $attached_quiz){
                            ?>
                            <div id="added-quiz-id-<?php echo $attached_quiz->ID; ?>" class="added-quiz-item added-quiz-item-<?php echo $attached_quiz->ID; ?>" data-quiz-id="<?php echo $attached_quiz->ID; ?>">
                                <span class="quiz-icon"><i class="dashicons dashicons-clock"></i></span>
                                <span class="quiz-name">
                                    <?php edit_post_link( $attached_quiz->post_title, null, null, $attached_quiz->ID ); ?>
                                </span>
                                <span class="quiz-control">
                                    <a href="javascript:;" class="dozent-quiz-delete-btn"><i class="dashicons dashicons-trash"></i></a>
                                </span>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>

                <div class="dozent-add-quiz-button-wrap">
                    <button type="button" class="button button-default dozent-add-quiz-btn"> <?php _e('Add Topic Quiz', 'dozent'); ?> </button>
                </div>
            </div>

        </div>
		<?php
	}
	?>

    <input type="hidden" id="dozent_topics_lessons_sorting" name="dozent_topics_lessons_sorting" value="" />
</div>

<div class="dozent-untopics-lessons">
    <h1><?php _e('Un Topics Lessons'); ?></h1>

    <div class="dozent-lessions">
        <div class="drop-lessons" style="display: <?php echo count($query_lesson->posts) == count($attached_lesson_ids) ? 'block' : 'none'; ?>;">
            <p><i class="dashicons dashicons-upload"></i> <?php _e('Drop un topics lesson here', 'dozent'); ?></p>
        </div>

		<?php
		foreach ($query_lesson->posts as $lesson){
		    if ( ! in_array($lesson->ID, $attached_lesson_ids)) {
			    ?>
                <div id="dozent-lesson-<?php echo $lesson->ID; ?>" class="dozent-lesson dozent-lesson-<?php echo $lesson->ID; ?>">
                    <div class="dozent-lesson-top">
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


<div class="dozent-metabox-add-topics">
    <h3><?php _e('Add Topic', 'dozent'); ?></h3>

    <div class="dozent-option-field-row">
        <div class="dozent-option-field-label">
            <label for=""><?php _e('Topic Name', 'dozent'); ?></label>
        </div>
        <div class="dozent-option-field">
            <input type="text" name="topic_title" value="">

            <p class="desc">
		        <?php _e('Topic titles will be publicly show where required, you can call it as a section also in course', 'dozent'); ?>
            </p>
        </div>
    </div>

    <div class="dozent-option-field-row">
        <div class="dozent-option-field-label">
            <label for=""><?php _e('Topic Summery', 'dozent'); ?></label>
        </div>
        <div class="dozent-option-field">
            <textarea name="topic_summery"></textarea>
            <p class="desc">
				<?php _e('The idea of a summary is a short text to prepare students for the activities within the topic or week. The text is shown on the course page under the topic name.', 'dozent'); ?>
            </p>
	        <?php
            submit_button(__('Add Topic', 'dozent')); ?>
        </div>
    </div>
</div>



<div class="dozent-modal-wrap dozent-quiz-modal-wrap">
    <div class="dozent-modal-content">
        <div class="modal-header">
            <div class="search-bar">
                <input type="text" class="dozent-modal-search-input" placeholder="<?php _e('Search quiz...'); ?>">
            </div>
            <div class="modal-close-wrap">
                <a href="javascript:;" class="modal-close-btn">&times;</a>
            </div>
        </div>
        <div class="modal-container"></div>
        <div class="modal-footer">
            <button type="button" class="button button-primary add_quiz_to_post_btn"><?php _e('Add Quiz', 'dozent'); ?></button>
        </div>
    </div>
</div>