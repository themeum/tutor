<?php
/**
 * Deactivation & Uninstall Feedback Modal Template
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.2.2
 */

defined( 'ABSPATH' ) || exit;

$reasons = array(
	'could_not_setup'        => array(
		'label'       => __( "I couldn't set up or configure the plugin", 'tutor' ),
		'placeholder' => '',
	),
	'found_better_plugin'    => array(
		'label'       => __( 'I found a better alternative', 'tutor' ),
		'placeholder' => __( 'Which plugin did you choose and why? (optional)', 'tutor' ),
	),
	'missing_feature'        => array(
		'label'       => __( 'A key feature is missing', 'tutor' ),
		'placeholder' => __( 'What feature are you looking for? (optional)', 'tutor' ),
	),
	'technical_issues'       => array(
		'label'       => __( 'The plugin caused technical issues or conflicts', 'tutor' ),
		'placeholder' => __( 'What broke or conflicted? (optional)', 'tutor' ),
	),
	'temporary_deactivation' => array(
		'label'       => __( "I'm temporarily deactivating for troubleshooting", 'tutor' ),
		'placeholder' => '',
	),
	'other'                  => array(
		'label'       => __( 'Other', 'tutor' ),
		'placeholder' => __( 'Please share your reason (optional)', 'tutor' ),
	),
);
?>

<div id="tutor-uninstall-feedback-modal" class="tutor-feedback-modal-wrap" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="tutor-feedback-modal-title">
	<div class="tutor-feedback-modal-overlay"></div>

	<div class="tutor-feedback-modal-window">
		<div class="tutor-feedback-modal-header">
			<div class="tutor-feedback-header-left">
				<div class="tutor-feedback-brand-icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<rect width="24" height="24" rx="6" fill="#3E64DE"/>
						<path d="M7 8H17M7 12H14M7 16H11" stroke="white" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</div>
				<div class="tutor-feedback-header-content">
					<h3 id="tutor-feedback-modal-title" class="tutor-feedback-modal-title">
						<span class="tutor-plugin-name"><?php esc_html_e( 'Tutor LMS', 'tutor' ); ?></span> - <?php esc_html_e( 'Quick Feedback', 'tutor' ); ?>
					</h3>
					<p class="tutor-feedback-modal-subtitle">
						<?php esc_html_e( 'If you have a moment, please let us know why you are deactivating:', 'tutor' ); ?>
					</p>
				</div>
			</div>
			<button type="button" class="tutor-feedback-modal-close" aria-label="<?php esc_attr_e( 'Close', 'tutor' ); ?>">&times;</button>
		</div>

		<form id="tutor-uninstall-feedback-form">
			<div class="tutor-feedback-modal-body">
				<div class="tutor-feedback-reasons-list">
					<?php foreach ( $reasons as $reason_key => $reason_info ) : ?>
						<div class="tutor-feedback-reason-item">
							<label class="tutor-feedback-radio-label">
								<input type="radio" name="uninstall_reason" value="<?php echo esc_attr( $reason_key ); ?>" class="tutor-feedback-radio" />
								<span class="tutor-feedback-radio-text"><?php echo esc_html( $reason_info['label'] ); ?></span>
							</label>

							<?php if ( ! empty( $reason_info['placeholder'] ) ) : ?>
								<div class="tutor-feedback-detail-wrap" id="detail-wrap-<?php echo esc_attr( $reason_key ); ?>" style="display: none;">
									<textarea
										class="tutor-feedback-detail-input"
										id="tutor-reason-detail-<?php echo esc_attr( $reason_key ); ?>"
										placeholder="<?php echo esc_attr( $reason_info['placeholder'] ); ?>"
										rows="2"
									></textarea>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="tutor-feedback-consent-notice">
					<p>
						<?php esc_html_e( 'By submitting, you agree to share your feedback, site URL, and contact email with Themeum to help improve Tutor LMS. You can also skip this step.', 'tutor' ); ?>
					</p>
				</div>
			</div>

			<div class="tutor-feedback-modal-footer">
				<div class="tutor-feedback-footer-left">
					<button type="button" class="tutor-btn-feedback tutor-btn-feedback-cancel">
						<?php esc_html_e( 'Cancel', 'tutor' ); ?>
					</button>
				</div>
				<div class="tutor-feedback-footer-right">
					<button type="button" class="tutor-btn-feedback tutor-btn-feedback-skip">
						<?php esc_html_e( 'Skip & Deactivate', 'tutor' ); ?>
					</button>
					<button type="submit" class="tutor-btn-feedback tutor-btn-feedback-submit" disabled>
						<span class="tutor-btn-feedback-text"><?php esc_html_e( 'Submit & Deactivate', 'tutor' ); ?></span>
						<span class="tutor-btn-feedback-spinner" style="display: none;"></span>
					</button>
				</div>
			</div>
		</form>
	</div>
</div>
