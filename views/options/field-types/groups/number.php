<?php
/**
 * Number input for tutor settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>
<input type="number" class="tutor-form-control" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $input_value ); ?>" min="0">
<?php
if ( $label ) {
	echo '<p>' . esc_html( $label ) . '</p>';
}
?>
