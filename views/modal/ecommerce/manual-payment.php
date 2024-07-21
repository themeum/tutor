<?php
/**
 * Add manual payment modal
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

?>
<form action="" id="tutor-manual-payment-form" method="post">
	<div class="tutor-modal tutor-modal-scrollable" id="tutor-add-manual-payment-modal">
		<div class="tutor-modal-overlay"></div>
		<div class="tutor-modal-window">
			<div class="tutor-modal-content">
				<div class="tutor-modal-header">
					<div class="tutor-modal-title">
						<?php esc_html_e( 'Set up manual payment method', 'tutor' ); ?>
					</div>
					<button class="tutor-modal-close tutor-iconic-btn" data-tutor-modal-close="" role="button">
						<span class="tutor-icon-times" area-hidden="true"></span>
					</button>
				</div>

				<div class="tutor-modal-body">
					<?php tutor_nonce_field(); ?>
					<input type="hidden" name="action" value="tutor_add_manual_payment_method">
					<input type="hidden" name="is_enable" value="off">

					<div class="tutor-rows">
						<div class="tutor-col">
							<label class="tutor-form-label">
								<?php esc_html_e( 'Custom payment method name', 'tutor' ); ?>
							</label>
							<div class="tutor-mb-16">
								<input type="text" name="payment_method_name" class="tutor-form-control tutor-mb-12" required/>
							</div>
						</div>
						<div class="tutor-col">
							<label class="tutor-form-label">
								<?php esc_html_e( 'Additional details', 'tutor' ); ?>
							</label>
							<div class="tutor-mb-16">
								<textarea name="additional_details" class="tutor-form-control tutor-mb-12" rows="4"></textarea>
								<div class="tutor-color-muted"><?php esc_html_e( 'Displays to customers when they’re choosing a payment method.', 'tutor' ); ?></div>
							</div>
						</div>
						<div class="tutor-col">
							<label class="tutor-form-label">
								<?php esc_html_e( 'Payment instructions ', 'tutor' ); ?>
							</label>
							<div>
								<textarea name="payment_instructions" class="tutor-form-control tutor-mb-12" rows="5"></textarea>
								<div class="tutor-color-muted"><?php esc_html_e( 'Displays to customers when they’re choosing a payment method.', 'tutor' ); ?></div>
							</div>
						</div>
					</div>
				</div>

				<div class="tutor-modal-footer">
					<button class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
						<?php esc_html_e( 'Cancel', 'tutor' ); ?>
					</button>

					<button type="submit" class="tutor-btn tutor-btn-primary" id="tutor-manual-payment-button">
						<?php esc_html_e( 'Add method', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</form>
<form action="" id="tutor-update-manual-payment-form" method="post">
	<div class="tutor-modal tutor-modal-scrollable" id="tutor-update-manual-payment-modal">
		<div class="tutor-modal-overlay"></div>
		<div class="tutor-modal-window">
			<div class="tutor-modal-content">
				<div class="tutor-modal-header">
					<div class="tutor-modal-title">
						<?php esc_html_e( 'Update manual payment method', 'tutor' ); ?>
					</div>
					<button class="tutor-modal-close tutor-iconic-btn" data-tutor-modal-close="" role="button">
						<span class="tutor-icon-times" area-hidden="true"></span>
					</button>
				</div>

				<div class="tutor-modal-body">
					<?php tutor_nonce_field(); ?>
					<input type="hidden" name="action" value="tutor_add_manual_payment_method">
					<input type="hidden" name="is_enable" value="">
					<input type="hidden" name="payment_method_id" value="">

					<div class="tutor-rows">
						<div class="tutor-col">
							<label class="tutor-form-label">
								<?php esc_html_e( 'Custom payment method name', 'tutor' ); ?>
							</label>
							<div class="tutor-mb-16">
								<input type="text" name="payment_method_name" class="tutor-form-control tutor-mb-12" required/>
							</div>
						</div>
						<div class="tutor-col">
							<label class="tutor-form-label">
								<?php esc_html_e( 'Additional details', 'tutor' ); ?>
							</label>
							<div class="tutor-mb-16">
								<textarea name="additional_details" class="tutor-form-control tutor-mb-12" rows="4"></textarea>
								<div class="tutor-color-muted"><?php esc_html_e( 'Displays to customers when they’re choosing a payment method.', 'tutor' ); ?></div>
							</div>
						</div>
						<div class="tutor-col">
							<label class="tutor-form-label">
								<?php esc_html_e( 'Payment instructions ', 'tutor' ); ?>
							</label>
							<div>
								<textarea name="payment_instructions" class="tutor-form-control tutor-mb-12" rows="5"></textarea>
								<div class="tutor-color-muted"><?php esc_html_e( 'Displays to customers when they’re choosing a payment method.', 'tutor' ); ?></div>
							</div>
						</div>
					</div>
				</div>

				<div class="tutor-modal-footer">
					<button class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
						<?php esc_html_e( 'Cancel', 'tutor' ); ?>
					</button>

					<button type="submit" class="tutor-btn tutor-btn-primary">
						<?php esc_html_e( 'Update', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</form>
