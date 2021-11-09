<?php
/**
 * Group fields for settings.
 *
 * @package Tutor LMS
 * @since 2.0
 */


if ( ! isset( $field['group_fields'] ) || ! is_array( $field['group_fields'] ) || ! count( $field['group_fields'] ) ) {
	return;
}
?>
<div class="tutor-option-field-row">
	<?php require tutor()->path . 'views/options/template/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<div class="d-flex input-select">
			<?php
			foreach ( $field['group_fields'] as $group_field_key => $group_field ) {
				$input_name    = "tutor_option[{$field['key']}][{$group_field_key}]";
				$default_value = isset( $group_field['default'] ) ? $group_field['default'] : false;
				$input_value   = $this->get( $field['key'] . '.' . $group_field_key, $default_value );
				$label         = tutor_utils()->avalue_dot( 'label', $group_field );

				include tutor()->path . "views/options/field-types/groups/{$group_field['type']}.php";
			}
			?>
		</div>
	</div>
</div>
