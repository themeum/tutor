<?php
/**
 * Color picker for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$field_key = isset( $field['key'] ) ? esc_attr( $field['key'] ) : null;

$value = $this->get( $field_key );
if ( ! $value && isset( $field['default'] ) ) {
	$value = esc_attr( $field['default'] );
}
?>
<input type="text" class="tutor_colorpicker" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" value="<?php echo esc_attr( $value ); ?>" >
