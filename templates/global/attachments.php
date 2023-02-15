<?php
/**
 * Display attachments
 *
 * @package Tutor\Templates
 * @subpackage Global
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attachments    = tutor_utils()->get_attachments();
$open_mode_view = apply_filters( 'tutor_pro_attachment_open_mode', null ) == 'view' ? ' target="_blank" ' : null;

do_action( 'tutor_global/before/attachments' );

if ( is_array( $attachments ) && count( $attachments ) ) : ?>
	<div class="tutor-course-attachments tutor-row">
		<?php foreach ( $attachments as $attachment ) : ?>
			<div class="tutor-col-md-6 tutor-mt-16">
				<div class="tutor-course-attachment tutor-card tutor-card-sm">
					<div class="tutor-card-body">
						<div class="tutor-row">
							<div class="tutor-col tutor-overflow-hidden">
								<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-text-ellipsis tutor-mb-4"><?php echo esc_html( $attachment->name ); ?></div>
								<div class="tutor-fs-7 tutor-color-muted"><?php esc_html_e( 'Size', 'tutor' ); ?>: <?php echo esc_html( $attachment->size ); ?></div>
							</div>

							<div class="tutor-col-auto">
								<a href="<?php echo esc_url( $attachment->url ); ?>" class="tutor-iconic-btn tutor-iconic-btn-secondary tutor-stretched-link" <?php echo esc_attr( $open_mode_view ? $open_mode_view : "download={$attachment->name}" ); ?>>
									<span class="tutor-icon-download" area-hidden="true"></span>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php else :
	tutor_utils()->tutor_empty_state( __( 'No Attachment Found', 'tutor' ) );
endif;

do_action( 'tutor_global/after/attachments' ); ?>
