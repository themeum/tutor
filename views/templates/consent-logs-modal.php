<?php
/**
 * Consent logs modal template.
 *
 * This template can be used in both students and instructors pages.
 *
 * @package Tutor\Views
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="tutor-modal" id="tutor-consent-logs-modal" role="dialog" aria-modal="true" aria-labelledby="tutor-consent-logs-title" aria-hidden="true">
	<div class="tutor-modal-overlay" data-tutor-modal-close></div>
	<div class="tutor-modal-window">
		<div class="tutor-modal-content tutor-modal-content-white">
			<button type="button" class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close aria-label="<?php esc_attr_e( 'Close', 'tutor' ); ?>">
				<span class="tutor-icon-times" aria-hidden="true"></span>
			</button>

			<div class="tutor-modal-header tutor-p-24 tutor-border-bottom">
				<h3 id="tutor-consent-logs-title" class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-m-0"><?php esc_html_e( 'Consent logs', 'tutor' ); ?></h3>
			</div>

			<div class="tutor-modal-body tutor-p-24 tutor-consent-logs-modal-body" style="max-height: 60vh; overflow-y: auto;">
				<div class="tutor-d-flex tutor-align-center tutor-justify-center tutor-py-48 tutor-color-muted tutor-fs-6"><?php esc_html_e( 'Loading...', 'tutor' ); ?></div>
			</div>

			<div class="tutor-modal-footer tutor-p-24 tutor-d-flex tutor-justify-end tutor-gap-1">
				<button type="button" class="tutor-btn tutor-btn-ghost tutor-mr-8" data-tutor-modal-close>
					<?php esc_html_e( 'Cancel', 'tutor' ); ?>
				</button>
				<button type="button" class="tutor-btn tutor-btn-secondary" data-consent-logs-download style="display: none;">
					<span class="tutor-icon-download tutor-mr-8" aria-hidden="true"></span>
					<?php esc_html_e( 'Download CSV', 'tutor' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
