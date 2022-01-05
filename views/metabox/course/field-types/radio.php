<?php
if ( ! empty($field['options'])){
	foreach ($field['options'] as $optionKey => $option){
		$option_value = $this->get($field['field_key'], tutils()->array_get('default', $field));
		?>
        <p>
            <label>
                <input type="radio" name="_tutor_course_settings[<?php echo $field['field_key']; ?>]"  value="<?php echo esc_attr( $optionKey ) ?>" <?php checked($option_value,  $optionKey) ?> /> <?php echo esc_attr( $option ) ?>
            </label>
        </p>
		<?php
	}
}
?>