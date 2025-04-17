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

$checked = isset( $field['value'] ) && 'on' === $field['value'] ? 'checked' : '';
?>
<div class="tutor-form-check">
	<?php if ( ! empty( $field['label'] ) ) : ?>
	<span class="label-before">
		<?php esc_html_e( 'Logged Only', 'tutor' ); ?>
	</span>
	<?php endif; ?>
	<input type="checkbox" name="tutor_option[supported_course_filters][search]" value="<?php echo esc_attr( $field['value'] ?? 'on' ); ?>" class="tutor-form-check-input" <?php echo esc_attr( $checked ); ?>>
</div>

