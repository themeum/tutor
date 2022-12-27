<?php
/**
 * Toggle switch button for email settings page.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$field_default    = $field['default'];
$field_key        = $field['key'];
$field_event      = $field['event'];
$field_label      = esc_attr( $field['label'] );
$default          = isset( $field_default ) ? esc_attr( $field_default ) : esc_attr( 'off' );
$option_value     = $this->get( esc_attr( $field_key . '.' . $field_event ), $default );
$field_key_event  = sanitize_key( $field_key . '_' . $field_event );
$field_key_title  = $field_key . ' --> ' . $field_event;
$field_template   = sanitize_key( $field['template'] );
$field_id         = sanitize_key( 'field_' . $field_key_event );
$tooltip_desc     = ! empty( $field['tooltip'] ) ? $field['tooltip'] : null;
$send_test_button = '<button type="button" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm send_test_email"
data-to="' . esc_attr( $field_key ) . '" data-label="' . $field_label . '" data-key="' . esc_attr( $field_event ) . '" data-template="' . esc_attr( $field_template ) . '"
>' . esc_attr( 'Send Test' ) . '</button>';
?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
	<div class="tutor-option-field-label <?php echo $tooltip_desc ? 'has-tooltip' : ''; ?>">
		<div class="tutor-fs-6 tutor-fw-medium tutor-mb-8" tutor-option-name>
			<?php echo esc_html( $field_label ); ?>
		</div>
		<?php if ( $tooltip_desc ) { ?>
			<div class="tooltip-wrap tooltip-icon">
				<span class="tooltip-txt tooltip-right"><?php echo esc_attr( $tooltip_desc ); ?></span>
			</div>
			<span style="white-space: nowrap;"></span>
		<?php } ?>
	</div>
	<div class="tutor-option-field-input tutor-d-flex has-btn-after">
		<label class="tutor-form-toggle">
			<input type="hidden" name="tutor_option[<?php echo esc_attr( $field_key ); ?>][<?php echo esc_attr( $field['event'] ); ?>]" value="<?php echo esc_attr( $option_value ); ?>">
			<input type="checkbox" value="on" <?php esc_attr( checked( $option_value, 'on' ) ); ?> class="tutor-form-toggle-input">
			<span class="tutor-form-toggle-control"></span>
		</label>
		<?php
		if ( isset( $field['buttons'] ) ) {
			foreach ( $field['buttons'] as $key => $button ) {
				if ( 'anchor' === $button['type'] ) {
					?>
					<a class="tutor-btn tutor-btn-outline-primary tutor-btn-sm" href="<?php echo esc_attr( $button['url'] ); ?>"><?php echo esc_attr( $button['text'] ); ?></a>
					<?php
				}
			}
		}
		?>
	</div>
</div>
