<div class="dozent-lesson-attachments-metabox">

	<div class="dozent-added-attachments-wrap">
		<?php
		$attachments = dozent_utils()->get_attachments();
		if ( is_array($attachments) && count($attachments)) {
			foreach ( $attachments as $attachment ) {
				?>
				<div class="dozent-added-attachment">
					<p><a href="javascript:;" class="dozent-delete-attachment">&times;</a>
						<span>
							<a href="<?php echo $attachment->url; ?>"><?php echo $attachment->name; ?></a>
						</span>
					</p>
					<input type="hidden" name="dozent_attachments[]" value="<?php echo $attachment->id; ?>">
				</div>
			<?php }
		}
		?>
	</div>

	<button type="button" class="button button-primary dozentUploadAttachmentBtn"><?php _e('Upload Attachment', 'dozent'); ?></button>
</div>