<?php
/**
 * Checkbox of group for tutor settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>
<label>
	<input type="checkbox" name="<?php echo esc_attr( $input_name ); ?>" value="1" <?php echo wp_kses_post( checked( $input_value, '1' ) ); ?> >
	<?php echo esc_html( $label ); ?>
</label>
