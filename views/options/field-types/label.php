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

?>

<div class="tutor-option-field-label">
	<div class="tutor-d-flex tutor-align-center">
		<div class="<?php echo esc_attr( $field['class'] ?? '' ); ?>">
			<?php echo esc_html( $field['label'] ?? '' ); ?>
		</div>			
	</div>
	<div class="tutor-fs-7 tutor-color-muted tutor-mt-8">
		<?php echo esc_html( $field['desc'] ); ?>
	</div>
</div>

