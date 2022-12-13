
<?php
/**
 * Common bulk confirmation modal
 *
 * @package Tutor\Views
 * @subpackage Tutor\ViewElements
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>
<div class="tutor-modal tutor-bulk-modal-disabled" id="tutor-bulk-confirm-popup">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<div class="tutor-modal-content tutor-modal-content-white">
			<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
				<span class="tutor-icon-times" area-hidden="true"></span>
			</button>

			<div class="tutor-modal-body tutor-text-center">
				<div class="tutor-my-44">
					<div class="tutor-fs-4 tutor-fw-medium tutor-color-black tutor-mb-12"><?php esc_html_e( 'Before You Proceed!', 'tutor' ); ?></div>
					<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Are you sure you would like to perform this action? We suggest you proceed with caution.', 'tutor' ); ?></div>

					<form id="tutor-common-confirmation-form-2" class="tutor-mt-40 tutor-mb-0" method="POST">
						<?php tutor_nonce_field(); ?>
						<input type="hidden" name="id">
						<input type="hidden" name="action">
						<div class="tutor-d-flex tutor-justify-center">
							<button class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
								<?php esc_html_e( 'Cancel', 'tutor' ); ?>
							</button>
							<button id="tutor-confirm-bulk-action" class="tutor-btn tutor-btn-primary tutor-ml-16" data-tutor-modal-submit>
								<?php esc_html_e( "Yes, I'am Sure", 'tutor' ); ?>
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
