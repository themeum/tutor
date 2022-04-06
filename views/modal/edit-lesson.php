<form class="tutor_lesson_modal_form">
	<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
	<input type="hidden" name="action" value="tutor_modal_create_or_update_lesson">
	<input type="hidden" name="lesson_id" value="<?php echo esc_attr( $post->ID ); ?>">
	<input type="hidden" name="current_topic_id" value="<?php echo esc_attr( $topic_id ); ?>">

    <?php do_action('tutor_lesson_edit_modal_form_before', $post); ?>

    <div class="tutor-mb-32">
        <label class="tutor-form-label"><?php _e('Lesson Name', 'tutor'); ?></label>
        <input type="text" name="lesson_title" class="tutor-form-control" value="<?php echo stripslashes($post->post_title); ?>"/>
        <div class="tutor-form-feedback">
            <i class="tutor-icon-circle-info-o tutor-form-feedback-icon"></i>
            <div><?php _e('Lesson titles are displayed publicly wherever required.', 'tutor'); ?></div>
        </div>
    </div>

    <div class="tutor-mb-32">
        <label class="tutor-form-label">
            <?php 
                _e('Lesson Content', 'tutor'); 
                
                if (get_tutor_option('enable_lesson_classic_editor')){
                    ?>
                        <a class="tutor-btn tutor-btn-link tutor-ml-12" target="_blank" href="<?php echo esc_url(get_admin_url()); ?>post.php?post=<?php echo $post->ID; ?>&action=edit" >
                            <i class="tutor-icon-edit tutor-mr-8"></i> <?php echo __('WP Editor', 'tutor'); ?>
                        </a>
                    <?php
                }
            ?>
        </label>

        <?php
            wp_editor(stripslashes($post->post_content), 'tutor_lesson_modal_editor', array( 'editor_height' => 150));
        ?>

        <div class="tutor-form-feedback">
            <i class="tutor-icon-circle-info-o tutor-form-feedback-icon"></i>
            <div><?php _e('The idea of a summary is a short text to prepare students for the activities within the topic or week. The text is shown on the course page under the topic name.', 'tutor'); ?></div>
        </div>
    </div>

    <div class="tutor-mb-32">
        <label class="tutor-form-label"><?php _e('Feature Image', 'tutor'); ?></label>
        <?php 
            $lesson_thumbnail_id = '';
            if (has_post_thumbnail($post->ID)){
                $lesson_thumbnail_id = get_post_meta($post->ID, '_thumbnail_id', true);
            }

            tutor_load_template_from_custom_path(tutor()->path.'/views/fragments/thumbnail-uploader.php', array(
                'media_id' => $lesson_thumbnail_id,
                'input_name' => '_lesson_thumbnail_id'
            ), false);
        ?>
    </div>

    <?php
        include tutor()->path.'views/metabox/video-metabox.php';
        do_action( 'tutor_lesson_edit_modal_after_video' );
        
        include tutor()->path.'views/metabox/lesson-attachments-metabox.php';
        do_action( 'tutor_lesson_edit_modal_after_attachment' );
        
        do_action('tutor_lesson_edit_modal_form_after', $post); 
    ?>
</form>
