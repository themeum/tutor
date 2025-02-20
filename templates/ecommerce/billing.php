<?php
/**
 * Billing Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

?>

<div class="tutor-fs-4 tutor-fw-medium tutor-mb-24"><?php esc_html_e( 'Settings', 'tutor' ); ?></div>

<div class="tutor-dashboard-content-inner tutor-dashboard-setting-billing">
	<div class="tutor-mb-32">
		<?php tutor_load_template( 'dashboard.settings.nav-bar', array( 'active_setting_nav' => 'billing' ) ); ?>
		<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mt-32"><?php esc_html_e( 'Billing Address', 'tutor' ); ?></div>
	</div>

	<form id="user_billing_form" style="max-width: 600px;">
		<?php tutor_nonce_field(); ?>
		<input type="hidden" value="tutor_save_billing_info" name="action" />

		<?php require __DIR__ . '/billing-form-fields.php'; ?>

		<div class="tutor-row">
			<div class="tutor-col-12">
				<button type="submit" class="tutor-btn tutor-btn-primary">
					<?php esc_html_e( 'Save Address', 'tutor' ); ?>
				</button>
			</div>
		</div>
	</form>
</div>
