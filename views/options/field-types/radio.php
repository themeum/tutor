<?php
/**
 * Radio input for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

if ( ! isset( $field['select_options'] ) || false !== $field['select_options'] ) {
	echo '<option value="-1">' . esc_html__( 'Select Option', 'tutor' ) . '</option>';
}
if ( ! empty( $field['options'] ) ) {
	foreach ( $field['options'] as $option_key => $option ) {
		$option_value = $this->get( $field['field_key'], tutor_utils()->array_get( 'default', $field ) );
		?>
		<p class="option-type-radio-wrap">
			<label>
				<input type="radio" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]"  value="<?php echo esc_attr( $option_key ); ?>" <?php echo esc_attr( checked( $option_value, $option_key ) ); ?> /> <?php echo esc_html( $option ); ?>
			</label>
		</p>
		<?php
	}
}
?>
