<select name="dozent_option[<?php echo $field['field_key']; ?>]" class="dozent_select2">
    <?php
    if ( ! isset($field['select_options']) || $field['select_options'] !== false){
        echo '<option value="-1">'.__('Select Option', 'dozent').'</option>';
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