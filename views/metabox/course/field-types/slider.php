<?php
/**
 * Slider field
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>
<div class="tutor-field-type-slider" data-min="<?php echo esc_attr( tutor_utils()->avalue_dot( 'options.min', $field ) ); ?>" data-max="<?php echo esc_attr( tutor_utils()->avalue_dot( 'options.max', $field ) ); ?>">
	<p class="tutor-field-type-slider-value"><?php echo esc_attr( $this->get( $field['field_key'], $field['default'] ) ); ?></p>
	<div class="tutor-field-slider"></div>
	<input type="hidden" value="<?php echo esc_attr( $this->get( $field['field_key'], $field['default'] ) ); ?>" name="_tutor_course_settings[<?php echo esc_attr( $field['field_key'] ); ?>]" />
</div>
