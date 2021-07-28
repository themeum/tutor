<?php
$value = $this->get($field['key']);
if ($value != '0' && !$value && isset($field['default'])) {
	$value = $field['default'];
}
?>
<div class="tutor-option-field-row has-bg">
	<div class="tutor-option-field-label">
		<label>Attempts allowed</label>
		<p class="desc">
			The highest number of attempts students are allowed to take for a quiz. <em>0</em> means
			unlimited attempts
		</p>
	</div>
	<div class="tutor-option-field-input">
		<input class="tutor-form-control" type="number" name="tutor_option[<?php echo $field['key']; ?>]" value="<?php echo $value; ?>" placeholder="0" min="0">
	</div>
</div>