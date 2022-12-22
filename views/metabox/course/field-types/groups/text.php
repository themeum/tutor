<?php
/**
 * Text field
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>
<input type="text" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $input_value ); ?>" >
<?php
if ( $label ) {
	?>
	<p><?php echo esc_html( $label ); ?></p>
	<?php
}
?>
