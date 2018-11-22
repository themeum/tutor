<div class="dozent-field-type-slider" data-min="<?php echo dozent_utils()->avalue_dot('options.min', $field) ?>" data-max="<?php echo dozent_utils()->avalue_dot('options.max', $field) ?>">
	<p class="dozent-field-type-slider-value"><?php echo $this->get($field['field_key'], $field['default']); ?></p>
	<div class="dozent-field-slider"></div>
	<input type="hidden" value="<?php echo $this->get($field['field_key'], $field['default']); ?>" name="dozent_option[<?php echo $field['field_key']; ?>]" />
</div>
