<select name="_tutor_course_settings[<?php echo esc_attr( $instructor->ID ); ?>]" class="tutor_select2">
	<?php
	if ( ! isset( $field['select_options'] ) || $field['select_options'] !== false ) {
		echo _esc_html( '<option value="-1">' . __( 'Select Option', 'tutor' ) . '</option>' );
	}
	if ( ! empty( $field['options'] ) ) {
		foreach ( $field['options'] as $optionKey => $option ) {
			?>
			<option value="<?php echo esc_attr( $optionKey ); ?>" <?php selected( $this->get( $field['field_key'] ), $optionKey ); ?> ><?php echo esc_attr( $option ); ?></option>
			<?php
		}
	}
	?>
</select>
