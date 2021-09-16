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
            </div>
        </div>

        <div class="tutor-mb-30">
            <label class="tutor-form-label"><?php _e('Feature Image', 'tutor'); ?></label>
            <div class="tutor-input-group tutor-mb-15">
                <div class="tutor-thumbnail-wrap ">
                    <p class="thumbnail-img tutor-lesson-edit-feature-img">
                        <?php
                        $thumbnail_upload_text = __('Upload Feature Image', 'tutor');
                        $lesson_thumbnail_id = '';
                        if (has_post_thumbnail($post->ID)){
                            $lesson_thumbnail_id = get_post_meta($post->ID, '_thumbnail_id', true);
                            echo get_the_post_thumbnail($post->ID);
                            $thumbnail_upload_text = __('Update Feature Image', 'tutor');
                        }
                        ?>
                        <a href="javascript:;" class="tutor-lesson-thumbnail-delete-btn" style="display: <?php echo $lesson_thumbnail_id ? 'block':'none'; ?>;"><i class="tutor-icon-line-cross"></i></a>
                    </p>

                    <input type="hidden" class="_lesson_thumbnail_id" name="_lesson_thumbnail_id" value="<?php echo $lesson_thumbnail_id; ?>">
                    <button type="button" class="lesson_thumbnail_upload_btn tutor-btn bordered-btn"><?php echo $thumbnail_upload_text; ?></button>
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