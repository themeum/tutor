<?php
/**
 * Toggle switch button for email settings page.
 *
 * @package Tutor LMS
 * @since 2.0.0
 */

$field_default = sanitize_text_field( $field['default'] );
$field_key     = sanitize_key( $field['key'] );
$field_event   = sanitize_key( $field['event'] );
$default       = isset( $field_default ) ? esc_attr( $field_default ) : esc_attr( 'off' );
$option_value  = $this->get( esc_attr( $field_key . '.' . $field_event ), $default );
$field_id      = sanitize_key( 'field_' . $field_key );
$tooltip_desc  = ! empty( $field['tooltip'] ) ? $field['tooltip'] : null;
?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
	<div class="tutor-option-field-label <?php echo $tooltip_desc ? 'has-tooltip' : ''; ?>">
		<h5 class="label"><?php echo esc_attr( $field['label'] ); ?></h5>
		<?php if ( $tooltip_desc ) { ?>
			<div class="tooltip-wrap tooltip-icon">
				<span class="tooltip-txt tooltip-right"><?php echo esc_attr( $tooltip_desc ); ?></span>
			</div>
		<?php } ?>
	</div>
	<div class="tutor-option-field-input d-flex has-btn-after">
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
					<a class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs" href="<?php echo esc_attr( $button['url'] ); ?>"><?php echo esc_attr( $button['text'] ); ?></a>
					<?php
				}
			}
		}
		?>
	</div>
</div>
