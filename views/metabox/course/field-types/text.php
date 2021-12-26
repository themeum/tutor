<?php
$value = $this->get($field['field_key']);
if ( ! $value && isset($field['default'])){
	$value = $field['default'];
}
?>
<input type="text" name="_tutor_course_settings[<?php echo esc_attr( $instructor->ID ); ?>]" value="<?php echo esc_attr( $value ); ?>" >