<?php
$default = $field['default'] ?? '';
$option_value = $this->get($field['key'], $default);
?>
<div class="tutor-option-field-row">
	<div class="tutor-option-field-label">
		<label><?php echo $field['label']; ?></label>
		<p class="desc"><?php echo $field['desc'] ?></p>
	</div>
	<div class="tutor-option-field-input">
		<label class="tutor-form-toggle">
			<?php echo null !== $field['label_title'] ? "<span class='label-before'>{$field['label_title']}</span>" : null; ?>
			<input type="checkbox" name="tutor_option[<?php echo $field['key']; ?>]" value="1" <?php checked($option_value, '1') ?> class="tutor-form-toggle-input">
			<span class="tutor-form-toggle-control"></span>
		</label>
	</div>
</div>