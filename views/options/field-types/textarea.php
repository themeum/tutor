<?php
/**
 * Textarea field for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$field_key   = sanitize_key( $field['key'] );
$field_id    = sanitize_key( 'field_' . $field_key );
$placeholder = isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : esc_attr( $field['desc'] );
?>
<div class="tutor-option-field-row col-1x145" id="<?php echo esc_attr( $field_id ); ?>"
>
<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<textarea class="tutor-form-control" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" rows="10" placeholder="<?php echo esc_attr( $placeholder ); ?>"><?php echo wp_kses_post( $this->get( $field_key ) ); ?></textarea>
	</div>
</div>
