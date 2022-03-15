<?php
/**
 * Information column for settings.
 *
 * @package Tutor LMS
 * @since 2.0
 */

?>
<div class="tutor-option-field-row">
	<div class="tutor-text-xs"><?php echo esc_attr( $field['label'] ); ?>:</div>
	<div class="tutor-text-sm tutor-text-right tutor-fs-7 tutor-fw-medium tutor-color-black <?php echo esc_attr( $field['status'] ); ?>"><?php echo esc_attr( $field['default'] ); ?></div>
</div>
