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
	<div class="tutor-input-group tutor-mb-15 tutor-lesson-attachments-metabox">
		<div class="tutor-attachment-cards attachment-type-2 tutor-course-builder-attachments is-lesson">
			<?php 
			$attachments = tutor_utils()->get_attachments($post->ID);
			if ( is_array($attachments) && count($attachments)) {
				foreach ( $attachments as $attachment ) {
					?>
					<div data-attachment_id="<?php echo $post->ID; ?>">
						<div>
							<a href="<?php echo $attachment->url; ?>" target="_blank">
								<?php echo $attachment->title; ?>
							</a>
							<input type="hidden" name="tutor_attachments[]" value="<?php echo $attachment->id; ?>">
						</div>
						<div>
							<span class="filesize"><?php _e('Size', 'tutor'); ?>: <?php echo $attachment->size; ?></span>
							<span class="tutor-delete-attachment tutor-icon-line-cross"></span>
						</div>
					</div>
				<?php }
			}
			?>
		</div>

		<button type="button" data-attachment-style="attachment-style-2" class="tutor-btn tutorUploadAttachmentBtn bordered-btn"><?php _e('Upload Attachment', 'tutor'); ?></button>
	</div>
</div>