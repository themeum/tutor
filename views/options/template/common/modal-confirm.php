<?php
/**
 * Template: Modal for confirmation
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>

<div id="tutor-modal-bulk-action" class="tutor-modal tutor-modal-confirmation">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<div class="tutor-modal-content tutor-modal-content-white">
			<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
				<span class="tutor-icon-times" area-hidden="true"></span>
			</button>
			<div class="tutor-modal-body tutor-text-center">
				<div class="tutor-px-lg-48 tutor-py-lg-24">
					<div class="tutor-mt-24">
						<img class="tutor-d-inline-block" src="<?php echo esc_url( tutor()->icon_dir . 'reset.svg' ); ?>" />
					</div>

					<div class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mb-12" data-modal-dynamic-title></div>

					<div class="tutor-alert tutor-warning tutor-mt-32">
						<div class="tutor-alert-text">
							<span class="tutor-alert-icon tutor-fs-4 tutor-icon-warning tutor-mr-12"></span>
							<span class="tutor-color-warning" data-modal-dynamic-content></span>
						</div>
					</div>

					<div class="tutor-d-flex tutor-justify-center tutor-mt-48 tutor-mb-24">
						<button class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
							<?php esc_html_e( 'Cancel', 'tutor' ); ?>
						</button>
						<button class="reset_to_default tutor-btn tutor-btn-primary tutor-ml-20" data-reset-for="Null" data-reset="Null">
							<?php echo esc_attr( 'Reset' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
