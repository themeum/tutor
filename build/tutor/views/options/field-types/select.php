<?php $field_id = 'field_' . $field['key'];
?>
<div class="tutor-option-field-row" id="<?php echo $field_id; ?>">
	<?php include tutor()->path . "views/options/template/field_heading.php"; ?>
	<div class="tutor-option-field-input">
		<select name="tutor_option[<?php echo $field['key']; ?>]" class="tutor-form-select">
			<?php
			if (!isset($field['options']) || $field['options'] !== false) {
				echo '<option value="-1">' . __('Select Option', 'tutor') . '</option>';
			}
			if (!empty($field['options'])) {
				foreach ($field['options'] as $optionKey => $option) {
			?>
					<option value="<?php echo $optionKey ?>" <?php selected($this->get($field['key']),  $optionKey) ?>><?php echo esc_html__($option); ?></option>
			<?php
				}
			}
			?>
		</select>
	</div>
</div>