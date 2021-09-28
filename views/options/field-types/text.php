<?php
$value = $this->get($field['key']);
if (empty($value) && isset($field['default'])) {
	$value = $field['default'];
}
$field_id = 'field_' . $field['key'];

?>
<div class="tutor-option-field-row" id="<?php echo $field_id; ?>">
	<?php include tutor()->path . "views/options/template/field_heading.php"; ?>

	<div class="tutor-option-field-input">
		<input type="text" name="tutor_option[<?php echo $field['key']; ?>]" class="tutor-form-control" value="<?php echo isset($value) ? $value : ''; ?>" />
	</div>
</div>
