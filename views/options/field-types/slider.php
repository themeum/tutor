<div class="lms-field-type-slider" data-min="<?php echo lms_utils()->avalue_dot('options.min', $field) ?>" data-max="<?php echo lms_utils()->avalue_dot('options.max', $field) ?>">
	<p class="lms-field-type-slider-value"><?php echo $this->get($field['field_key'], $field['default']); ?></p>
	<div class="lms-field-slider"></div>
	<input type="hidden" value="<?php echo $this->get($field['field_key'], $field['default']); ?>" name="lms_option[<?php echo $field['field_key']; ?>]" />
</div>
