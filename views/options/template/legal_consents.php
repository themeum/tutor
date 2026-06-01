<?php
/**
 * Legal consents settings view.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\GDPR\Controllers\LegalConsent;

defined( 'ABSPATH' ) || exit;

$consents = LegalConsent::get_consents();

if ( empty( $consents ) ) {
	$consents = $this->get( 'legal_consents', array() );
}

if ( ! is_array( $consents ) ) {
	$consents = array();
}

if ( isset( $consents['enabled'] ) || isset( $consents['title'] ) ) {
	$consents = array( $consents );
}

$display_options = LegalConsent::get_display_place_options();
$method_options  = LegalConsent::get_consent_method_options();
$wp_pages        = get_pages(
	array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'sort_order'  => 'ASC',
		'sort_col'    => 'post_title',
	)
);
$default_consent = array(
	'id'          => 0,
	'enabled'     => 'off',
	'title'       => __( 'Consent Title', 'tutor' ),
	'display_on'  => array(),
	'message'     => __( 'By continuing, you agree to our Terms of Service and Privacy Policy.', 'tutor' ),
	'method'      => LegalConsent::METHOD_MANDATORY_CHECK,
	'content_map' => array(),
);

$render_card = function ( array $consent, $index ) use ( $display_options, $method_options, $wp_pages ) {
	$consent_id   = $consent['id'] ?? 0;
	$enabled      = $consent['enabled'] ?? 'off';
	$title        = $consent['title'] ?? '';
	$display_on   = $consent['display_on'] ?? array();
	$message      = $consent['message'] ?? '';
	$method       = $consent['method'] ?? LegalConsent::METHOD_MANDATORY_CHECK;
	$content_map  = $consent['content_map'] ?? array();
	$is_collapsed = 0 !== (int) $consent['id'];
	?>
	<div class="tutor-legal-consent-card<?php echo $is_collapsed ? ' is-collapsed' : ''; ?>" data-consent-card data-consent-index="<?php echo esc_attr( $index ); ?>" data-consent-id="<?php echo esc_attr( $consent_id ); ?>">
		<?php tutor_nonce_field(); ?>

		<div class="tutor-legal-consent-card-header">
			<div class="tutor-legal-consent-card-title tutor-fs-6">
				<?php SvgIcon::make()->name( Icon::CONTRACT_OUTLINE )->size( 32 )->render(); ?>
				<span data-consent-title><?php echo esc_html( $title ); ?></span>
			</div>

			<div class="tutor-legal-consent-header-actions">
				<label class="tutor-form-toggle">
					<input type="hidden" value="<?php echo esc_attr( $enabled ); ?>" data-consent-enabled-hidden>
					<input type="checkbox" class="tutor-form-toggle-input" <?php checked( $enabled, 'on' ); ?> data-consent-enabled>
					<span class="tutor-form-toggle-control"></span>
				</label>

				<button type="button" class="tutor-legal-consent-settings-toggle" data-consent-toggle aria-expanded="<?php echo $is_collapsed ? 'false' : 'true'; ?>" aria-label="<?php esc_attr_e( 'Consent settings', 'tutor' ); ?>" <?php disabled( 0 === (int) $consent_id ); ?>>
					<i class="tutor-icon-slider-horizontal" aria-hidden="true"></i>
				</button>
			</div>
		</div>

		<div class="tutor-legal-consent-card-content" data-consent-content>
			<div class="tutor-legal-consent-card-body">
				<div class="tutor-option-field-row">
					<div class="tutor-option-field-label">
						<div class="tutor-fs-6 tutor-fw-medium" tutor-option-name><?php esc_html_e( 'Consent Title', 'tutor' ); ?></div>
						<div class="tutor-fs-7 tutor-color-muted tutor-mt-8"><?php esc_html_e( 'Enter title (visible to admin only)', 'tutor' ); ?></div>
					</div>
					<div class="tutor-option-field-input">
						<input
							type="text"
							class="tutor-form-control"
							placeholder="<?php esc_attr_e( 'Consent Title', 'tutor' ); ?>"
							value="<?php echo esc_attr( $title ); ?>"
							data-consent-title-input
						/>
					</div>
				</div>

				<div class="tutor-option-field-row">
					<div class="tutor-option-field-label">
						<div class="tutor-fs-6 tutor-fw-medium" tutor-option-name><?php esc_html_e( 'Display on', 'tutor' ); ?></div>
						<div class="tutor-fs-7 tutor-color-muted tutor-mt-8"><?php esc_html_e( 'Where this consent appears', 'tutor' ); ?></div>
					</div>
					<div class="tutor-option-field-input">
						<div class="tutor-legal-consent-checkbox-grid">
							<?php foreach ( $display_options as $option_key => $option_label ) : ?>
								<?php $input_id = 'legal_consents_display_on_' . $index . '_' . $option_key; ?>
								<div class="tutor-form-check">
									<input
										type="checkbox"
										id="<?php echo esc_attr( $input_id ); ?>"
										value="<?php echo esc_attr( $option_key ); ?>"
										class="tutor-form-check-input"
										name="display_on[]"
										<?php checked( isset( $display_on[ $option_key ] ) ); ?>
									/>
									<label for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $option_label ); ?></label>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="tutor-option-field-row">
					<div class="tutor-option-field-label">
						<div class="tutor-fs-6 tutor-fw-medium" tutor-option-name><?php esc_html_e( 'Consent Message', 'tutor' ); ?></div>
						<div class="tutor-fs-7 tutor-color-muted tutor-mt-8"><?php esc_html_e( 'Message shown to users', 'tutor' ); ?></div>
					</div>
					<div class="tutor-option-field-input" style="position: relative;">
						<textarea
							class="tutor-form-control"
							rows="5"
							placeholder="<?php esc_attr_e( 'By continuing, you agree to our Terms of Service and Privacy Policy.', 'tutor' ); ?>"
							style="padding-right: 44px;"
							name="consent_message"
						><?php echo esc_textarea( $message ); ?></textarea>

						<div class="tutor-legal-consent-page-link-control">
							<button type="button" class="tutor-iconic-btn tutor-legal-consent-page-link-trigger" data-page-dropdown-toggle aria-expanded="false" aria-label="<?php esc_attr_e( 'Add Page Link', 'tutor' ); ?>" title="<?php esc_attr_e( 'Add Page Link', 'tutor' ); ?>">
								<i class="tutor-icon-plus-light" aria-hidden="true"></i>
							</button>

							<div class="tutor-option-dropdown tutor-legal-consent-page-dropdown" data-page-dropdown hidden>
								<?php foreach ( $wp_pages as $page ) : ?>
									<?php
									$page_key = strtolower( preg_replace( '/[^a-z0-9]+/', '_', sanitize_title( $page->post_title ) ) );
									?>
									<button type="button" class="tutor-legal-consent-page-dropdown-item<?php echo isset( $content_map[ $page_key ] ) ? ' is-selected' : ''; ?>" data-page-btn value="<?php echo esc_attr( $page->ID ); ?>" data-page-key="<?php echo esc_attr( $page_key ); ?>" <?php echo isset( $content_map[ $page_key ] ) ? 'disabled' : ''; ?>>
										<?php echo esc_html( $page->post_title ); ?>
									</button>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>

				<div class="tutor-option-field-row">
					<div class="tutor-option-field-label">
						<div class="tutor-fs-6 tutor-fw-medium" tutor-option-name><?php esc_html_e( 'Consent Method', 'tutor' ); ?></div>
						<div class="tutor-fs-7 tutor-color-muted tutor-mt-8"><?php esc_html_e( 'How users give consent', 'tutor' ); ?></div>
					</div>
					<div class="tutor-option-field-input">
						<select class="tutor-form-select" name="consent_method">
							<?php foreach ( $method_options as $option_key => $option_label ) : ?>
								<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $method, $option_key ); ?>><?php echo esc_html( $option_label ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>

			<div class="tutor-legal-consent-card-footer">
				<button type="button" class="tutor-btn tutor-btn-sm tutor-legal-consent-delete-btn" data-consent-delete>
					<?php esc_html_e( 'Delete', 'tutor' ); ?>
				</button>

				<div class="tutor-legal-consent-card-footer-actions">
					<button type="button" class="tutor-btn tutor-btn-sm tutor-btn-ghost" data-consent-cancel>
						<?php esc_html_e( 'Discard', 'tutor' ); ?>
					</button>

					<button type="button" class="tutor-btn tutor-btn-sm tutor-btn-primary" data-consent-save>
						<?php esc_html_e( 'Save Changes', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
	<?php
};
?>

<div class="tutor-option-main-title">
	<div class="tutor-fs-4 tutor-fw-medium tutor-color-black" tutor-option-title><?php echo esc_html( $section['label'] ?? __( 'Legal Consents', 'tutor' ) ); ?></div>
</div>

<div class="tutor-legal-consents" data-legal-consents>
	<div data-consent-empty-state<?php echo ! empty( $consents ) ? ' hidden' : ''; ?>>
		<?php tutor_utils()->render_list_empty_state( array( 'title' => __( 'No legal consent yet.', 'tutor' ) ) ); ?>

		<div class="tutor-legal-consents-empty-state-action">
			<button type="button" class="tutor-btn tutor-btn-outline-primary" data-add-consent>
				<i class="tutor-icon-plus-light tutor-mr-8" aria-hidden="true"></i>
				<?php esc_html_e( 'New Consent', 'tutor' ); ?>
			</button>
		</div>
	</div>

	<div class="tutor-legal-consents-list" data-consent-list>
		<?php foreach ( $consents as $index => $consent ) : ?>
			<?php $render_card( $consent, $index ); ?>
		<?php endforeach; ?>
	</div>

	<div class="tutor-legal-consents-footer" data-consent-footer<?php echo empty( $consents ) ? ' hidden' : ''; ?>>
		<button type="button" class="tutor-btn tutor-btn-outline-primary" data-add-consent>
			<i class="tutor-icon-plus-light tutor-mr-8" aria-hidden="true"></i>
			<?php esc_html_e( 'New Consent', 'tutor' ); ?>
		</button>
	</div>

	<template data-consent-template>
		<?php $render_card( $default_consent, '__INDEX__' ); ?>
	</template>
</div>

<div id="tutor-legal-consent-delete-modal" class="tutor-modal" role="dialog" aria-modal="true" aria-hidden="true">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<div class="tutor-modal-content tutor-modal-content-white">
			<button type="button" class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close aria-label="<?php esc_attr_e( 'Close', 'tutor' ); ?>">
				<span class="tutor-icon-times" aria-hidden="true"></span>
			</button>

			<div class="tutor-modal-body tutor-text-center">
				<div class="tutor-mt-48">
					<img class="tutor-d-inline-block" src="<?php echo esc_url( tutor()->url ); ?>assets/images/icon-trash.svg" alt="" aria-hidden="true" />
				</div>

				<div class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mb-12"><?php esc_html_e( 'Delete Consent?', 'tutor' ); ?></div>
				<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Are you sure you want to permanently delete this consent?', 'tutor' ); ?></div>
				<div class="tutor-d-flex tutor-justify-center tutor-my-48">
					<button type="button" class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
						<?php esc_html_e( 'Cancel', 'tutor' ); ?>
					</button>
					<button type="button" class="tutor-btn tutor-btn-primary tutor-ml-20" id="tutor-legal-consent-confirm-delete">
						<?php esc_html_e( 'Delete Consent', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
