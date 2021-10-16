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
                <i class="tutor-v2-icon-test icon-info-circle-outline-filled tutor-input-feedback-icon"></i>
                <?php _e('Lesson titles are displayed publicly wherever required.', 'tutor'); ?>
            </p>
        </div>
    </div>

    <div class="tutor-mb-30">
        <label class="tutor-form-label"><?php _e('Lesson Content', 'tutor'); ?></label>
        <div class="tutor-input-group tutor-mb-15">
            <?php
            wp_editor(stripslashes($post->post_content), 'tutor_lesson_modal_editor', array( 'editor_height' => 150));
            ?>
        </div>
    </div>

    <div class="tutor-mb-30">
        <label class="tutor-form-label"><?php _e('Feature Image', 'tutor'); ?></label>
        <div class="tutor-input-group tutor-mb-15">
            <div class="tutor-thumbnail-uploader" data-media-heading="<?php _e('Select or Upload Media Of Your Chosen Persuasion', 'tutor'); ?>" data-button-text="<?php _e( 'Use this media', 'tutor' )?>">
                <div class="thumbnail-wrapper tutor-d-flex tutor-align-items-center tutor-mt-10 tutor-p-15">
                        <div class="thumbnail-preview image-previewer">
                        <span class="preview-loading"></span>
                        <?php 
                            $lesson_thumbnail_id = '';
                            $lesson_thumbnail_url = '';
                            if (has_post_thumbnail($post->ID)){
                                $lesson_thumbnail_id = get_post_meta($post->ID, '_thumbnail_id', true);
                                $lesson_thumbnail_url = get_the_post_thumbnail_url($post->ID);
                            }
                        ?>
                        <input type="hidden" class="tutor-tumbnail-id-input" name="_lesson_thumbnail_id" value="<?php echo $lesson_thumbnail_id; ?>">
                        <img src="<?php echo $lesson_thumbnail_url; ?>" alt="course builder logo"/>
                        <span class="delete-btn" style="<?php echo !$lesson_thumbnail_url ? 'display:none' : ''; ?>"></span>
                    </div>
                    <div class="thumbnail-input">
                        <p class="text-regular-body color-text-subsued">
                            <strong class="text-medium-body">Size: 700x430 pixels;</strong>
                            <br />
                            File Support:
                            <strong class="text-medium-body">
                            jpg, .jpeg,. gif, or .png.
                            </strong>
                        </p>
                        
                        <button class="tutor-btn tutor-is-sm tutor-mt-15 tutor-thumbnail-upload-button">
                            <span class="tutor-btn-icon tutor-v2-icon-test icon-image-filled"></span>
                            <span>Upload Image</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
        include tutor()->path.'views/metabox/video-metabox.php';
        include tutor()->path.'views/metabox/lesson-attachments-metabox.php';
    ?>

    <?php do_action('tutor_lesson_edit_modal_form_after', $post); ?>
</form>