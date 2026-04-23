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

use Tutor\GDPR\Controllers\LegalConsent;

$consent_definition = array();

foreach ( $section['blocks'] as $block ) {
	if ( empty( $block['fields'] ) || ! is_array( $block['fields'] ) ) {
		continue;
	}

	foreach ( $block['fields'] as $field ) {
		if ( 'legal_consents' === ( $field['key'] ?? '' ) ) {
			$consent_definition = $field;
			break 2;
		}
	}
}

$default_consents    = $consent_definition['default'] ?? array();
$controller_consents = LegalConsent::get_consents();
$stored_consents     = ! empty( $controller_consents ) ? $controller_consents : $this->get( 'legal_consents', $default_consents );

if ( ! is_array( $stored_consents ) || empty( $stored_consents ) ) {
	$stored_consents = $default_consents;
}

if ( isset( $stored_consents['enabled'] ) || isset( $stored_consents['title'] ) ) {
	$stored_consents = array( $stored_consents );
}

$stored_consents = array_map(
	function ( $consent ) {
		$display_on = $consent['display_on'] ?? array();
		if ( ! is_array( $display_on ) ) {
			$display_on = array_filter( array_map( 'trim', explode( ',', (string) $display_on ) ) );
			$display_on = array_combine( $display_on, $display_on ) ?: array();
		}
		$consent['display_on'] = $display_on;
		$consent['method']     = $consent['method'] ?? LegalConsent::METHOD_MANDATORY_CHECK;
		$consent['collapsed']  = 'on';

		return $consent;
	},
	$stored_consents
);

$display_options = LegalConsent::get_display_place_options();
$method_options  = LegalConsent::get_consent_method_options();

$wp_pages = get_pages(
	array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'sort_order'  => 'ASC',
		'sort_col'    => 'post_title',
	)
);

$default_item = $default_consents[0] ?? array(
	'enabled'    => 'on',
	'title'      => __( 'Registration Consent', 'tutor' ),
	'display_on' => array(
		LegalConsent::DISPLAY_ON_SIGNUP => LegalConsent::DISPLAY_ON_SIGNUP,
	),
	'message'    => __( 'By continuing, you agree to our Terms of Service and Privacy Policy.', 'tutor' ),
	'method'     => LegalConsent::METHOD_MANDATORY_CHECK,
	'collapsed'  => 'on',
);

/**
 * Render a legal consent card.
 *
 * @param array $consent Consent values.
 * @param int   $index Consent position.
 *
 * @return void
 */
$render_card = function ( $consent, $index ) use ( $display_options, $method_options, $wp_pages ) {
	$enabled_value = $consent['enabled'] ?? 'on';
	$enabled_value = ( 1 === (int) $enabled_value || 'on' === $enabled_value ) ? 'on' : 'off';
	$consent_id    = isset( $consent['id'] ) ? (int) $consent['id'] : 0;
	$title_value   = $consent['title'] ?? '';
	$message_value = $consent['message'] ?? '';
	$method_value  = $consent['method'] ?? LegalConsent::METHOD_MANDATORY_CHECK;
	$collapsed     = $consent['collapsed'] ?? 'on';
	$is_collapsed  = 'on' === $collapsed;
	$display_on    = $consent['display_on'] ?? array();

	if ( ! is_array( $display_on ) ) {
		$display_on = array();
	}

	$current_map = $consent['content_map'] ?? array();
	if ( ! is_array( $current_map ) ) {
		$current_map = json_decode( (string) $current_map, true );
	}
	if ( ! is_array( $current_map ) ) {
		$current_map = array();
	}
	?>
	<div class="tutor-legal-consent-card<?php echo $is_collapsed ? ' is-collapsed' : ''; ?>" data-consent-card data-consent-index="<?php echo esc_attr( $index ); ?>" data-consent-id="<?php echo esc_attr( $consent_id ); ?>">
		<input type="hidden" name="tutor_option[legal_consents][<?php echo esc_attr( $index ); ?>][collapsed]" value="<?php echo esc_attr( $collapsed ); ?>" data-consent-collapsed>
		<?php tutor_nonce_field(); ?>

		<div class="tutor-legal-consent-card-header">
			<div class="tutor-legal-consent-card-title tutor-fs-6">
				<span class="tutor-icon-legal-consent" aria-hidden="true"></span>
				<span data-consent-title><?php echo esc_html( $title_value ); ?></span>
			</div>

			<div class="tutor-legal-consent-header-actions">
				<label class="tutor-form-toggle">
					<input type="hidden" name="tutor_option[legal_consents][<?php echo esc_attr( $index ); ?>][enabled]" value="<?php echo esc_attr( $enabled_value ); ?>" data-consent-enabled-hidden>
					<input type="checkbox" class="tutor-form-toggle-input" <?php checked( $enabled_value, 'on' ); ?> data-consent-enabled>
					<span class="tutor-form-toggle-control"></span>
				</label>

				<button type="button" class="tutor-legal-consent-settings-toggle" data-consent-toggle aria-expanded="<?php echo $is_collapsed ? 'false' : 'true'; ?>" aria-label="<?php esc_attr_e( 'Consent settings', 'tutor' ); ?>">
					<i class="tutor-icon-slider-horizontal" aria-hidden="true"></i>
				</button>
			</div>
		</div>

		<div class="tutor-legal-consent-card-content" data-consent-content>
			<div class="tutor-legal-consent-card-body">
				<div class="tutor-option-field-row" id="field_legal_consents_title_<?php echo esc_attr( $index ); ?>">
					<div class="tutor-option-field-label">
						<div class="tutor-fs-6 tutor-fw-medium" tutor-option-name><?php esc_html_e( 'Consent Title', 'tutor' ); ?></div>
						<div class="tutor-fs-7 tutor-color-muted tutor-mt-8"><?php esc_html_e( 'Internal title (visible to admin only)', 'tutor' ); ?></div>
					</div>
					<div class="tutor-option-field-input">
						<input
							type="text"
							name="tutor_option[legal_consents][<?php echo esc_attr( $index ); ?>][title]"
							class="tutor-form-control"
							placeholder="<?php esc_attr_e( 'Registration Consent', 'tutor' ); ?>"
							value="<?php echo esc_attr( $title_value ); ?>"
							data-consent-title-input
						/>
					</div>
				</div>

				<div class="tutor-option-field-row" id="field_legal_consents_display_on_<?php echo esc_attr( $index ); ?>">
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
										name="tutor_option[legal_consents][<?php echo esc_attr( $index ); ?>][display_on][<?php echo esc_attr( $option_key ); ?>]"
										value="<?php echo esc_attr( $option_key ); ?>"
										class="tutor-form-check-input"
										<?php checked( isset( $display_on[ $option_key ] ) ); ?>
									/>
									<label for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $option_label ); ?></label>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="tutor-option-field-row" id="field_legal_consents_message_<?php echo esc_attr( $index ); ?>">
					<div class="tutor-option-field-label">
						<div class="tutor-fs-6 tutor-fw-medium" tutor-option-name><?php esc_html_e( 'Consent Message', 'tutor' ); ?></div>
						<div class="tutor-fs-7 tutor-color-muted tutor-mt-8"><?php esc_html_e( 'Message shown to users', 'tutor' ); ?></div>
					</div>
					<div class="tutor-option-field-input" style="position: relative;">
						<textarea
							name="tutor_option[legal_consents][<?php echo esc_attr( $index ); ?>][message]"
							class="tutor-form-control"
							rows="5"
							placeholder="<?php esc_attr_e( 'By continuing, you agree to our Terms of Service and Privacy Policy.', 'tutor' ); ?>"
							style="padding-right: 44px;"
						><?php echo esc_textarea( $message_value ); ?></textarea>
						<div class="tutor-legal-consent-page-link-control">
							<button type="button" class="tutor-iconic-btn tutor-legal-consent-page-link-trigger" data-page-dropdown-toggle aria-expanded="false" aria-label="<?php esc_attr_e( 'Add Page Link', 'tutor' ); ?>" title="<?php esc_attr_e( 'Add Page Link', 'tutor' ); ?>">
								<i class="tutor-icon-plus-light" aria-hidden="true"></i>
							</button>
							<div class="tutor-option-dropdown tutor-legal-consent-page-dropdown" data-page-dropdown hidden>
							<?php foreach ( $wp_pages as $page ) : ?>
								<?php
								$page_slug   = strtolower( preg_replace( '/[^a-z0-9]+/', '_', sanitize_title( $page->post_title ) ) );
								$page_key    = $page_slug . '_' . $page->ID;
								$is_selected = isset( $current_map[ $page_key ] );
								?>
								<button type="button" class="tutor-legal-consent-page-dropdown-item<?php echo $is_selected ? ' is-selected' : ''; ?>" data-page-btn value="<?php echo esc_attr( $page->ID ); ?>" data-page-slug="<?php echo esc_attr( $page_slug ); ?>" data-page-key="<?php echo esc_attr( $page_key ); ?>" <?php echo $is_selected ? 'disabled' : ''; ?>>
									<?php echo esc_html( $page->post_title ); ?>
								</button>
							<?php endforeach; ?>
						</div>
						</div>
					</div>
				</div>

				<div class="tutor-option-field-row" id="field_legal_consents_method_<?php echo esc_attr( $index ); ?>">
					<div class="tutor-option-field-label">
						<div class="tutor-fs-6 tutor-fw-medium" tutor-option-name><?php esc_html_e( 'Consent Method', 'tutor' ); ?></div>
						<div class="tutor-fs-7 tutor-color-muted tutor-mt-8"><?php esc_html_e( 'How users give consent', 'tutor' ); ?></div>
					</div>
					<div class="tutor-option-field-input">
						<select name="tutor_option[legal_consents][<?php echo esc_attr( $index ); ?>][method]" class="tutor-form-select">
							<?php foreach ( $method_options as $option_key => $option_label ) : ?>
								<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $method_value, $option_key ); ?>><?php echo esc_html( $option_label ); ?></option>
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
						<?php esc_html_e( 'Cancel', 'tutor' ); ?>
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

echo $this->view_template( 'common/reset-button-template.php', $section ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<div class="tutor-legal-consents" data-legal-consents>
	<div class="tutor-legal-consents-list" data-consent-list>
		<?php foreach ( $stored_consents as $index => $consent ) : ?>
			<?php $render_card( $consent, $index ); ?>
		<?php endforeach; ?>
	</div>

	<div class="tutor-legal-consents-footer">
		<button type="button" class="tutor-btn tutor-btn-outline-primary" data-add-consent>
			<i class="tutor-icon-plus-light tutor-mr-8" aria-hidden="true"></i>
			<?php esc_html_e( 'New Consent', 'tutor' ); ?>
		</button>
	</div>

	<template data-consent-template>
		<?php $render_card( $default_item, '__INDEX__' ); ?>
	</template>
</div>
