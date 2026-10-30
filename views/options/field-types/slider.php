<?php
/**
 * Slider for settings.
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
$value     = $this->get( $field_key, $field['default'] ?? '' );
?>

<div class="tutor-field-type-slider" data-min="<?php echo esc_attr( tutor_utils()->avalue_dot( 'options.min', $field ) ); ?>" data-max="<?php echo esc_attr( tutor_utils()->avalue_dot( 'options.max', $field ) ); ?>">
	<p class="tutor-field-type-slider-value"><?php echo esc_html( $value ); ?></p>
	<div class="tutor-field-slider"></div>
	<input type="hidden" value="<?php echo esc_attr( $value ); ?>" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" />
</div>
