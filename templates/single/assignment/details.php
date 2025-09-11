<?php
/**
 * Single attempt page
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.8.2
 */

$description   = get_the_content();
$has_show_more = strlen( $description ) > 500 ? true : false;

?>

<?php if ( $description ) : ?>
	<div class="tutor-assignment-description-details tutor-assignment-border-bottom tutor-pb-32 tutor-pb-sm-44">
		<div id="content-section" class="tutor-pt-40 tutor-pt-sm-60<?php echo $has_show_more ? ' tutor-toggle-more-content tutor-toggle-more-collapsed' : ''; ?>"<?php echo $has_show_more ? ' data-tutor-toggle-more-content data-toggle-height="300" style="height: 300px;"' : ''; ?>>
			<div class="tutor-fs-6 tutor-fw-medium tutor-color-black">
				<?php esc_html_e( 'Description', 'tutor' ); ?>
			</div>
			<div class="tutor-entry-content tutor-fs-6 tutor-color-secondary tutor-pt-12">
				<?php echo apply_filters( 'the_content', $description ); //phpcs:ignore ?>
			</div>
		</div>
		<?php if ( $has_show_more ) : ?>
			<a href="#" class="tutor-btn-show-more tutor-btn tutor-btn-ghost tutor-mt-32" data-tutor-toggle-more=".tutor-toggle-more-content">
				<span class="tutor-toggle-btn-icon tutor-icon tutor-icon-plus tutor-mr-8" area-hidden="true"></span>
				<span class="tutor-toggle-btn-text"><?php esc_html_e( 'Show More', 'tutor' ); ?></span>
			</a>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php
$assignment_attachments = maybe_unserialize( get_post_meta( get_the_ID(), '_tutor_assignment_attachments', true ) );

if ( tutor_utils()->count( $assignment_attachments ) ) :
	?>
	<div class="tutor-assignment-attachments tutor-pt-40">
		<span class="tutor-fs-6 tutor-fw-medium tutor-color-black">
			<?php esc_html_e( 'Attachments', 'tutor' ); ?>
		</span>
		<div class="tutor-assignment-attachments-list tutor-pt-16">
			<?php if ( is_array( $assignment_attachments ) && count( $assignment_attachments ) ) : ?>
				<?php foreach ( $assignment_attachments as $attachment_id ) : ?>
					<?php
					$attachment_name = get_post_meta( $attachment_id, '_wp_attached_file', true );
					$attachment_name = substr( $attachment_name, strrpos( $attachment_name, '/' ) + 1 );
					$file_size       = tutor_utils()->get_readable_filesize( get_attached_file( $attachment_id ) );
					?>
						<div class="tutor-instructor-card tutor-col-sm-5 tutor-py-16 tutor-mr-12 tutor-ml-3">
							<div class="tutor-icard-content">
								<div class="tutor-fs-6 tutor-color-secondary">
									<a href="<?php echo esc_url( wp_get_attachment_url( $attachment_id ) ); ?>" target="_blank" download>
										<?php echo esc_html( $attachment_name ); ?>
									</a>
								</div>
								<div class="tutor-fs-7">
									<?php esc_html_e( 'Size: ', 'tutor' ); ?>
									<?php echo esc_html( $file_size ); ?>
								</div>
						</div>
						<div class="tutor-d-flex tutor-align-center">
							<a class="tutor-iconic-btn tutor-iconic-btn-outline" href="<?php echo esc_url( wp_get_attachment_url( $attachment_id ) ); ?>" target="_blank">
								<span class="tutor-icon-download" area-hidden="true"></span>
								</a>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>