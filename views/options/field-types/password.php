<?php
/**
 * Input filed type password for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

$field_key     = $field['key'];
$default       = isset( $field['default'] ) ? $field['default'] : false;
$value         = $this->get( $field_key, $default );
$field_id      = esc_attr( 'field_' . $field_key );
$placeholder   = isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : esc_attr( $field['desc'] );
$field_classes = isset( $field['field_classes'] ) ? $field['field_classes'] : '';
$field_style   = isset( $field['field_style'] ) ? 'style="' . $field['field_style'] . '"' : '';
$max_length    = isset( $field['maxlength'] ) ? (int) $field['maxlength'] : 0;
?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>
	<div class="tutor-option-field-input">
		<div class="tutor-type-password">
			<input type="password" 
					name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" 
					class="tutor-form-control <?php echo esc_attr( $field_classes ); ?>" <?php echo esc_attr( $field_style ); ?> 
					placeholder="<?php echo esc_attr( $placeholder ); ?>"
					<?php if ( $max_length > 0 ) : ?>
						maxlength="<?php echo esc_attr( $max_length ); ?>"
					<?php endif; ?> 
					value="<?php echo esc_attr( isset( $value ) ? $value : '' ); ?>" />
			<button type="button"><i class="tutor-icon-eye-slash-bold"></i></button>
		</div>

	</div>
</div>
