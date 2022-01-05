
<select name="<?php echo esc_attr( $input_name ); ?>">
	<?php
	if ( ! isset( $group_field['select_options'] ) || $group_field['select_options'] !== false ) {
		echo '<option value="-1">' . __( 'Select Option', 'tutor' ) . '</option>';
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
