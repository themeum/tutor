<div class="tutor-option-field-row">
	<div class="tutor-option-field-label">
		<label><?php echo $field['label']; ?></label>
		<p class="desc"><?php echo $field['desc'] ?></p>
	</div>
	<div class="tutor-option-field-input">
		<select name="tutor_option[<?php echo $field['key']; ?>]" class="tutor-form-select">
			<?php
			if (!isset($field['options']) || $field['options'] !== false) {
				echo '<option value="-1">' . __('Select Option', 'tutor') . '</option>';
			}
			if (!empty($field['options'])) {
				foreach ($field['options'] as $optionKey => $option) {
			?>
					<option value="<?php echo $optionKey ?>" <?php selected($this->get($field['key']),  $optionKey) ?>><?php echo $option ?></option>
			<?php
				}
			}
			?>
		</select>
	</div>
</div>