<?php
/**
 * Info row for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>
<div class="tutor-option-field-row tutor-d-block">
	<div class="tutor-fs-7"><?php echo esc_attr( $field['label'] ); ?>:</div>
	<div class="tutor-fs-7 tutor-fw-medium tutor-color-black <?php echo esc_attr( $field['status'] ); ?>"><?php echo esc_attr( $field['default'] ); ?></div>
</div>
