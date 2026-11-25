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

defined( 'ABSPATH' ) || exit;

/**
 * Group field settings.
 *
 * @var string $input_name
 * @var mixed  $input_value
 * @var array  $group_field
 */
?>
<select class="tutor-form-select" name="<?php echo esc_attr( $input_name ); ?>">
	<?php
	if ( ! isset( $group_field['select_options'] ) || false !== $group_field['select_options'] ) {
		echo '<option value="-1">' . esc_html__( 'Select Option', 'tutor' ) . '</option>';
	}
	if ( ! empty( $group_field['options'] ) ) {
		foreach ( $group_field['options'] as $option_key => $option ) {
			?>
			<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $input_value, $option_key ); ?>><?php echo esc_html( $option ); ?></option>
			<?php
		}
	}
	?>
</select>
