<?php
/**
 * Checkbox items template for full width type field.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
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
	<?php if ( ! empty( $field['label'] ) ) : ?>
	<span class="label-before">
		<?php echo esc_html( $field['label'] ); ?>
	</span>
	<?php endif; ?>
	<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
	<input type="checkbox" value="<?php echo esc_attr( $value ); ?>" class="tutor-form-toggle-input tutor-form-check-input" <?php echo esc_attr( $checked ); ?>>
</div>

