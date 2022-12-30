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
<select name="_tutor_course_settings[<?php echo esc_attr( $field['field_key'] ); ?>]" class="tutor_select2">
	<?php
	if ( ! isset( $field['select_options'] ) && false !== $field['select_options'] ) {
		echo '<option value="-1">' . esc_html__( 'Select Option', 'tutor' ) . '</option>';
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
