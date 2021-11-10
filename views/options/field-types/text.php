<?php
/**
 * Input filed type text for settings.
 *
 * @package Tutor LMS
 * @since 2.0
 */

$value = $this->get( $field['key'] );
if ( empty( $value ) && isset( $field['default'] ) ) {
	$value = $field['default'];
}
$field_key = isset( $field['key'] ) ? esc_attr( $field['key'] ) : null;
$field_id  = esc_attr( 'field_' . $field_key );
?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
	<?php require tutor()->path . 'views/options/template/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<input type="text" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" class="tutor-form-control" value="<?php echo esc_attr( isset( $value ) ? $value : '' ); ?>" />
	</div>
</div>
