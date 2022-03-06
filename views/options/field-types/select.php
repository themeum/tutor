<?php

/**
 * Select filed for settings.
 *
 * @package Tutor LMS
 * @since 2.0
 */
$field_key = sanitize_key($field['key']);
$dashboard_page_id = (int) tutor_utils()->get_option('tutor_dashboard_page_id');
// $selected_option_key = $this->get($field_key, (isset($field['default']) ? esc_attr($field['default']) : null));
$field_id  = sanitize_key('field_' . $field_key); ?>

<div class="tutor-option-field-row" id="<?php echo esc_attr($field_id); ?>">
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>
	<div class="tutor-option-field-input">
		<select name="tutor_option[<?php echo esc_attr($field_key); ?>]" class="tutor-form-select">
			<?php
			if (!isset($field['options']) && $field['options'] !== false) {
				echo '<option value="-1">' . __('Select Option', 'tutor') . '</option>';
			}
			if (!empty($field['options'])) { 
				foreach ($field['options'] as $option_key => $option) {
			?>
					<option value="<?php echo esc_attr($option_key); ?>" <?php echo $dashboard_page_id === $option_key ? " selected='selected'" : "" ?>><?php echo esc_attr($option); ?></option>
			<?php
				}
			}
			?>
		</select>
	</div>
</div>