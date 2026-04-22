<?php
/**
 * Legal consents settings view.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.8.0
 */

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

$default_consents = $consent_definition['default'] ?? array();
$stored_consents  = $this->get( 'legal_consents', $default_consents );

if ( ! is_array( $stored_consents ) || empty( $stored_consents ) ) {
	$stored_consents = $default_consents;
}

if ( isset( $stored_consents['enabled'] ) || isset( $stored_consents['title'] ) ) {
	$stored_consents = array( $stored_consents );
}

// Temporary compatibility with the single-consent keys used during early implementation.
if ( empty( $stored_consents ) || ! isset( $stored_consents[0]['title'] ) ) {
	$legacy_title      = $this->get( 'legal_consents_title', '' );
	$legacy_message    = $this->get( 'legal_consents_message', '' );
	$legacy_method     = $this->get( 'legal_consents_method', '' );
	$legacy_enabled    = $this->get( 'legal_consents_enabled', 'on' );
	$legacy_display_on = $this->get( 'legal_consents_display_on', array() );

	if ( $legacy_title || $legacy_message || $legacy_method || ! empty( $legacy_display_on ) ) {
		$stored_consents = array(
			array(
				'enabled'    => $legacy_enabled,
				'title'      => $legacy_title ? $legacy_title : __( 'Registration Consent', 'tutor' ),
				'display_on' => is_array( $legacy_display_on ) ? $legacy_display_on : array(),
				'message'    => $legacy_message ? $legacy_message : __( 'By continuing, you agree to our Terms of Service and Privacy Policy.', 'tutor' ),
				'method'     => $legacy_method ? $legacy_method : 'required_checkbox',
				'collapsed'  => 'off',
			),
		);
	}
}

$display_options = array(
	'signup_page'  => __( 'Sign up page', 'tutor' ),
	'login_page'   => __( 'Login Page', 'tutor' ),
	'checkout'     => __( 'Checkout', 'tutor' ),
	'subscription' => __( 'Subscription', 'tutor' ),
	'enrollment'   => __( 'Enrollment', 'tutor' ),
);

$method_options = array(
	'required_checkbox' => __( 'Required & Comes with a checkbox', 'tutor' ),
	'optional_checkbox' => __( 'Optional & Comes with a checkbox', 'tutor' ),
	'implicit'          => __( 'Implicit by continuing', 'tutor' ),
);

$wp_pages = get_pages(
	array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'sort_order'  => 'ASC',
		'sort_col'   => 'post_title',
	)
);

$default_item = $default_consents[0] ?? array(
	'enabled'    => 'on',
	'title'      => __( 'Registration Consent', 'tutor' ),
	'display_on' => array(
		'signup_page' => 'signup_page',
	),
	'message'    => __( 'By continuing, you agree to our Terms of Service and Privacy Policy.', 'tutor' ),
	'method'     => 'required_checkbox',
	'collapsed'  => 'off',
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
	$title_value   = $consent['title'] ?? '';
	$message_value = $consent['message'] ?? '';
	$method_value  = $consent['method'] ?? 'required_checkbox';
	$collapsed     = $consent['collapsed'] ?? 'off';
	$is_collapsed  = 'on' === $collapsed;
	$display_on    = $consent['display_on'] ?? array();

	if ( ! is_array( $display_on ) ) {
		$display_on = array();
	}
	?>
	<div class="tutor-legal-consent-card<?php echo $is_collapsed ? ' is-collapsed' : ''; ?>" data-consent-card data-consent-index="<?php echo esc_attr( $index ); ?>">
		<input type="hidden" name="tutor_option[legal_consents][<?php echo esc_attr( $index ); ?>][collapsed]" value="<?php echo esc_attr( $collapsed ); ?>" data-consent-collapsed>

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
						<button type="button" class="tutor-btn tutor-btn-ghost tutor-p-3 tutor-rounded" style="position: absolute; right: 8px; bottom: 8px; z-index: 1;" data-page-select-toggle title="<?php esc_attr_e( 'Add Page Link', 'tutor' ); ?>">
							<i class="tutor-icon-link" aria-hidden="true"></i>
						</button>
						<select name="tutor_option[legal_consents][<?php echo esc_attr( $index ); ?>][page_id]" class="tutor-form-select" style="position: absolute; width: 1px; height: 1px; padding: 0; border: 0; opacity: 0; pointer-events: none;" data-page-select hidden>
							<option value=""><?php esc_html_e( 'Select a page', 'tutor' ); ?></option>
							<?php foreach ( $wp_pages as $page ) : ?>
								<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $consent['page_id'] ?? '', $page->ID ); ?>><?php echo esc_html( $page->post_title ); ?></option>
							<?php endforeach; ?>
						</select>
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
