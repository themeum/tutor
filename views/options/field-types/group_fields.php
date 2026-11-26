<?php
/**
 * Group fields for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field settings.
 *
 * @var mixed $this  Options_V2 or Tools_V2 instance.
 * @var array $field
 */

if ( ! isset( $field['group_fields'] ) || ! is_array( $field['group_fields'] ) || ! count( $field['group_fields'] ) ) {
	return;
}

$field_key = $field['key'] ?? '';
$field_id  = 'field_' . $field_key;
?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<div class="tutor-d-flex input-select">
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
