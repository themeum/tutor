<?php
/**
 * Checkbox without label template
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.5.0
 */

$key    = $field['key'] ?? '';
$events = $field['event'] ?? null;
if ( $events ) {
	$key = $field['event'] . '.' . $key;
}

$value = $this->get( $key );
if ( empty( $value ) && ! empty( $field['default'] ) ) {
	$value = $field['default'];
}

$checked = 'on' === $value ? 'checked' : '';

// Prepare field name.
$field_name = $this->get_field_name( $field );
?>
<div class="tutor-form-check">
	<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
	<input type="checkbox" value="<?php echo esc_attr( $value ); ?>" class="tutor-form-toggle-input tutor-form-check-input" <?php echo esc_attr( $checked ); ?>>
</div>

