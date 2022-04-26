<?php
/**
 * Tutor confirm modal
 * a common modal for confirmation
 *
 * @package Tutor confirm modal
 * @since v2.0.0
 */
?>
<div class="tutor-modal" id="tutor-common-confirmation-modal">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<div class="tutor-modal-content tutor-modal-content-white">
			<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
				<span class="tutor-icon-times" area-hidden="true"></span>
			</button>

			<div class="tutor-modal-body tutor-text-center">
				<div class="tutor-mt-48">
					<img class="tutor-d-inline-block" src="<?php echo tutor()->url; ?>assets/images/icon-trash.svg" />
				</div>

				<div class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mb-12"><?php esc_html_e('Do You Want to Delete This?', 'tutor'); ?></div>
				<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e('Are you sure you want to delete this permanently from the site? Please confirm your choice.', 'tutor'); ?></div>

				<form id="tutor-common-confirmation-form" class="tutor-m-0" method="POST">
					<?php tutor_nonce_field(); ?>
					<input type="hidden" name="id">
					<input type="hidden" name="action">
					<div class="tutor-d-flex tutor-justify-center tutor-my-48">
						<button class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
							<?php esc_html_e( 'Cancel', 'tutor' ); ?>
						</button>
						<button type="submit" class="tutor-btn tutor-btn-primary tutor-ml-16" data-tutor-modal-submit>
							<?php esc_html_e( "Yes, I'am Sure", 'tutor' ); ?>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
