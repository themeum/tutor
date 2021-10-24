<div class="course-contents tutor-course-builder-content-container">

    <div class="wp_editor_config_example" style="display: none;">
        <?php wp_editor('', 'tutor_editor_config'); ?>
    </div>

	<?php
	if (empty($current_topic_id)){
		$current_topic_id = (int) tutor_utils()->avalue_dot('current_topic_id', $_POST);
	}

	$query_lesson = tutor_utils()->get_lesson($course_id, -1);
	// $query_topics = tutor_utils()->get_topics($course_id);
	$attached_lesson_ids = array();

    // tutor_utils()->get_topics function doesn't work correctly for multi instructor case. Rather use get_posts.    
    $topic_args = array(
        'post_type'  => 'topics',
        'post_parent'  => $course_id,
        'orderby' => 'menu_order',
        'order'   => 'ASC',
        'posts_per_page'    => -1,
    );
    $query_topics = (object) array('posts' => get_posts($topic_args));

	if ( ! count($query_topics->posts)){
		echo '<p class="course-empty-content">'.__('Add a topic to build your course', 'tutor').'</p>';
	}

	foreach ($query_topics->posts as $topic){
		?>
        <div id="tutor-topics-<?php echo $topic->ID; ?>" class="tutor-topics-wrap" data-topic-id="<?php echo $topic->ID; ?>">
            <div class="tutor-topics-top">
                <h4 class="tutor-topic-title">
                    <i class="fas fa-bars course-move-handle"></i>
                    <span class="topic-inner-title"><?php echo stripslashes($topic->post_title); ?></span>
                    <span class="tutor-topic-inline-edit-btn ">
                        <i class="tutor-icon-pencil" data-tutor-modal-target="tutor-topics-edit-id-<?php echo $topic->ID; ?>"></i>
                    </span>
                    <span class="topic-delete-btn">
                        <i class="tutor-icon-garbage"></i>
                    </span>
                    <span class="expand-collapse-wrap">
                        <i class="tutor-icon-light-down"></i>
                    </span>
                </h4>

                <?php 
                    tutor_load_template_from_custom_path(tutor()->path.'/views/modal/topic-form.php', array(
                        'modal_title'   => __('Update Topic', 'tutor'),
                        'wrapper_id'    => 'tutor-topics-edit-id-' . $topic->ID,
                        'topic_id'      => $topic->ID,
                        'course_id'     => $course_id,
                        'title'         => $topic->post_title,
                        'summary'       => $topic->post_content,
                        'wrapper_class' => 'tutor-topics-edit-form',
                        'button_text'   => __('Update Topic', 'tutor'),
                        'button_class'  => 'tutor-topics-edit-button'
                    ), false); 
                ?>
            </div>
            <div class="tutor-topics-body" style="display: <?php echo $current_topic_id == $topic->ID ? 'block' : 'none'; ?>;">

                <div class="tutor-lessons"><?php
                    // Below function doesn't work somehow because of using WP_Query in ajax call. Will be removed in future.
					// $lessons = tutor_utils()->get_course_contents_by_topic($topic->ID, -1); 
                    
		            $post_type = apply_filters( 'tutor_course_contents_post_types', array( tutor()->lesson_post_type, 'tutor_quiz' ) );
                    $lessons = (object) array('posts' => get_posts(array(
                        'post_type'      => $post_type,
                        'post_parent'    => $topic->ID,
                        'posts_per_page' => -1,
                        'orderby'        => 'menu_order',
                        'order'          => 'ASC',
                    )));

					foreach ($lessons->posts as $lesson){
						$attached_lesson_ids[] = $lesson->ID;

						if ($lesson->post_type === 'tutor_quiz'){
							$quiz = $lesson;
                            tutor_load_template_from_custom_path(tutor()->path.'/views/fragments/quiz-list-single.php', array(
                                'quiz_id' => $quiz->ID,
                                'topic_id' => $topic->ID,
                                'quiz_title' => $quiz->post_title,
                            ), false);

						} elseif ($lesson->post_type === 'tutor_assignments'){
							?>
                            <div id="tutor-assignment-<?php echo $lesson->ID; ?>" class="course-content-item tutor-assignment tutor-assignment-<?php echo $lesson->ID; ?>">
                                <div class="tutor-lesson-top">
                                    <i class="fas fa-bars"></i>
                                    <a href="javascript:;" class="open-tutor-assignment-modal" data-assignment-id="<?php echo $lesson->ID; ?>"
                                       data-topic-id="<?php echo $topic->ID; ?>"><i class="tutor-icon-clipboard"></i> <?php echo
                                        $lesson->post_title; ?> </a>
                                    <a href="javascript:;" class="tutor-delete-lesson-btn" data-lesson-id="<?php echo $lesson->ID; ?>"><i class="tutor-icon-garbage"></i></a>
                                </div>
                            </div>
							<?php
                        } elseif ($lesson->post_type === 'tutor_zoom_meeting'){
							?>
                            <div id="tutor-zoom-meeting-<?php echo $lesson->ID; ?>" class="course-content-item tutor-zoom-meeting-item tutor-zoom-meeting-<?php echo $lesson->ID; ?>">
                                <div class="tutor-lesson-top">
                                    <i class="fas fa-bars"></i>
                                    <a href="javascript:;" class="tutor-zoom-meeting-modal-open-btn" data-meeting-id="<?php echo $lesson->ID; ?>" data-topic-id="<?php echo $topic->ID; ?>" data-click-form="course-builder">
                                        <?php echo stripslashes($lesson->post_title); ?>
                                    </a>
                                    <a href="javascript:;" class="tutor-zoom-meeting-delete-btn" data-meeting-id="<?php echo $lesson->ID; ?>"><i class="tutor-icon-garbage"></i></a>
                                </div>
                            </div>
							<?php
                        } else {
							?>
                            <div id="tutor-lesson-<?php echo $lesson->ID; ?>" class="course-content-item tutor-lesson tutor-lesson-<?php echo
							$lesson->ID; ?>">
                                <div class="tutor-lesson-top">
                                    <i class="fas fa-bars"></i>
                                    <a href="javascript:;" class="open-tutor-lesson-modal" data-lesson-id="<?php echo $lesson->ID; ?>" data-topic-id="<?php echo $topic->ID; ?>"><?php echo stripslashes($lesson->post_title); ?> </a>
                                    <a href="javascript:;" class="tutor-delete-lesson-btn" data-lesson-id="<?php echo $lesson->ID; ?>"><i class="tutor-icon-garbage"></i></a>
                                </div>
                            </div>
							<?php
						}
					}
                ?></div>

                <div class="tutor_add_content_wrap" data-topic_id="<?php echo $topic->ID; ?>">
                    <?php do_action('tutor_course_builder_before_btn_group', $topic->ID); ?>

                    <button class="tutor-btn tutor-is-outline tutor-is-sm open-tutor-lesson-modal create-lesson-in-topic-btn" data-topic-id="<?php echo $topic->ID; ?>" data-lesson-id="0" >
                        <i class="tutor-icon-plus-square-button tutor-mr-10"></i>
                        <?php _e('Lesson', 'tutor'); ?>
                    </button>
                    
                    <button class="tutor-btn tutor-is-outline tutor-is-sm tutor-add-quiz-btn" data-topic-id="<?php echo $topic->ID; ?>">
                        <i class="tutor-icon-plus-square-button tutor-mr-10"></i>
                        <?php _e('Quiz', 'tutor'); ?>
                    </button>

                    <?php do_action('tutor_course_builder_after_btn_group', $topic->ID); ?>
                </div>
            </div>
        </div>
		<?php
	}
	?>
    <input type="hidden" id="tutor_topics_lessons_sorting" name="tutor_topics_lessons_sorting" value="" />
</div>


<?php 
	if ( count( $query_lesson ) > count( $attached_lesson_ids ) ) {
		?>
        <div class="tutor-untopics-lessons tutor-course-builder-content-container">
            <h3><?php _e( 'Un-assigned lessons' ); ?></h3>

            <div class="tutor-lessons "><?php
				foreach ( $query_lesson as $lesson ) {
					if ( ! in_array( $lesson->ID, $attached_lesson_ids ) ) {

						if ($lesson->post_type === 'tutor_quiz'){
							$quiz = $lesson;
                            tutor_load_template_from_custom_path(tutor()->path.'/views/fragments/quiz-list-single.php', array(
                                'quiz_id' => $quiz->ID,
                                'topic_id' => $topic->ID,
                                'quiz_title' => $quiz->post_title,
                            ), false);
						}elseif($lesson->post_type === 'tutor_assignments'){
							?>
                            <div id="tutor-assignment-<?php echo $lesson->ID; ?>" class="course-content-item tutor-assignment tutor-assignment-<?php echo
							$lesson->ID; ?>">
                                <div class="tutor-lesson-top">
                                    <i class="fas fa-bars"></i>
                                    <a href="javascript:;" class="open-tutor-assignment-modal" data-assignment-id="<?php echo $lesson->ID; ?>"
                                       data-topic-id="<?php echo $topic->ID; ?>"><i class="tutor-icon-clipboard"></i> <?php echo
										stripslashes($lesson->post_title); ?> </a>
                                    <a href="javascript:;" class="tutor-delete-lesson-btn" data-lesson-id="<?php echo $lesson->ID; ?>"><i class="tutor-icon-garbage"></i></a>
                                </div>
                            </div>
							<?php
						} else{
							?>
                            <div id="tutor-lesson-<?php echo $lesson->ID; ?>" class="course-content-item tutor-lesson tutor-lesson-<?php echo
							$lesson->ID; ?>">
                                <div class="tutor-lesson-top">
                                    <i class="fas fa-bars"></i>
                                    <a href="javascript:;" class="open-tutor-lesson-modal" data-lesson-id="<?php echo $lesson->ID; ?>" data-topic-id="<?php echo is_object($topic) ? $topic->ID : ''; ?>"><?php echo stripslashes($lesson->post_title); ?> </a>
                                    <a href="javascript:;" class="tutor-delete-lesson-btn" data-lesson-id="<?php echo $lesson->ID; ?>"><i class="tutor-icon-garbage"></i></a>
                                </div>
                            </div>
							<?php
						}

					}
				}
            ?></div>
        </div>
	<?php }
?>