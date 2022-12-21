<?php
/**
 * Select inside group for tutor settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>
<select class="tutor-form-select" name="<?php echo esc_attr( $input_name ); ?>">
	<?php
	if ( ! isset( $group_field['select_options'] ) || false !== $group_field['select_options'] ) {
		echo '<option value="-1">' . esc_attr( 'Select Option' ) . '</option>';
	}
	if ( ! empty( $group_field['options'] ) ) {
		foreach ( $group_field['options'] as $option_key => $option ) {
			?>
			<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $input_value, $option_key ); ?>><?php echo esc_attr( $option ); ?></option>
			<?php
		}
	}
	?>
</select>
