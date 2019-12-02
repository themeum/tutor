<div class="course-contents">
	<?php
	if (empty($current_topic_id)){
		$current_topic_id = (int) tutor_utils()->avalue_dot('current_topic_id', $_POST);
	}

	$query_lesson = tutor_utils()->get_lesson($course_id, -1);
	$query_topics = tutor_utils()->get_topics($course_id);
	$attached_lesson_ids = array();

	if ( ! count($query_topics->posts)){
		echo '<p class="course-empty-content">'.__('Add a topics to build this course', 'tutor').'</p>';
	}

	foreach ($query_topics->posts as $topic){
		?>
        <div id="tutor-topics-<?php echo $topic->ID; ?>" class="tutor-topics-wrap">

            <div class="tutor-topics-top">
                <h4 class="tutor-topic-title">
                    <i class="tutor-icon-move course-move-handle"></i>
                    <span class="topic-inner-title"><?php echo $topic->post_title; ?></span>

                    <span class="tutor-topic-inline-edit-btn">
                        <i class="tutor-icon-pencil topic-edit-icon"></i>
                    </span>
                    <span class="topic-delete-btn">
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?action=tutor_delete_topic&topic_id='.$topic->ID), tutor()->nonce_action, tutor()->nonce); ?>" title="<?php _e('Delete Topic', 'tutor'); ?>" data-topic-id="<?php echo $topic->ID; ?>">
                            <i class="tutor-icon-garbage"></i>
                        </a>
                    </span>

                    <span class="expand-collapse-wrap">
                        <a href="javascript:;"><i class="tutor-icon-light-down"></i> </a>
                    </span>
                </h4>

                <div class="tutor-topics-edit-form" style="display: none;">
                    <div class="tutor-option-field-row">
                        <div class="tutor-option-field-label">
                            <label for=""><?php _e('Topic Name', 'tutor'); ?></label>
                        </div>
                        <div class="tutor-option-field">
                            <input type="text" name="topic_title" class="course-edit-topic-title-input" value="<?php echo $topic->post_title; ?>">

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

                            <button type="button" class="button button-primary tutor-topics-edit-button"><i class="tutor-icon-pencil"></i> <?php _e('Edit Topic', 'tutor'); ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tutor-topics-body" style="display: <?php echo $current_topic_id == $topic->ID ? 'block' : 'none'; ?>;">

                <div class="tutor-lessons">
					<?php
					$lessons = tutor_utils()->get_lessons_by_topic($topic->ID, -1);
					foreach ($lessons->posts as $lesson){
						$attached_lesson_ids[] = $lesson->ID;
						?>
                        <div id="tutor-lesson-<?php echo $lesson->ID; ?>" class=" tutor-lesson tutor-lesson-<?php echo $lesson->ID; ?>">
                            <div class="tutor-lesson-top">
                                <i class="tutor-icon-move"></i>
                                <a href="javascript:;" class="open-tutor-lesson-modal" data-lesson-id="<?php echo $lesson->ID; ?>" data-topic-id="<?php echo $topic->ID; ?>"><?php echo $lesson->post_title; ?> </a>

                                <a href="<?php echo admin_url("post.php?post={$lesson->ID}&action=edit"); ?>"><i class="tutor-icon-pencil"></i> </a>


                                <a href="javascript:;" class="tutor-delete-lesson-btn" data-lesson-id="<?php echo $lesson->ID; ?>"><i class="tutor-icon-garbage"></i></a>

                            </div>
                        </div>
						<?php
					}
					?>


					<?php
					/*
					if (count($query_lesson->posts) > count($attached_lesson_ids)){


						?>
						<div class="drop-lessons">
							<p>
								<i class="dashicons dashicons-upload"></i>
								<?php echo __('Drop lesson here or', 'tutor'); ?>

								<a href="javascript:;" class="create-lesson-in-topic-btn open-tutor-lesson-modal" data-topic-id="<?php echo $topic->ID; ?>" data-lesson-id="0" ><?php _e('Create one'); ?></a>
							</p>
						</div>
					<?php }else{
						?>
						<div class="create-new-lesson-wrap">
							<a href="javascript:;" class="create-lesson-in-topic-btn open-tutor-lesson-modal" data-topic-id="<?php echo $topic->ID; ?>" data-lesson-id="0" ><?php _e('Create new lesson', 'tutor'); ?></a>
						</div>
						<?php
					}
					   */ ?>

                    <!--
                    <div class="tutor-create-new-lesson-form" style="display: none;" data-topic-id="<?php /*echo $topic->ID; */?>">
                        <div class="tutor-option-field-row">
                            <div class="tutor-option-field">
                                <input type="text" name="lesson_title" value="" placeholder="<?php /*_e('Lesson title', 'tutor'); */?>">

                                <button type="button" class="button button-primary tutor-create-lesson-btn"> <?php /*_e('Create Lesson', 'tutor'); */?></button>
                            </div>
                        </div>
                    </div>
                    -->

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
                                    <a href="javascript:;" class="tutor-quiz-delete-btn"><i class="tutor-icon-garbage"></i></a>
                                </span>
                                </div>
								<?php
							}
						}
						?>
                    </div>

                    <div class="tutor-add-quiz-button-wrap">
                        <a href="javascript:;" class="create-lesson-in-topic-btn open-tutor-lesson-modal" data-topic-id="<?php echo $topic->ID; ?>"
                           data-lesson-id="0" ><?php _e('Add new lesson', 'tutor'); ?></a>

                        <!--<button type="button" class="tutor-add-quiz-btn"> <?php /*_e('Add Topic Quiz', 'tutor'); */?> </button>-->
                    </div>

                </div>

            </div>


        </div>
		<?php
	}
	?>
    <input type="hidden" id="tutor_topics_lessons_sorting" name="tutor_topics_lessons_sorting" value="" />
</div>


<?php if (count($query_lesson->posts)) {
	if ( count( $query_lesson->posts ) > count( $attached_lesson_ids ) ) {
		?>
        <div class="tutor-untopics-lessons">
            <h3><?php _e( 'Un-assigned lessons' ); ?></h3>

            <div class="tutor-lessons">
                <!--<div class="drop-lessons" >
                <p><i class="dashicons dashicons-upload"></i> <?php /*_e('Drop any unassigned lesson here', 'tutor'); */
				?></p>
                </div>-->
				<?php
				foreach ( $query_lesson->posts as $lesson ) {
					if ( ! in_array( $lesson->ID, $attached_lesson_ids ) ) {
						?>
                        <div id="tutor-lesson-<?php echo $lesson->ID; ?>" class="tutor-lesson tutor-lesson-<?php echo $lesson->ID; ?>">


                            <div class="tutor-lesson-top">
                                <i class="tutor-icon-move"></i>
                                <a href="javascript:;" class="open-tutor-lesson-modal" data-lesson-id="<?php echo $lesson->ID; ?>"><?php echo $lesson->post_title; ?> </a>

                                <a href="<?php echo admin_url("post.php?post={$lesson->ID}&action=edit"); ?>"><i class="tutor-icon-pencil"></i> </a>

                                <a href="javascript:;" class="tutor-delete-lesson-btn" data-lesson-id="<?php echo $lesson->ID; ?>"><i class="tutor-icon-garbage"></i></a>

                            </div>

                        </div>
						<?php
					}
				}
				?>
            </div>
        </div>
	<?php }
}
?>
