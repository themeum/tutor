<?php
/**
 * Text field
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$value = $this->get( $field['field_key'] );
if ( ! $value && isset( $field['default'] ) ) {
	$value = $field['default'];
}
?>
<input type="text" name="_tutor_course_settings[<?php echo esc_attr( $field['field_key'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" >
