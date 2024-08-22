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

use Tutor\Ecommerce\BillingController;

$billing_controller = new BillingController();
$billing_info       = $billing_controller->get_billing_info();

$billing_first_name = $billing_info->billing_first_name ?? '';
$billing_last_name  = $billing_info->billing_last_name ?? '';
$billing_email      = $billing_info->billing_email ?? '';
$billing_phone      = $billing_info->billing_phone ?? '';
$billing_zip_code   = $billing_info->billing_zip_code ?? '';
$billing_address    = $billing_info->billing_address ?? '';
$billing_country    = $billing_info->billing_country ?? '';
$billing_state      = $billing_info->billing_state ?? '';
$billing_city       = $billing_info->billing_city ?? '';
?>

<div class="tutor-fs-5 tutor-fw-medium tutor-mb-24"><?php esc_html_e( 'Billing', 'tutor' ); ?></div>

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
