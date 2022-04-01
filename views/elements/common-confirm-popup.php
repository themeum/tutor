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
		<div class="tutor-modal-content">
			<button class="tutor-modal-close-o" data-tutor-modal-close>
				<span class="tutor-icon-times" area-hidden="true"></span>
			</button>
			<div class="tutor-modal-body tutor-text-center tutor-bulk-confirm-modal">
				<form id="tutor-common-confirmation-form">
					<?php tutor_nonce_field(); ?>
					<input type="hidden" name="id">
					<input type="hidden" name="action">
					<div id="tutor-common-confirmation-modal-content"></div>
					<div class="tutor-modal-btns tutor-btn-group">
						<button data-tutor-modal-close class="tutor-btn tutor-btn-outline-primary">
							<?php esc_html_e( 'Cancel', 'tutor' ); ?>
						</button>
						<button id="tutor-confirmation-btn" class="tutor-btn tutor-btn-primary tutor-ml-16">
							<?php esc_html_e( "Yes, I'am Sure", 'tutor' ); ?>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
