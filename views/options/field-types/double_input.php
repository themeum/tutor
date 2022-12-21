<?php
/**
 * Double input for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

if ( ! isset( $field['fields'] ) || ! is_array( $field['fields'] ) || ! count( $field['fields'] ) ) {
	return;
}

$field_key = esc_attr( $field['key'] );
$field_id  = esc_attr( 'field_' . $field_key );
?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<div class="tutor-d-flex tutor-flex-column double-input">
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
