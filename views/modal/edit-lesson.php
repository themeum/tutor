<form class="tutor_lesson_modal_form">
	<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
    <input type="hidden" name="action" value="tutor_modal_create_or_update_lesson">
    <input type="hidden" name="lesson_id" value="<?php echo $post->ID; ?>">
    <input type="hidden" name="current_topic_id" value="<?php echo $topic_id; ?>">
    
    <?php do_action('tutor_lesson_edit_modal_form_before', $post); ?>

    <div class="tutor-mb-30">
        <label class="tutor-form-label"><?php _e('Lesson Name', 'tutor'); ?></label>
        <div class="tutor-input-group tutor-mb-15">
            <input type="text" name="lesson_title" class="tutor-form-control tutor-mb-10" value="<?php echo stripslashes($post->post_title); ?>"/>
            <p class="tutor-input-feedback tutor-has-icon">
                <i class="ttr-info-circle-outline-filled tutor-input-feedback-icon"></i>
                <?php _e('Lesson titles are displayed publicly wherever required.', 'tutor'); ?>
            </p>
        </div>
    </div>

    <div class="tutor-mb-30">
        <label class="tutor-form-label">
            <?php 
                _e('Lesson Content', 'tutor'); 
                
                if (get_tutor_option('enable_lesson_classic_editor')){
                    ?>
                        <a class="tutor-ml-10" target="_blank" href="<?php echo esc_url(get_admin_url()); ?>post.php?post=<?php echo $post->ID; ?>&action=edit" >
                            <i class="tutor-icon-classic-editor"></i> <?php echo __('WP Editor', 'tutor'); ?>
                        </a>
                    <?php
                }
            ?>
        </label>
        <div class="tutor-input-group tutor-mb-15">
            <?php
            wp_editor(stripslashes($post->post_content), 'tutor_lesson_modal_editor', array( 'editor_height' => 150));
            ?>
        </div>
    </div>

    <div class="tutor-mb-30">
        <label class="tutor-form-label"><?php _e('Feature Image', 'tutor'); ?></label>
        <div class="tutor-input-group tutor-mb-15">
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
    </div>

    <?php
        include tutor()->path.'views/metabox/video-metabox.php';
        do_action( 'tutor_lesson_edit_modal_after_video' );
        
        include tutor()->path.'views/metabox/lesson-attachments-metabox.php';
        do_action( 'tutor_lesson_edit_modal_after_attachment' );
    ?>

    <?php do_action('tutor_lesson_edit_modal_form_after', $post); ?>
</form>