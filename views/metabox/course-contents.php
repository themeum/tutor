<div class="wp_editor_config_example" style="display: none;">
    <?php wp_editor('', 'tutor_lesson_editor_config'); ?>
</div>

<div class="wp_editor_config_example" style="display: none;">
    <?php wp_editor('', 'tutor_assignment_editor_config'); ?>
</div>

<div class="course-contents tutor-course-builder-content-container">

	<?php
	$attached_lesson_ids = array();

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
    /* $query_contents = get_posts(array(
        'post_parent'    => 0,
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'meta_query'     => array(
            array(
                'key'     => '_tutor_course_id_for_lesson',
                'value'   => $course_id,
                'compare' => '=',
            ),
        ),
    )); */

	foreach ($query_topics as $topic){
		?>
        <div id="tutor-topics-<?php echo $topic->ID; ?>" class="tutor-topics-wrap" data-topic-id="<?php echo $topic->ID; ?>">
            <div class="tutor-topics-top">
                <div class="tutor-topic-title">
                    <span class="tutor-icon-humnurger-filled tutor-icon-24 course-move-handle"></span>
                    <span class="topic-inner-title tutor-text-bold-body tutor-color-text-primary"><?php echo stripslashes($topic->post_title); ?></span>
                    <span class="tutor-topic-inline-edit-btn tutor-topic-btn-hover tutor-font-size-24">
                        <i class="color-text-hints tutor-icon-edit-filled tutor-icon-24" data-tutor-modal-target="tutor-topics-edit-id-<?php echo $topic->ID; ?>"></i>
                    </span>
                    <span class="topic-delete-btn tutor-topic-btn-hover tutor-font-size-24">
                        <i class="color-text-hints tutor-icon-delete-stroke-filled tutor-icon-24"></i>
                    </span>
                    <span class="expand-collapse-wrap tutor-topic-btn-hover tutor-font-size-24">
                        <i class="color-text-brand pops tutor-icon-angle-down-filled tutor-icon-26"></i>
                    </span>
                </div>
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
                        'button_class'  => 'tutor-save-topic-btn'
                    ), false);
                ?>
            </div>
            <div class="tutor-topics-body" style="display: <?php echo (isset($current_topic_id) && $current_topic_id == $topic->ID) ? 'block' : 'none'; ?>;">
                <div class="tutor-lessons"><?php
                    // Below function doesn't work somehow because of using WP_Query in ajax call. Will be removed in future.
		            $post_type = apply_filters( 'tutor_course_contents_post_types', array( tutor()->lesson_post_type, 'tutor_quiz' ) );
                    $contents = (object) array('posts' => get_posts(array(
                        'post_type'      => $post_type,
                        'post_parent'    => $topic->ID,
                        'posts_per_page' => -1,
                        'orderby'        => 'menu_order',
                        'order'          => 'ASC',
                    )));

                    $course_contents = $contents->posts;
					require __DIR__ . '/course-content-single.php';
                ?></div>

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
            </div>
        </div>
		<?php
	}
	?>
	<input type="hidden" id="tutor_topics_lessons_sorting" name="tutor_topics_lessons_sorting" value="" />
</div>

<?php /* if ( count( $query_contents ) > count( $attached_lesson_ids ) ): ?>
    <div class="tutor-untopics-lessons tutor-course-builder-content-container">
        <h3><?php _e( 'Un-assigned lessons' ); ?></h3>

        <div class="tutor-lessons ">
            <?php
                $course_contents = $query_contents;
                require __DIR__ . '/course-content-single.php';
            ?>
        </div>
    </div>
<?php endif; */ ?>
