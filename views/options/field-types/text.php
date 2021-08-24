<?php
$value = $this->get($field['key']);
if ($value != '0' && !$value && isset($field['default'])) {
	$value = $field['default'];
}
?>
<div class="tutor-option-field-row">
<?php include tutor()->path . "views/options/template/field_heading.php";?>

	<div class="tutor-option-field-input">
		<input type="text" name="tutor_option[<?php echo $field['key']; ?>]" class="tutor-form-control" placeholder='Please write your "<?php echo $field['label']; ?>"' value="<?php echo $value ?? null ?>" />
	</div>
</div>