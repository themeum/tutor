<?php
/**
 * Email field for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$value = $this->get( $field['key'] );
if ( '0' != $value && ! $value && isset( $field['default'] ) ) {
	$value = $field['default'];
}
$field_key   = isset( $field['key'] ) ? esc_attr( $field['key'] ) : null;
$field_id    = esc_attr( 'field_' . $field_key );
$placeholder = isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : esc_attr( $field['desc'] );
?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<input type="email" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" class="tutor-form-control" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo esc_attr( isset( $value ) ? $value : '' ); ?>" />
	</div>
</div>
