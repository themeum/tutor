<?php
/**
 * Number input for tutor settings.
 *
 * @package Tutor LMS
 * @since 2.0
 */
?>
<input type="number" class="tutor-form-control" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $input_value ); ?>" min="0">
<?php
if ( $label ) {
	echo '<p>' . esc_html( $label ) . '</p>';
}
?>
