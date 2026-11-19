<?php
/**
 * Deactivation & Uninstall Feedback Handler
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.2.2
 */

namespace TUTOR;

defined( 'ABSPATH' ) || exit;

use Tutor\Core\Input;

/**
 * Class UninstallFeedback
 */
class UninstallFeedback {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_footer', array( $this, 'render_deactivation_modal' ) );
		add_action( 'wp_ajax_tutor_uninstall_feedback', array( $this, 'ajax_send_uninstall_feedback' ) );
	}

	/**
	 * Enqueue assets only on plugins.php screen
	 *
	 * @param string $hook Screen hook.
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		if ( 'plugins.php' !== $hook || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		wp_register_style( 'tutor-uninstall-feedback', false, array(), TUTOR_VERSION );
		wp_enqueue_style( 'tutor-uninstall-feedback' );
		wp_add_inline_style( 'tutor-uninstall-feedback', $this->get_inline_css() );

		wp_register_script( 'tutor-uninstall-feedback', false, array( 'jquery' ), TUTOR_VERSION, true );
		wp_enqueue_script( 'tutor-uninstall-feedback' );
		wp_localize_script(
			'tutor-uninstall-feedback',
			'tutor_uninstall_feedback_data',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'tutor_uninstall_feedback_nonce' ),
			)
		);
		wp_add_inline_script( 'tutor-uninstall-feedback', $this->get_inline_js() );
	}

	/**
	 * Render the deactivation feedback modal HTML in admin footer
	 *
	 * @return void
	 */
	public function render_deactivation_modal() {
		$screen = get_current_screen();
		if ( ! $screen || 'plugins' !== $screen->id || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		include tutor()->path . 'views/modal/uninstall-feedback-modal.php';
	}

	/**
	 * Handle AJAX request for deactivation feedback
	 *
	 * @return void
	 */
	public function ajax_send_uninstall_feedback() {
		$nonce = Input::post( 'security', '' );
		if ( ! wp_verify_nonce( $nonce, 'tutor_uninstall_feedback_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'tutor' ) ), 403 );
		}

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'tutor' ) ), 403 );
		}

		$plugin_slug      = sanitize_key( Input::post( 'plugin_slug', 'tutor' ) );
		$uninstall_reason = sanitize_text_field( Input::post( 'uninstall_reason', '' ) );
		$reason_detail    = sanitize_textarea_field( Input::post( 'reason_detail', '' ) );

		// Determine plugin version.
		$plugin_version = TUTOR_VERSION;
		if ( 'tutor-pro' === $plugin_slug && defined( 'TUTOR_PRO_VERSION' ) ) {
			$plugin_version = TUTOR_PRO_VERSION;
		}

		$current_user = wp_get_current_user();
		$user_email   = $current_user && $current_user->exists() ? $current_user->user_email : '';

		// Prepare payload with all required fields.
		$payload = array(
			'plugin_slug'      => $plugin_slug,
			'plugin_version'   => $plugin_version,
			'website_url'      => get_site_url(),
			'user_email'       => $user_email,
			'user_ip'          => tutor_utils()->get_ip(),
			'uninstall_reason' => $uninstall_reason,
			'reason_detail'    => $reason_detail,
			'created_at'       => gmdate( 'Y-m-d H:i:s' ),
			'updated_at'       => gmdate( 'Y-m-d H:i:s' ),
		);

		/**
		 * Filter the uninstall feedback payload before dispatch.
		 *
		 * @since 4.2.2
		 *
		 * @param array $payload Feedback payload data.
		 */
		$payload = apply_filters( 'tutor_uninstall_feedback_payload', $payload );

		/**
		 * Filter the remote endpoint receiver URL.
		 *
		 * @since 4.2.2
		 *
		 * @param string $endpoint Remote API endpoint.
		 */
		$endpoint = apply_filters( 'tutor_uninstall_feedback_endpoint', 'https://api.themeum.com/api/v1/tutor/uninstall-feedback' );

		if ( ! empty( $endpoint ) ) {
			wp_remote_post(
				$endpoint,
				array(
					'method'      => 'POST',
					'timeout'     => 3,
					'redirection' => 2,
					'httpversion' => '1.1',
					'blocking'    => false,
					'headers'     => array(
						'Content-Type' => 'application/json; charset=utf-8',
						'Accept'       => 'application/json',
					),
					'body'        => wp_json_encode( $payload ),
					'data_format' => 'body',
				)
			);
		}

		wp_send_json_success(
			array(
				'message' => __( 'Thank you for your feedback.', 'tutor' ),
				'payload' => $payload,
			)
		);
	}

	/**
	 * Scoped CSS for the deactivation feedback modal
	 *
	 * @return string
	 */
	private function get_inline_css(): string {
		return '
			#tutor-uninstall-feedback-modal {
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				z-index: 100000;
				display: flex;
				align-items: center;
				justify-content: center;
				box-sizing: border-box;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			}
			#tutor-uninstall-feedback-modal *,
			#tutor-uninstall-feedback-modal *::before,
			#tutor-uninstall-feedback-modal *::after {
				box-sizing: border-box;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-modal-overlay {
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				background: rgba(18, 20, 24, 0.65);
				backdrop-filter: blur(2px);
				z-index: 100001;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-modal-window {
				position: relative;
				z-index: 100002;
				background: #ffffff;
				border-radius: 12px;
				width: 100%;
				max-width: 540px;
				margin: 20px;
				box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15), 0 1px 3px rgba(0, 0, 0, 0.05);
				overflow: hidden;
				animation: tutorFeedbackFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
			}
			@keyframes tutorFeedbackFadeIn {
				from { opacity: 0; transform: scale(0.96) translateY(-8px); }
				to { opacity: 1; transform: scale(1) translateY(0); }
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-modal-header {
				display: flex;
				align-items: flex-start;
				justify-content: space-between;
				padding: 24px 28px 16px;
				border-bottom: 1px solid #eef0f3;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-header-left {
				display: flex;
				gap: 14px;
				align-items: flex-start;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-brand-icon {
				flex-shrink: 0;
				margin-top: 2px;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-modal-title {
				margin: 0 0 6px 0;
				font-size: 18px;
				font-weight: 600;
				color: #1e2229;
				line-height: 1.3;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-modal-subtitle {
				margin: 0;
				font-size: 13.5px;
				color: #636b78;
				line-height: 1.4;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-modal-close {
				background: transparent;
				border: none;
				font-size: 24px;
				line-height: 1;
				color: #929ba8;
				cursor: pointer;
				padding: 4px;
				margin: -4px -6px 0 0;
				border-radius: 6px;
				transition: all 0.15s ease;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-modal-close:hover {
				color: #1e2229;
				background: #f3f5f7;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-modal-body {
				padding: 20px 28px;
				max-height: 60vh;
				overflow-y: auto;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-reasons-list {
				display: flex;
				flex-direction: column;
				gap: 12px;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-reason-item {
				display: flex;
				flex-direction: column;
				gap: 8px;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-radio-label {
				display: flex;
				align-items: center;
				gap: 10px;
				cursor: pointer;
				font-size: 14px;
				color: #2c333e;
				user-select: none;
				line-height: 1.4;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-radio {
				margin: 0 !important;
				cursor: pointer;
				width: 17px !important;
				height: 17px !important;
				accent-color: #3e64de;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-detail-wrap {
				margin-left: 27px;
				margin-top: 4px;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-detail-input {
				width: 100%;
				border: 1px solid #dcdfe4;
				border-radius: 8px;
				padding: 10px 12px;
				font-size: 13px;
				color: #212529;
				outline: none;
				resize: vertical;
				transition: border-color 0.15s ease, box-shadow 0.15s ease;
				font-family: inherit;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-detail-input:focus {
				border-color: #3e64de;
				box-shadow: 0 0 0 3px rgba(62, 100, 222, 0.15);
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-consent-notice {
				margin-top: 20px;
				padding: 12px 14px;
				background: #f7f9fc;
				border: 1px solid #e9ecf2;
				border-radius: 8px;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-consent-notice p {
				margin: 0;
				font-size: 12px;
				line-height: 1.5;
				color: #727a87;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-modal-footer {
				display: flex;
				align-items: center;
				justify-content: space-between;
				padding: 16px 28px 20px;
				border-top: 1px solid #eef0f3;
				background: #ffffff;
			}
			#tutor-uninstall-feedback-modal .tutor-feedback-footer-right {
				display: flex;
				align-items: center;
				gap: 12px;
			}
			#tutor-uninstall-feedback-modal .tutor-btn-feedback {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				gap: 8px;
				padding: 9px 18px;
				border-radius: 8px;
				font-size: 13.5px;
				font-weight: 500;
				cursor: pointer;
				border: 1px solid transparent;
				transition: all 0.15s ease;
				text-decoration: none;
				line-height: 1.3;
			}
			#tutor-uninstall-feedback-modal .tutor-btn-feedback-cancel {
				background: transparent;
				color: #636b78;
				border-color: #dcdfe4;
			}
			#tutor-uninstall-feedback-modal .tutor-btn-feedback-cancel:hover {
				background: #f5f6f8;
				color: #212529;
			}
			#tutor-uninstall-feedback-modal .tutor-btn-feedback-skip {
				background: transparent;
				color: #636b78;
				padding: 8px 12px;
			}
			#tutor-uninstall-feedback-modal .tutor-btn-feedback-skip:hover {
				color: #212529;
				text-decoration: underline;
			}
			#tutor-uninstall-feedback-modal .tutor-btn-feedback-submit {
				background: #3e64de;
				color: #ffffff;
				border-color: #3e64de;
				box-shadow: 0 1px 2px rgba(62, 100, 222, 0.2);
			}
			#tutor-uninstall-feedback-modal .tutor-btn-feedback-submit:hover:not(:disabled) {
				background: #3051c2;
				border-color: #3051c2;
			}
			#tutor-uninstall-feedback-modal .tutor-btn-feedback-submit:disabled {
				opacity: 0.55;
				cursor: not-allowed;
				background: #a0b2ee;
				border-color: #a0b2ee;
			}
			#tutor-uninstall-feedback-modal .tutor-btn-feedback-spinner {
				width: 14px;
				height: 14px;
				border: 2px solid rgba(255, 255, 255, 0.4);
				border-top-color: #ffffff;
				border-radius: 50%;
				animation: tutorFeedbackSpin 0.6s linear infinite;
			}
			@keyframes tutorFeedbackSpin {
				to { transform: rotate(360deg); }
			}
		';
	}

	/**
	 * Client-side script for deactivation feedback modal
	 *
	 * @return string
	 */
	private function get_inline_js(): string {
		return '
			(function($) {
				"use strict";
				$(function() {
					var modal = $("#tutor-uninstall-feedback-modal");
					if (!modal.length) {
						return;
					}

					var form = $("#tutor-uninstall-feedback-form");
					var deactivationUrl = "";
					var currentPluginSlug = "tutor";

					var deactivateSelectors = [
						\'tr[data-slug="tutor"] .deactivate a\',
						\'tr[data-slug="tutor-pro"] .deactivate a\',
						\'tr[data-plugin*="tutor.php"] .deactivate a\',
						\'tr[data-plugin*="tutor-pro.php"] .deactivate a\',
						\'a[href*="action=deactivate"][href*="tutor%2Ftutor.php"]\',
						\'a[href*="action=deactivate"][href*="tutor/tutor.php"]\',
						\'a[href*="action=deactivate"][href*="tutor-pro%2Ftutor-pro.php"]\',
						\'a[href*="action=deactivate"][href*="tutor-pro/tutor-pro.php"]\'
					];

					$(document).on("click", deactivateSelectors.join(","), function(e) {
						e.preventDefault();
						deactivationUrl = $(this).attr("href");

						var row = $(this).closest("tr");
						var slug = row.attr("data-slug") || "";
						var pluginAttr = row.attr("data-plugin") || "";
						var href = deactivationUrl || "";

						var isPro = slug === "tutor-pro" || pluginAttr.indexOf("tutor-pro") !== -1 || href.indexOf("tutor-pro") !== -1;
						currentPluginSlug = isPro ? "tutor-pro" : "tutor";

						modal.find(".tutor-plugin-name").text(isPro ? "Tutor LMS Pro" : "Tutor LMS");

						form[0].reset();
						modal.find(".tutor-feedback-detail-wrap").hide();
						modal.find(".tutor-btn-feedback-submit").prop("disabled", true);
						modal.find(".tutor-btn-feedback-submit .tutor-btn-feedback-text").text("Submit & Deactivate");
						modal.find(".tutor-btn-feedback-spinner").hide();
						modal.find(".tutor-btn-feedback").prop("disabled", false);

						modal.fadeIn(150);
					});

					modal.on("change", \'input[name="uninstall_reason"]\', function() {
						var selectedVal = $(this).val();
						modal.find(".tutor-btn-feedback-submit").prop("disabled", false);

						modal.find(".tutor-feedback-detail-wrap").hide();
						var targetDetail = modal.find("#detail-wrap-" + selectedVal);
						if (targetDetail.length) {
							targetDetail.slideDown(150);
							targetDetail.find("textarea").focus();
						}
					});

					function closeModal() {
						modal.fadeOut(150);
						deactivationUrl = "";
					}

					modal.on("click", ".tutor-feedback-modal-close, .tutor-btn-feedback-cancel, .tutor-feedback-modal-overlay", function(e) {
						e.preventDefault();
						closeModal();
					});

					$(document).on("keyup", function(e) {
						if (e.key === "Escape" && modal.is(":visible")) {
							closeModal();
						}
					});

					modal.on("click", ".tutor-btn-feedback-skip", function(e) {
						e.preventDefault();
						if (deactivationUrl) {
							window.location.href = deactivationUrl;
						}
					});

					form.on("submit", function(e) {
						e.preventDefault();

						var selectedReason = modal.find(\'input[name="uninstall_reason"]:checked\').val() || "";
						var reasonDetail = modal.find("#tutor-reason-detail-" + selectedReason).val() || "";

						var submitBtn = modal.find(".tutor-btn-feedback-submit");
						submitBtn.prop("disabled", true);
						submitBtn.find(".tutor-btn-feedback-text").text("Deactivating...");
						submitBtn.find(".tutor-btn-feedback-spinner").show();
						modal.find(".tutor-btn-feedback-cancel, .tutor-btn-feedback-skip").prop("disabled", true);

						var safetyRedirectTimer = setTimeout(function() {
							if (deactivationUrl) {
								window.location.href = deactivationUrl;
							}
						}, 3000);

						$.ajax({
							url: window.tutor_uninstall_feedback_data ? window.tutor_uninstall_feedback_data.ajax_url : ajaxurl,
							type: "POST",
							data: {
								action: "tutor_uninstall_feedback",
								security: window.tutor_uninstall_feedback_data ? window.tutor_uninstall_feedback_data.nonce : "",
								plugin_slug: currentPluginSlug,
								uninstall_reason: selectedReason,
								reason_detail: reasonDetail
							},
							complete: function() {
								clearTimeout(safetyRedirectTimer);
								if (deactivationUrl) {
									window.location.href = deactivationUrl;
								}
							}
						});
					});
				});
			})(jQuery);
		';
	}
}
