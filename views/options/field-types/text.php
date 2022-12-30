<?php
/**
 * Input filed type text for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$value = $this->get( $field['key'] );
if ( empty( $value ) && isset( $field['default'] ) ) {
	$value = $field['default'];
}
$field_key     = isset( $field['key'] ) ? esc_attr( $field['key'] ) : null;
$field_id      = esc_attr( 'field_' . $field_key );
$placeholder   = isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : esc_attr( $field['desc'] );
$field_classes = isset( $field['field_classes'] ) ? $field['field_classes'] : '';
$field_style   = isset( $field['field_style'] ) ? 'style="' . $field['field_style'] . '"' : '';
?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<input type="text" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" class="tutor-form-control <?php echo esc_attr( $field_classes ); ?>" <?php echo esc_attr( $field_style ); ?> placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo esc_attr( isset( $value ) ? $value : '' ); ?>" />
	</div>
</div>
