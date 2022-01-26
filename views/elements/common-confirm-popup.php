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
	<span class="tutor-modal-overlay"></span>
	<button data-tutor-modal-close class="tutor-modal-close">
		<span class="tutor-icon-line-cross-line"></span>
	</button>
	<div class="tutor-modal-root">
		<div class="tutor-modal-inner">
			<div class="tutor-modal-body tutor-text-center tutor-bulk-confirm-modal">
				<form id="tutor-common-confirmation-form">
					<?php tutor_nonce_field(); ?>
					<input type="hidden" name="id">
					<input type="hidden" name="action">
					<!-- body content will be generate from js -->
					<div id="tutor-common-confirmation-modal-content"></div>
					<div class="tutor-modal-btns tutor-btn-group">
						<button data-tutor-modal-close class="tutor-btn tutor-is-outline tutor-is-default">
							<?php esc_html_e( 'Cancel', 'tutor' ); ?>
						</button>
						<button class="tutor-btn tutor-btn-wordpress tutor-no-hover tutor-btn-loading tutor-btn-lg">
							<?php esc_html_e( "Yes, I'am Sure", 'tutor' ); ?>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
