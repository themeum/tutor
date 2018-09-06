<select name="lms_option[<?php echo $field['field_key']; ?>]" class="select2">
    <?php
    if ( ! isset($field['select_options']) || $field['select_options'] !== false){
        echo '<option value="-1">'.__('Select Option', 'lms').'</option>';
    }
	if ( ! empty($field['options'])){
		foreach ($field['options'] as $optionKey => $option){
			?>
			<option value="<?php echo $optionKey ?>" <?php selected($this->get($field['field_key']),  $optionKey) ?> ><?php echo $option ?></option>
			<?php
		}
	}
	?>
</select>