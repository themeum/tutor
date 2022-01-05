
<input type="text" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $input_value ); ?>" >
<?php
if ($label){
	echo "<p>{$label}</p>";
}
?>