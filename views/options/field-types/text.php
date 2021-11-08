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
$field_id = 'field_' . $field['key'];

?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
	<?php require tutor()->path . 'views/options/template/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<input type="text" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" class="tutor-form-control" value="<?php echo esc_attr( isset( $value ) ? $value : '' ); ?>" />
	</div>
</div>
