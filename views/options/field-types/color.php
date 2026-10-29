<?php
/**
 * Color picker for settings.
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
 * @var mixed $this  Options_V2 or Tools_V2 instance.
 * @var array $field
 */

$field_key = $field['key'] ?? '';

$value = $this->get( $field_key );
if ( ! $value && isset( $field['default'] ) ) {
	$value = $field['default'];
}
?>
<input type="text" class="tutor_colorpicker" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" value="<?php echo esc_attr( $value ); ?>" >
