<?php
/**
 * Don't change it, it's supporting modal in other place
 * if get_the_ID() empty, then it's means we are passing $post variable from another place
 */
if (get_the_ID())
	global $post;

?>

<div class="tutor-mb-30">
	<label class="tutor-form-label"><?php _e('Attachment', 'tutor'); ?></label>
	<div class="tutor-input-group tutor-mb-15 tutor-attachments-metabox">
		<?php 
			$attachments = tutor_utils()->get_attachments($post->ID);
			tutor_load_template_from_custom_path(tutor()->path.'/views/fragments/attachments.php', array(
				'name' => 'tutor_attachments[]',
				'attachments' => $attachments
			));
		?>
		<button type="button" class="tutor-btn tutorUploadAttachmentBtn bordered-btn" data-name="tutor_attachments[]">
			<?php _e('Upload Attachment', 'tutor'); ?>
		</button>
	</div>
</div>