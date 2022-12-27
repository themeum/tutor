<?php
/**
 * Number field
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>
<input type="number" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $input_value ); ?>" >
<?php
if ( $label ) {
	echo '<p>' . esc_html( $label ) . '</p>';
}
?>
