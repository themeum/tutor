<?php
/**
 * Checkbox field
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>
<label>
	<input type="checkbox" name="<?php echo esc_attr( $input_name ); ?>" value="1" <?php checked( $input_value, '1' ); ?> >
	<?php echo esc_attr( $label ); ?>
</label>
