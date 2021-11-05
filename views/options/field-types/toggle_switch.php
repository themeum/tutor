<?php
/**
 * Checkbox items template for full width type field.
 *
 * @package Tutor LMS
 * @since 2.0
 */

$field_key         = esc_attr( $field['key'] );
$field_default     = esc_attr( $field['default'] );
$field_label_title = esc_attr( $field['label_title'] );
$label_title       = ( isset( $field_label_title ) && null !== esc_attr( $field_label_title ) ) ? esc_attr( $field_label_title ) : null;
$default           = isset( $field_default ) ? esc_attr( $field_default ) : esc_attr( 'off' );
$option_value      = $this->get( esc_attr( $field_key ), $default );
$field_id          = esc_attr( 'field_' . $field_key );
?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
	<?php require tutor()->path . 'views/options/template/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<label class="tutor-form-toggle">
			<?php printf( "<span class='label-before'>%s</span>", esc_attr( $field_label_title ) ); ?>
			<input type="hidden" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" value="<?php echo esc_attr( $option_value ); ?>">
			<input type="checkbox" value="on" <?php checked( esc_attr( $option_value ), 'on' ); ?> class="tutor-form-toggle-input">
			<span class="tutor-form-toggle-control"></span>
		</label>
	</div>
</div>
