<?php
/**
 * Toggle Switch (radio) button for settings.
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

$field_key          = $field['key'] ?? '';
$field_id           = 'field_' . $field_key;
$field_default      = $field['default'] ?? 'off';
$field_label_title  = $field['label_title'] ?? '';
$option_value       = $this->get( $field_key, $field_default );
$option_value       = ( 1 === (int) $option_value || 'on' === $option_value ) ? 'on' : 'off';
$toggle_fields      = $field['toggle_fields'] ?? '';
$toggle_blocks      = $field['toggle_blocks'] ?? '';
$has_control_button = ! empty( $field['has_control_button'] );
?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>
	<div class="tutor-option-field-input">
		<label class="tutor-form-toggle">
			<?php printf( "<span class='label-before'>%s</span>", esc_html( $field_label_title ) ); ?>
			<input type="hidden" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" value="<?php echo esc_attr( $option_value ); ?>">
			<input type="checkbox"
				<?php if ( ! $has_control_button && $toggle_fields ) : ?>
					data-toggle-fields="<?php echo esc_attr( $toggle_fields ); ?>"
				<?php endif; ?>
				<?php if ( ! $has_control_button && $toggle_blocks ) : ?>
					data-toggle-blocks="<?php echo esc_attr( $toggle_blocks ); ?>"
				<?php endif; ?>
				<?php checked( $option_value, 'on' ); ?>
				class="tutor-form-toggle-input">
			<span class="tutor-form-toggle-control"></span>
		</label>
		<?php if ( $has_control_button ) : ?>
		<button type="button" class="tutor-control-button">
			<input type="checkbox"
				<?php if ( $toggle_fields ) : ?>
					data-toggle-fields="<?php echo esc_attr( $toggle_fields ); ?>"
				<?php endif; ?>
				<?php if ( $toggle_blocks ) : ?>
					data-toggle-blocks="<?php echo esc_attr( $toggle_blocks ); ?>"
				<?php endif; ?>
			>
			<i class="tutor-icon-slider-horizontal"></i>
		</button>
		<?php endif; ?>
	</div>
</div>
