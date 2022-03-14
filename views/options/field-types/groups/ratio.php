<?php
/**
 * Ration input for tutor settings.
 *
 * @package Tutor LMS
 * @since 2.0
 */
?>

<label for="revenue-instructor" class="revenue-percentage">
	<span><?php echo esc_attr( $label ); ?></span>
	<input type="number" class="tutor-form-control" placeholder="0 %" name="<?php echo esc_attr( $input_name ); ?>" id="<?php echo esc_attr( $group_field['id'] ); ?>" min="0" max="100" value="<?php echo esc_attr( $input_value ); ?>">
</label>
