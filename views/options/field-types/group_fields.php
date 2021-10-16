<?php
if (!isset($field['group_fields']) || !is_array($field['group_fields']) || !count($field['group_fields'])) {
	return;
}
?>
<div class="tutor-option-field-row">
	<?php include tutor()->path . "views/options/template/field_heading.php"; ?>

	<div class="tutor-option-field-input">
		<div class="tutor-bs-d-flex input-select">
			<?php
			foreach ($field['group_fields'] as $groupFieldKey => $group_field) {
				$input_name = "tutor_option[{$field['key']}][{$groupFieldKey}]";
				$default_value = isset($group_field['default']) ? $group_field['default'] : false;
				$input_value = $this->get($field['key'] . '.' . $groupFieldKey, $default_value);
				$label = tutor_utils()->avalue_dot('label', $group_field);
				
				include tutor()->path . "views/options/field-types/groups/{$group_field['type']}.php";
			}
			?>
		</div>
	</div>
</div>