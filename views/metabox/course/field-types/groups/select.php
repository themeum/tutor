<?php
/**
 * Select field
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>
<select name="<?php echo esc_attr( $input_name ); ?>">
	<?php
	if ( ! isset( $group_field['select_options'] ) || false !== $group_field['select_options'] ) {
		echo '<option value="-1">' . esc_html__( 'Select Option', 'tutor' ) . '</option>';
	}
	if ( ! empty( $group_field['options'] ) ) {
		foreach ( $group_field['options'] as $optionKey => $option ) {
			?>
			<option value="<?php echo esc_attr( $optionKey ); ?>" <?php selected( $input_value, $optionKey ); ?> ><?php echo esc_attr( $option ); ?></option>
			<?php
		}
	}
	?>
</select>
