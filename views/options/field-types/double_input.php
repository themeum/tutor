<?php
/**
 * Double input for settings.
 *
 * @package Tutor LMS
 * @since 2.0
 */

if ( ! isset( $field['fields'] ) || ! is_array( $field['fields'] ) || ! count( $field['fields'] ) ) {
	return;
}
?>
<div class="tutor-option-field-row">
	<?php require tutor()->path . 'views/options/template/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<div class="d-flex flex-column double-input">
			<?php
			foreach ( $field['fields'] as $group_field_key => $group_field ) {
				$input_name    = "tutor_option[{$group_field_key}]";
				$default_value = isset( $group_field['default'] ) ? $group_field['default'] : false;
				$input_value   = $this->get( $group_field_key, $default_value );
				$label         = $group_field['title'] ?? $group_field_key;
				include tutor()->path . "views/options/field-types/groups/{$group_field['type']}.php";
			}
			?>
		</div>
	</div>
</div>
