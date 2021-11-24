<div class="tutor-attachment-cards tutor-course-builder-attachments <?php echo ( isset( $data['no_control'] ) && $data['no_control'] ) ? 'tutor-no-control' : ''; ?>">
	<?php
	$attachments = $data['attachments'];
	$size_below  = isset( $data['size_below'] ) && $data['size_below'] == true;
	if ( is_array( $attachments ) && count( $attachments ) ) {
		foreach ( $attachments as $attachment ) {
			if ( ! isset( $attachment->id ) ) {
				continue;
			}
			?>
			<div data-attachment_id="<?php echo esc_html( $attachment->id ); ?>">
				<div>
					<a class="filename" href="<?php echo esc_url( $attachment->url ); ?>" target="_blank">
						<?php echo esc_html( $attachment->title ); ?>
					</a>
					<?php if ( $size_below ) : ?>
						<span class="filesize"><?php esc_html_e( 'Size', 'tutor' ); ?>: <?php echo esc_html( $attachment->size ); ?></span>
					<?php endif; ?>
					<input type="hidden" name="<?php echo isset( $data['name'] ) ? $data['name'] : ''; ?>" value="<?php echo esc_attr( $attachment->id ); ?>">
				</div>
				<div>
					<?php if ( ! $size_below ) : ?>
						<span class="filesize"><?php esc_html_e( 'Size', 'tutor' ); ?>: <?php echo esc_html( $attachment->size ); ?></span>
					<?php endif; ?>
					<span class="tutor-delete-attachment tutor-action-icon tutor-icon-line-cross"></span>
				</div>
			</div>
			<?php
		}
	}
	?>
</div>

<?php
if ( isset( $data['add_button'] ) && true === $data['add_button'] ) {
	?>
			<button type="button" class="tutor-btn tutor-btn-tertiary tutor-is-outline tutor-btn-md tutorUploadAttachmentBtn" data-name="<?php echo isset( $data['name'] ) ? esc_attr( $data['name'] ) : ''; ?>">
				<?php esc_html_e( 'Add Attachment', 'tutor' ); ?>
			</button>
		<?php
}
?>
