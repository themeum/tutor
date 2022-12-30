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
	<?php isset( $field['label'] ) ? printf( '<div class="tutor-fs-6 tutor-fw-medium tutor-mb-8" tutor-option-name>%s</div>', esc_attr( $field['label'] ) ) : null; ?>
	<?php isset( $field['desc'] ) ? printf( '<div class="tutor-fs-7 tutor-color-muted">%s</div>', wp_kses_post( $field['desc'] ) ) : null; ?>
</div>
