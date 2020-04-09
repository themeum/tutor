<?php
$value = $this->get($field['field_key']);
if ( $value != '0' && ! $value && isset($field['default'])){
	$value = $field['default'];
}
?>
<input type="text" name="tutor_option[<?php echo $field['field_key']; ?>]" value="<?php echo $value; ?>" >