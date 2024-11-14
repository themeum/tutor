<?php
/**
 * Template: Field heading
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>
<div class="tutor-option-field-label">
	<div class="tutor-d-flex tutor-gap-1 tutor-align-center">
		<?php isset( $field['label_icon'] ) ? printf( '<img src="%s" />', esc_attr( $field['label_icon'] ) ) : null; ?>
		<?php isset( $field['label'] ) ? printf( '<div class="tutor-fs-6 tutor-fw-medium" tutor-option-name>%s</div>', esc_attr( $field['label'] ) ) : null; ?>
		<?php isset( $field['label_tag'] ) ? printf( '<div class="tutor-tag tag-success" tutor-option-name>%s</div>', esc_attr( $field['label_tag'] ) ) : null; ?>
	</div>
	<?php ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) ? printf( '<div class="tutor-fs-7 tutor-color-muted tutor-mt-8">%s</div>', wp_kses_post( $field['desc'] ) ) : null; ?>
</div>
