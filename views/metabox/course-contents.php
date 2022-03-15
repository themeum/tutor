<div class="wp_editor_config_example" style="display: none;">
    <?php wp_editor('', 'tutor_lesson_editor_config'); ?>
</div>

<div class="wp_editor_config_example" style="display: none;">
    <?php wp_editor('', 'tutor_assignment_editor_config'); ?>
</div>

<div class="course-contents">

	<?php

    // tutor_utils()->get_topics function doesn't work correctly for multi instructor case. Rather use get_posts.
    $query_topics = get_posts(array(
        'post_type'      => 'topics',
        'post_parent'    => $course_id,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'posts_per_page' => -1,
    ));


    // Actually all kind of contents. 
    // This keyword '_tutor_course_id_for_lesson' used just to support backward compatibillity
    global $wpdb;
    $unassigned_contents = $wpdb->get_results(
        "SELECT content.* FROM {$wpdb->posts} content 
	        INNER JOIN {$wpdb->postmeta} meta ON content.ID=meta.post_id
        WHERE content.post_parent=0 
            AND meta.meta_key='_tutor_course_id_for_lesson' 
            AND meta.meta_value=".$course_id,
    );

    if(is_array($unassigned_contents) && count($unassigned_contents)) {
        
        $query_topics[]=(object)array(
            'ID' => 0,
            'post_title' => __('Un-assigned Topic Contents', 'tutor'),
            'contents' => $unassigned_contents
        );
    }

	foreach ($query_topics as $topic){
        $is_topic = $topic->ID > 0;
		?>
        <div id="tutor-topics-<?php echo $topic->ID; ?>" class="tutor-topics-wrap" data-topic-id="<?php echo $topic->ID; ?>">
            <div class="tutor-topics-top">
                <div class="tutor-topic-title">
                    <span class="<?php echo $is_topic ? 'tutor-icon-humnurger-filled course-move-handle' : 'tutor-icon-warning-f'; ?> tutor-icon-24"></span>
                    <span class="topic-inner-title tutor-fs-6 tutor-fw-bold tutor-color-black">
                        <?php echo stripslashes($topic->post_title); ?>
                    </span>
                    <?php if($is_topic): ?>
                        <span class="tutor-topic-inline-edit-btn tutor-topic-btn-hover tutor-font-size-24">
                            <i class="tutor-color-muted tutor-icon-edit-filled tutor-icon-24" data-tutor-modal-target="tutor-topics-edit-id-<?php echo $topic->ID; ?>"></i>
                        </span>
                        <span class="topic-delete-btn tutor-topic-btn-hover tutor-font-size-24">
                            <i class="tutor-color-muted tutor-icon-delete-stroke-filled tutor-icon-24"></i>
                        </span>
                    <?php endif; ?>
                    <span class="expand-collapse-wrap tutor-topic-btn-hover tutor-font-size-24">
                        <i class="color-text-brand pops tutor-icon-angle-down-filled tutor-icon-26"></i>
                    </span>
                </div>
                <?php
                    if($is_topic) {
                        tutor_load_template_from_custom_path(tutor()->path.'/views/modal/topic-form.php', array(
                            'modal_title'   => __('Update Topic', 'tutor'),
                            'wrapper_id'    => 'tutor-topics-edit-id-' . $topic->ID,
                            'topic_id'      => $topic->ID,
                            'course_id'     => $course_id,
                            'title'         => $topic->post_title,
                            'summary'       => $topic->post_content,
                            'wrapper_class' => 'tutor-topics-edit-form',
                            'button_text'   => __('Update Topic', 'tutor'),
                            'button_class'  => 'tutor-save-topic-btn'
                        ), false);
                    }
                ?>
            </div>
            <div class="tutor-topics-body" style="display: <?php echo (isset($current_topic_id) && $current_topic_id == $topic->ID) ? 'block' : 'none'; ?>;">
                <div class="tutor-lessons">
                    <?php
                        $post_type = apply_filters( 'tutor_course_contents_post_types', array( tutor()->lesson_post_type, 'tutor_quiz' ) );
                        $course_contents = !$is_topic ? $topic->contents : get_posts(array(
                            'post_type'      => $post_type,
                            'post_parent'    => $topic->ID,
                            'posts_per_page' => -1,
                            'orderby'        => 'menu_order',
                            'order'          => 'ASC',
                        ));

                        $counter = array(
                            'lesson' => 0,
                            'quiz' => 0,
                            'assignment' => 0
                        );
                    
                        foreach ($course_contents as $content){
                    
                            if ($content->post_type === 'tutor_quiz'){
                                $quiz = $content;
                                $counter['quiz']++;
                                tutor_load_template_from_custom_path(tutor()->path.'/views/fragments/quiz-list-single.php', array(
                                    'quiz_id' => $quiz->ID,
                                    'topic_id' => $topic->ID,
                                    'quiz_title' => __('Quiz', 'tutor').' '.$counter['quiz'].': '. $quiz->post_title,
                                ), false);
                    
                            } elseif ($content->post_type === 'tutor_assignments'){
                                $counter['assignment']++;
                                ?>
                                <div data-course_content_id="<?php echo $content->ID; ?>" id="tutor-assignment-<?php echo $content->ID; ?>" class="course-content-item tutor-assignment tutor-assignment-<?php echo $content->ID; ?>">
                                    <div class="tutor-course-content-top">
                                        <span class="tutor-color-muted tutor-icon-humnurger-filled tutor-font-size-24 tutor-pr-2"></span>
                                        <a href="javascript:;" class="<?php echo $is_topic ? 'open-tutor-assignment-modal' : ''; ?>" data-assignment-id="<?php echo $content->ID; ?>" data-topic-id="<?php echo $topic->ID; ?>">
                                            <?php echo __('Assignment', 'tutor').' '.$counter['assignment'].': '. $content->post_title; ?>
                                        </a>
                                        <div class="tutor-course-content-top-right-action">
                                            <?php if($is_topic): ?>
                                                <a href="javascript:;" class="open-tutor-assignment-modal" data-assignment-id="<?php echo $content->ID; ?>" data-topic-id="<?php echo $topic->ID; ?>">
                                                    <span class="tutor-color-muted tutor-icon-edit-filled tutor-font-size-24"></span>
                                                </a>
                                            <?php endif; ?>
                                            <a href="javascript:;" class="tutor-delete-lesson-btn" data-lesson-id="<?php echo $content->ID; ?>">
                                                <span class="tutor-color-muted tutor-icon-delete-stroke-filled tutor-font-size-24"></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            } else if($content->post_type=='lesson') {
                                $counter['lesson']++;
                                ?>
                                <div data-course_content_id="<?php echo $content->ID; ?>" id="tutor-lesson-<?php echo $content->ID; ?>" class="course-content-item tutor-lesson tutor-lesson-<?php echo $content->ID; ?>">
                                    <div class="tutor-course-content-top">
                                        <span class="tutor-color-muted tutor-icon-humnurger-filled tutor-font-size-24 tutor-pr-8"></span>
                                        <a href="javascript:;" class="<?php echo $is_topic ? 'open-tutor-lesson-modal' : ''; ?>" data-lesson-id="<?php echo $content->ID; ?>" data-topic-id="<?php echo $topic->ID; ?>">
                                            <?php echo __('Lesson', 'tutor').' '.$counter['lesson'].': '.stripslashes($content->post_title); ?>
                                        </a>
                                        <div class="tutor-course-content-top-right-action">
                                            <?php if($is_topic): ?>
                                                <a href="javascript:;" class="open-tutor-lesson-modal" data-lesson-id="<?php echo $content->ID; ?>" data-topic-id="<?php echo $topic->ID; ?>">
                                                    <span class="tutor-color-muted tutor-icon-edit-filled tutor-font-size-24"></span>
                                                </a>
                                            <?php endif; ?>
                                            <a href="javascript:;" class="tutor-delete-lesson-btn" data-lesson-id="<?php echo $content->ID; ?>">
                                                <span class="tutor-color-muted tutor-icon-delete-stroke-filled tutor-font-size-24"></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            } else {
                                !isset($counter[$content->post_type]) ? $counter[$content->post_type]=0 : 0;
                                $counter[$content->post_type]++;
                                do_action( 'tutor/course/builder/content/'.$content->post_type, $content, $topic, $course_id, $counter[$content->post_type] );
                            }
                        }
                    ?>
                </div>

                <?php if($is_topic): ?>
                    <div class="tutor_add_content_wrap tutor_add_content_wrap_btn_sm" data-topic_id="<?php echo $topic->ID; ?>">
                        <?php do_action('tutor_course_builder_before_btn_group', $topic->ID); ?>

                        <button class="tutor-btn tutor-is-outline tutor-is-sm open-tutor-lesson-modal create-lesson-in-topic-btn" data-topic-id="<?php echo $topic->ID; ?>" data-lesson-id="0" >
                            <i class="tutor-icon-plus-square-filled tutor-icon-24 tutor-mr-8"></i>
                            <?php _e('Lesson', 'tutor'); ?>
                        </button>

                        <button class="tutor-btn tutor-is-outline tutor-is-sm tutor-add-quiz-btn" data-topic-id="<?php echo $topic->ID; ?>">
                            <i class="tutor-icon-plus-square-filled tutor-icon-24 tutor-mr-8"></i>
                            <?php _e('Quiz', 'tutor'); ?>
                        </button>

                        <?php do_action('tutor_course_builder_after_btn_group', $topic->ID, $course_id); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
		<?php
	}
	?>
	<input type="hidden" id="tutor_topics_lessons_sorting" name="tutor_topics_lessons_sorting" value="" />
</div>