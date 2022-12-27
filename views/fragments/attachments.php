<?php
/**
 * Tutor Attachment Meta box
 *
 * @package Tutor\Views
 * @subpackage Tutor\Fragments
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$attachments = $data['attachments'];
$size_below  = isset( $data['size_below'] ) && true == $data['size_below'];
?>

<div class="tutor-attachment-cards tutor-row tutor-attachment-size-<?php echo $size_below ? 'below' : 'aside'; ?> tutor-course-builder-attachments <?php echo ( isset( $data['no_control'] ) && $data['no_control'] ) ? 'tutor-no-control' : ''; ?>">
	<?php if ( is_array( $attachments ) && count( $attachments ) ) : ?>
		<?php foreach ( $attachments as $attachment ) : ?>
			<?php
			if ( ! is_object( $attachment ) || ! property_exists( $attachment, 'id' ) ) {
				continue; }
			?>
			<div class="tutor-col-lg-6 tutor-col-xl-4 tutor-mb-16" data-attachment_id="<?php echo esc_attr( $attachment->id ); ?>">
				<div class="tutor-card">
					<div class="tutor-card-body">
						<div class="tutor-row tutor-align-center">
							<div class="tutor-col tutor-overflow-hidden">
								<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-text-ellipsis tutor-mb-4"><?php echo esc_html( $attachment->title ); ?></div>
								<div class="tutor-fs-7 tutor-color-muted">
									<?php esc_html_e( 'Size', 'tutor' ); ?>: <?php echo esc_html( $attachment->size ); ?>
								</div>
								<input type="hidden" name="<?php echo esc_attr( isset( $data['name'] ) ? $data['name'] : '' ); ?>" value="<?php echo esc_attr( $attachment->id ); ?>">
							</div>

							<div class="tutor-col-auto">
								<span class="tutor-delete-attachment tutor-iconic-btn tutor-iconic-btn-secondary" role="button">
									<span class="tutor-icon-times" area-hidden="true"></span>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<?php if ( isset( $data['add_button'] ) && true === $data['add_button'] ) : ?>
	<button type="button" class="tutor-btn tutor-btn-outline-primary tutorUploadAttachmentBtn" data-name="<?php echo isset( $data['name'] ) ? esc_attr( $data['name'] ) : ''; ?>">
		<span class="tutor-icon-paperclip tutor-mr-8"></span>
		<span><?php esc_html_e( 'Upload Attachments', 'tutor' ); ?></span>
	</button>
<?php endif; ?>
