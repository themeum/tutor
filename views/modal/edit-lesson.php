<form class="tutor_lesson_modal_form">
	<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
    <input type="hidden" name="action" value="tutor_modal_create_or_update_lesson">
    <input type="hidden" name="lesson_id" value="<?php echo $post->ID; ?>">
    <input type="hidden" name="current_topic_id" value="<?php echo $topic_id; ?>">

    <div class="lesson-modal-form-wrap">

	    <?php do_action('tutor_lesson_edit_modal_form_before', $post); ?>

        <div class="tutor-mb-30">
            <label class="tutor-form-label"><?php _e('Lesson Name', 'tutor'); ?></label>
            <div class="tutor-input-group tutor-mb-15">
                <input type="text" name="lesson_title" class="tutor-form-control tutor-mb-10" value="<?php echo stripslashes($post->post_title); ?>"/>
                <p class="tutor-input-feedback tutor-has-icon">
                    <i class="far fa-question-circle tutor-input-feedback-icon"></i>
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
                <p class="tutor-input-feedback tutor-has-icon">
                    <i class="far fa-question-circle tutor-input-feedback-icon"></i>
                    <?php _e('The idea of a summary is a short text to prepare students for the activities within the topic or week. The text is shown on the course page under the topic name.', 'tutor'); ?>
                </p>
            </div>
        </div>

        <div class="tutor-mb-30">
            <label class="tutor-form-label"><?php _e('Feature Image', 'tutor'); ?></label>
            <div class="tutor-input-group tutor-mb-15">
                <div class="d-flex logo-upload tutor-thumbnail-wrap">
                    <div class="logo-preview">
                        <span class="preview-loading"></span>
                        <img class="upload_preview" src="" alt="course builder logo">
                        <span class="delete-btn"></span>
                    </div>
                    <div class="logo-upload-wrap">
                        <p>
                            Size: <strong>700x430 pixels;</strong> File Support:
                            <strong>jpg, .jpeg or .png.</strong>
                        </p>
                        <label for="builder-logo-upload" class="tutor-btn tutor-is-sm image_upload_button">
                            <input type="hidden" id="_lesson_thumbnail_id" name="_lesson_thumbnail_id" value="<?php echo $lesson_thumbnail_id; ?>">
                            <span class="tutor-btn-icon tutor-v2-icon-test icon-image-filled"></span>
                            <span class="lesson_thumbnail_upload_btn">Upload Image</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

		<?php
		include tutor()->path.'views/metabox/video-metabox.php';
		include tutor()->path.'views/metabox/lesson-attachments-metabox.php';
		?>

        <?php do_action('tutor_lesson_edit_modal_form_after', $post); ?>

    </div>
</form>