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

defined( 'ABSPATH' ) || exit;

/**
 * Field settings.
 *
 * @var mixed $this  Options_V2 or Tools_V2 instance.
 * @var array $field
 */

$field_key   = sanitize_key( $field['key'] ?? '' );
$default     = $field['default'] ?? false;
$value       = $this->get( $field_key, $default );
$field_id    = 'field_' . $field_key;
$placeholder = $field['placeholder'] ?? ( $field['desc'] ?? '' );
$max_length  = isset( $field['maxlength'] ) ? (int) $field['maxlength'] : 0;
$rows        = isset( $field['rows'] ) ? (int) $field['rows'] : 10;
?>
<div class="tutor-option-field-row col-1x145" id="<?php echo esc_attr( $field_id ); ?>">
<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<textarea class="tutor-form-control" 
				name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" 
				<?php if ( $max_length > 0 ) : ?>
					maxlength="<?php echo esc_attr( $max_length ); ?>"
				<?php endif; ?>
				<?php if ( $rows > 0 ) : ?>
					rows="<?php echo esc_attr( $rows ); ?>"
				<?php endif; ?>
				placeholder="<?php echo esc_attr( $placeholder ); ?>"><?php echo wp_kses_post( (string) $value ); ?></textarea>
	</div>
</div>
