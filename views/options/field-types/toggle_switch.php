<?php
/**
 * Toggle Switch (radio) button for settings.
 *
 * @package Tutor LMS
 * @since 2.0
 */

$field_key         = esc_attr( $field['key'] );
$field_id          = esc_attr( 'field_' . $field_key );
$field_default     = esc_attr( $field['default'] );
$field_label_title = isset( $field['label_title'] ) ? $field['label_title'] : null;
$label_title       = ( isset( $field_label_title ) && null !== esc_attr( $field_label_title ) ) ? esc_attr( $field_label_title ) : null;
$default           = isset( $field_default ) ? esc_attr( $field_default ) : esc_attr( 'off' );
$option_value      = $this->get( esc_attr( $field_key ), $default );
$option_value      = is_array( $option_value ) ? $option_value[0] : $option_value;
?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>
	<div class="tutor-option-field-input">
		<label class="tutor-form-toggle">
			<?php printf( "<span class='label-before'>%s</span>", esc_attr( $field_label_title ) ); ?>
			<input type="hidden" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" value="<?php echo esc_attr( $option_value ); ?>">
			<input type="checkbox" <?php checked( esc_attr( $option_value ), 'on' ); ?> class="tutor-form-toggle-input">
			<span class="tutor-form-toggle-control"></span>
		</label>
	</div>
</div>
