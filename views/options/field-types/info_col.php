<?php
/**
 * Information column for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field settings.
 *
 * @var array $field
 */

?>
<div class="tutor-option-field-row">
	<div class="tutor-fs-7"><?php echo esc_html( $field['label'] ?? '' ); ?>:</div>
	<div class="tutor-fs-7 tutor-fw-medium tutor-color-black tutor-text-right tutor-color-<?php echo esc_attr( $field['status'] ?? '' ); ?>"><?php echo wp_kses_post( $field['default'] ?? '' ); ?></div>
</div>
