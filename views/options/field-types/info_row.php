<?php
/**
 * Info row for settings.
 *
 * @package Tutor LMS
 * @since 2.0
 */

?>
<div class="tutor-option-field-row d-block">
	<div class="tutor-text-xs"><?php echo esc_attr( $field['label'] ); ?>:</div>
	<div class="tutor-text <?php echo esc_attr( $field['status'] ); ?>"><?php echo esc_attr( $field['default'] ); ?></div>
</div>
