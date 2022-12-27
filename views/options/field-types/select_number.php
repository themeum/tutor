<?php
/**
 * Select & input type number for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$field_key = sanitize_key( $field['key'] );
$field_id  = sanitize_key( 'field_' . $field_key );
?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input tutor-d-flex tutor-justify-end">
		<select class="tutor-form-select" disabled="">
			<option selected="">Select your Fee type</option>
			<option value="1">percent</option>
			<option value="2">fixed</option>
		</select>
		<input type="number" class="tutor-form-control" placeholder="0" value="0" min="0" disabled="">
	</div>
</div>
