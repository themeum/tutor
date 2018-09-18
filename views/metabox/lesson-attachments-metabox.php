<div class="lms-lesson-attachments-metabox">

	<div class="lms-added-attachments-wrap">
		<?php
		$attachments = lms_utils()->get_lesson_attachments();
		if ( is_array($attachments) && count($attachments)) {
			foreach ( $attachments as $attachment ) {
				?>
				<div class="lms-added-attachment">
					<p><a href="javascript:;" class="lms-delete-attachment">&times;</a>
						<span>
							<a href="<?php echo $attachment->url; ?>"><?php echo $attachment->name; ?></a>
						</span>
					</p>
					<input type="hidden" name="lms_attachments[]" value="<?php echo $attachment->id; ?>">
				</div>
			<?php }
		}
		?>
	</div>

	<button type="button" class="button button-primary lmsUploadAttachmentBtn"><?php _e('Upload Attachment', 'lms'); ?></button>
</div>