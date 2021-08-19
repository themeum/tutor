<?php
$value = $this->get($field['key']);
if ($value != '0' && !$value && isset($field['default'])) {
	$value = $field['default'];
}
?>
<div class="tutor-option-field-row">
	<!-- has-bg -->
	<div class="tutor-option-field-label">
		<label><?php echo $field['label'] ?></label>
		<p class="desc"><?php echo $field['desc'] ?></p>
	</div>
	<div class="tutor-option-field-input">
		<input class="tutor-form-control" type="number" name="tutor_option[<?php echo $field['key']; ?>]" value="<?php echo $value; ?>" placeholder="0" min="0">
	</div>
</div>