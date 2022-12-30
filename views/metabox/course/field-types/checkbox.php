<?php
/**
 * Checkbox meta box
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

if ( empty( $field['options'] ) ) {
	$default      = isset( $field['default'] ) ? $field['default'] : '';
	$option_value = $this->get( $field['field_key'], $default );
	$label_title  = isset( $field['label_title'] ) ? $field['label_title'] : $field['label'];
	?>
	<label>
		<input type="checkbox" name="_tutor_course_settings[<?php echo esc_attr( $field['field_key'] ); ?>]" value="1" <?php checked( $option_value, '1' ); ?> />
		<?php echo esc_attr( $label_title ); ?>
	</label>
	<?php
} else {
	// Check if multi option exists.
	foreach ( $field['options'] as $field_option_key => $field_option ) {
		?>
		<label>
			<input type="checkbox" name="_tutor_course_settings[<?php echo esc_attr( $field['field_key'] ); ?>][<?php echo esc_attr( $field_option_key ); ?>]" value="1" <?php checked( $this->get( $field['field_key'] . '.' . $field_option_key ), '1' ); ?> />
			<?php echo esc_attr( $field_option ); ?>
		</label>
		<br />
		<?php
	}
}
?>
