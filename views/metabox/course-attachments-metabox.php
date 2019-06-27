<div class="tutor-lesson-attachments-metabox">

	<div class="tutor-added-attachments-wrap">
		<?php
		$attachments = tutor_utils()->get_attachments();
		if ( is_array($attachments) && count($attachments)) {
			foreach ( $attachments as $attachment ) {
				?>
				<div class="tutor-added-attachment">
					<p><a href="javascript:;" class="tutor-delete-attachment">&times;</a>
						<span>
							<a href="<?php echo $attachment->url; ?>"><?php echo $attachment->name; ?></a>
						</span>
					</p>
					<input type="hidden" name="tutor_attachments[]" value="<?php echo $attachment->id; ?>">
				</div>
			<?php }
		}
		?>
	</div>

	<button type="button" class="tutor-course-builder-button active tutorUploadAttachmentBtn"><?php _e('Upload Attachment', 'tutor'); ?></button>
</div>